<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ShipmentRouteMap from '@/Components/ShipmentRouteMap.vue';

const props = defineProps({
    shipment: {
        type: Object,
        required: true,
    },
    locations: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const isAdmin = computed(() => user.value.role === 'admin');
const isOperator = computed(() => user.value.role === 'operator');

const showCheckpointModal = ref(false);
const showTransitionModal = ref(false);

const checkpointForm = useForm({
    location_id: '',
    status: props.shipment.current_status,
    event_type: 'NORMAL',
    latitude: '',
    longitude: '',
    occurred_at: new Date().toISOString().slice(0, 16),
    description: '',
});

const onLocationSelect = () => {
    const loc = props.locations.find(l => l.id === Number(checkpointForm.location_id));
    if (loc) {
        checkpointForm.latitude = loc.latitude;
        checkpointForm.longitude = loc.longitude;
    }
};

const submitCheckpoint = () => {
    checkpointForm.post(`/shipments/${props.shipment.id}/tracking`, {
        onSuccess: () => {
            showCheckpointModal.value = false;
            checkpointForm.reset('description', 'latitude', 'longitude');
        },
    });
};

const transitionForm = useForm({
    status: '',
    reason: '',
});

const submitTransition = () => {
    transitionForm.post(`/shipments/${props.shipment.id}/transition`, {
        onSuccess: () => {
            showTransitionModal.value = false;
            transitionForm.reset();
        },
    });
};

const statusBadgeClass = (st) => {
    switch (st) {
        case 'CREATED': return 'bg-white/10 text-[#D6E6EA] border-white/15';
        case 'BOOKED':
        case 'PICKED_UP': return 'bg-[#0C969C]/15 text-[#7DE2E6] border-[#0C969C]/35';
        case 'IN_TRANSIT': return 'bg-amber-500/15 text-amber-300 border-amber-400/30';
        case 'DELIVERED': return 'bg-emerald-500/15 text-emerald-300 border-emerald-400/30';
        case 'CANCELLED': return 'bg-rose-500/15 text-rose-300 border-rose-400/30';
        default: return 'bg-white/10 text-[#D6E6EA] border-white/15';
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Shipment ${shipment.shipment_number}`" />

        <!-- Header -->
        <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 mb-6 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <h1 class="text-xl font-extrabold font-mono tracking-tight text-white">{{ shipment.shipment_number }}</h1>
                    <span :class="['px-2.5 py-0.5 rounded-md border font-mono font-bold text-xs', statusBadgeClass(shipment.current_status)]">
                        {{ shipment.current_status }}
                    </span>
                    <span v-if="shipment.is_delayed" class="px-2.5 py-0.5 rounded-md bg-rose-500/15 text-rose-300 border border-rose-500/30 text-xs font-bold inline-flex items-center gap-1">
                        <i class="bi bi-clock-history"></i>
                        <span>DELAYED (+{{ shipment.delay_duration_hours }}j)</span>
                    </span>
                    <span v-else class="px-2.5 py-0.5 rounded-md bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 text-xs font-bold inline-flex items-center gap-1">
                        <i class="bi bi-check-circle"></i>
                        <span>ON-TIME</span>
                    </span>
                </div>
                <p class="text-xs font-light text-[#9ABBC8]">
                    Didaftarkan pada {{ new Date(shipment.created_at).toLocaleDateString() }} &bull; Customer: <strong class="text-white">{{ shipment.customer?.name || 'N/A' }}</strong>
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Link href="/shipments" class="px-3.5 py-1.5 border border-white/15 bg-white/5 hover:bg-white/10 text-xs font-semibold rounded-full text-[#D6E6EA] hover:text-white transition inline-flex items-center gap-1.5">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </Link>

                <template v-if="isAdmin || isOperator">
                    <button @click="showCheckpointModal = true" class="px-4 py-1.5 bg-[#274D60] hover:bg-[#0A7075] text-white text-xs font-semibold rounded-full transition inline-flex items-center gap-1.5 shadow-lg shadow-black/25 cursor-pointer">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>Catat Checkpoint</span>
                    </button>
                    <button @click="showTransitionModal = true" class="px-3.5 py-1.5 bg-white/10 hover:bg-white/15 border border-white/15 text-xs font-semibold rounded-full text-white transition inline-flex items-center gap-1.5 cursor-pointer">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Ubah Status</span>
                    </button>
                    <Link :href="`/shipments/${shipment.id}/edit`" class="p-2 border border-white/15 bg-white/5 hover:bg-white/15 text-[#D6E6EA] hover:text-white rounded-full text-xs transition" title="Edit Metadata" aria-label="Edit Metadata">
                        <i class="bi bi-pencil"></i>
                    </Link>
                </template>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Route Banner, Timeline & Line Items -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Route Card -->
                <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl">
                    <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4 text-center sm:text-left">
                        <div>
                            <span class="text-[10px] font-mono font-bold text-[#9ABBC8] uppercase tracking-wider block">Origin (Asal)</span>
                            <div class="text-xl font-mono font-extrabold text-[#7DE2E6]">{{ shipment.origin_location?.code }}</div>
                            <div class="text-xs font-bold text-white">{{ shipment.origin_location?.name }}</div>
                            <div class="text-[11px] text-[#9ABBC8]">{{ shipment.origin_location?.city }}, {{ shipment.origin_location?.country }}</div>
                            <div class="text-[11px] text-[#9ABBC8] mt-2 font-mono">
                                ETD: <strong class="text-white">{{ new Date(shipment.expected_departure).toLocaleDateString() }}</strong>
                            </div>
                        </div>

                        <div class="flex flex-col items-center justify-center text-center">
                            <span class="px-2.5 py-0.5 rounded-full bg-[#032324] border border-white/15 text-[10px] font-mono font-bold text-[#7DE2E6] mb-1.5">
                                {{ shipment.carrier?.code }} &bull; {{ shipment.carrier?.transport_mode }}
                            </span>
                            <i class="bi bi-arrow-right text-2xl text-[#0C969C] hidden sm:block"></i>
                            <i class="bi bi-arrow-down text-2xl text-[#0C969C] sm:hidden"></i>
                            <div class="text-[11px] text-[#D6E6EA] font-semibold mt-1">{{ shipment.carrier?.name }}</div>
                        </div>

                        <div class="sm:text-right">
                            <span class="text-[10px] font-mono font-bold text-[#9ABBC8] uppercase tracking-wider block">Destination (Tujuan)</span>
                            <div class="text-xl font-mono font-extrabold text-emerald-400">{{ shipment.destination_location?.code }}</div>
                            <div class="text-xs font-bold text-white">{{ shipment.destination_location?.name }}</div>
                            <div class="text-[11px] text-[#9ABBC8]">{{ shipment.destination_location?.city }}, {{ shipment.destination_location?.country }}</div>
                            <div class="text-[11px] text-[#9ABBC8] mt-2 font-mono">
                                ETA: <strong class="text-white">{{ new Date(shipment.actual_arrival || shipment.estimated_arrival || shipment.expected_arrival).toLocaleDateString() }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interactive Route Map (Leaflet Dark Canvas) -->
                <ShipmentRouteMap :shipment="shipment" height="380px" />

                <!-- Chronological Tracking Timeline -->
                <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl">
                    <div class="flex items-center justify-between pb-3 mb-6 border-b border-white/10">
                        <div>
                            <h2 class="text-sm font-bold text-white">Riwayat Perjalanan (Tracking Timeline)</h2>
                            <p class="text-xs font-light text-[#9ABBC8]">Append-only audit trail checkpoint secara kronologis.</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg bg-[#032324] border border-white/15 text-[#7DE2E6] font-mono font-bold text-xs">
                            {{ shipment.tracking_events?.length || 0 }} Checkpoints
                        </span>
                    </div>

                    <div class="relative pl-6 space-y-6 before:content-[''] before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-white/10">
                        <div v-for="event in shipment.tracking_events" :key="event.id" class="relative group">
                            <!-- Bullet Marker -->
                            <div class="absolute -left-6 top-1 w-4 h-4 rounded-full border-2 border-[#031716] flex items-center justify-center text-[8px]" :class="event.event_type === 'EXCEPTION' ? 'bg-rose-500 text-white shadow-[0_0_8px_#f43f5e]' : (event.status === 'DELIVERED' ? 'bg-emerald-500 text-[#031716] shadow-[0_0_8px_#10b981]' : 'bg-[#0C969C] text-[#031716] shadow-[0_0_8px_#0C969C]')">
                                <i :class="event.event_type === 'EXCEPTION' ? 'bi bi-exclamation' : (event.status === 'DELIVERED' ? 'bi bi-check' : 'bi bi-dot')"></i>
                            </div>

                            <div class="bg-[#031716]/75 border border-white/10 rounded-2xl p-4 shadow-md">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                    <div class="flex items-center gap-2">
                                        <span :class="['px-2 py-0.5 rounded text-[10px] font-mono font-bold border', statusBadgeClass(event.status)]">
                                            {{ event.status }}
                                        </span>
                                        <span class="text-xs font-bold text-white">
                                            {{ event.location ? `${event.location.name} (${event.location.code})` : 'In Transit / High Seas' }}
                                        </span>
                                    </div>
                                    <span class="text-[11px] font-mono text-[#9ABBC8]">
                                        <i class="bi bi-clock me-1"></i>{{ new Date(event.occurred_at).toLocaleString() }}
                                    </span>
                                </div>

                                <p class="text-xs text-[#D6E6EA] mb-2 leading-relaxed">{{ event.description }}</p>

                                <!-- Weather Snapshot Badge -->
                                <div v-if="event.weather_snapshot" class="my-2 px-3 py-1.5 rounded-xl bg-[#032324] border border-white/10 inline-flex items-center gap-2 text-xs">
                                    <i class="bi bi-cloud-sun text-amber-400"></i>
                                    <span class="font-bold text-white">{{ event.weather_snapshot.condition }}</span>
                                    <span class="text-white/25">&bull;</span>
                                    <span class="font-mono text-[#7DE2E6]">{{ event.weather_snapshot.temperature }}&deg;C</span>
                                    <span class="text-white/25">&bull;</span>
                                    <span class="text-[#9ABBC8] font-mono">Angin: {{ event.weather_snapshot.wind_speed ?? event.weather_snapshot.windspeed ?? '-' }} km/h</span>
                                </div>

                                <div class="flex items-center justify-between pt-2 border-t border-white/10 text-[10px] text-[#9ABBC8]">
                                    <span v-if="event.latitude && event.longitude" class="font-mono text-[#9ABBC8]">
                                        <i class="bi bi-geo-alt me-1 text-[#0C969C]"></i>{{ event.latitude }}, {{ event.longitude }}
                                    </span>
                                    <span v-else></span>
                                    <span>Dicatat oleh: <strong class="text-[#D6E6EA]">{{ event.creator?.name || 'System' }}</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manifest Items Table -->
                <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl">
                    <h2 class="text-sm font-bold text-white pb-3 mb-4 border-b border-white/10">Manifest Muatan Kargo (Line Items)</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-[#031716]/75 border-b border-white/10 text-[#9ABBC8] font-mono font-bold uppercase tracking-wider text-[10px]">
                                    <th class="py-2.5 px-3">SKU</th>
                                    <th class="py-2.5 px-3">Deskripsi</th>
                                    <th class="py-2.5 px-3 text-center">Qty</th>
                                    <th class="py-2.5 px-3 text-right">Berat (kg)</th>
                                    <th class="py-2.5 px-3 text-right">Volume (CBM)</th>
                                    <th class="py-2.5 px-3 text-right">Declared Value</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10">
                                <tr v-for="item in shipment.items" :key="item.id" class="hover:bg-white/[0.03]">
                                    <td class="py-2.5 px-3 font-mono font-bold text-[#7DE2E6]">{{ item.sku || '-' }}</td>
                                    <td class="py-2.5 px-3 text-[#D6E6EA]">{{ item.description }}</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-white">{{ item.quantity }}</td>
                                    <td class="py-2.5 px-3 text-right font-mono text-[#D6E6EA]">{{ Number(item.weight_kg).toFixed(2) }} kg</td>
                                    <td class="py-2.5 px-3 text-right font-mono text-[#D6E6EA]">{{ Number(item.volume_cbm).toFixed(2) }} CBM</td>
                                    <td class="py-2.5 px-3 text-right font-mono text-emerald-400 font-semibold">${{ Number(item.declared_value).toLocaleString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Specs Box -->
            <div class="space-y-6">
                <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl space-y-4 text-xs">
                    <h2 class="text-sm font-bold text-white pb-2 border-b border-white/10">Spesifikasi Muatan</h2>

                    <div class="flex justify-between py-1.5 border-b border-white/10">
                        <span class="text-[#9ABBC8]">Total Berat:</span>
                        <span class="font-mono font-bold text-white">{{ Number(shipment.total_weight_kg).toFixed(2) }} kg</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-white/10">
                        <span class="text-[#9ABBC8]">Total Volume:</span>
                        <span class="font-mono font-bold text-white">{{ Number(shipment.total_volume_cbm).toFixed(2) }} CBM</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-white/10">
                        <span class="text-[#9ABBC8]">Klasifikasi:</span>
                        <span class="font-semibold text-white">{{ shipment.cargo_type }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-white/10">
                        <span class="text-[#9ABBC8]">No. Referensi PO:</span>
                        <span class="font-mono text-[#7DE2E6]">{{ shipment.reference_number || '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-white/10">
                        <span class="text-[#9ABBC8]">Koordinat Terakhir:</span>
                        <span class="font-mono text-[#D6E6EA]">
                            {{ shipment.current_lat && shipment.current_lng ? `${shipment.current_lat}, ${shipment.current_lng}` : 'Origin Hub' }}
                        </span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-[#9ABBC8]">Dibuat Oleh:</span>
                        <span class="text-[#D6E6EA] font-semibold">{{ shipment.creator?.name || 'System' }}</span>
                    </div>

                    <div v-if="shipment.notes" class="pt-3 border-t border-white/10">
                        <span class="text-[11px] font-semibold text-[#9ABBC8] block mb-1.5">Catatan Operasional:</span>
                        <div class="p-3 rounded-xl bg-[#031716]/75 border border-white/10 text-xs text-[#D6E6EA] whitespace-pre-line leading-relaxed">{{ shipment.notes }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal 1: Append Checkpoint -->
        <div v-if="showCheckpointModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#031716]/85 backdrop-blur-sm">
            <div class="bg-[#032324]/95 backdrop-blur-2xl rounded-3xl border border-white/15 p-6 max-w-lg w-full shadow-2xl">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-white/10">
                    <h3 class="text-sm font-bold text-white">Catat Checkpoint Tracking Baru</h3>
                    <button @click="showCheckpointModal = false" aria-label="Tutup Modal" class="text-[#9ABBC8] hover:text-white cursor-pointer">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form @submit.prevent="submitCheckpoint" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-[#D6E6EA] mb-1">Lokasi Checkpoint</label>
                        <select v-model="checkpointForm.location_id" @change="onLocationSelect" class="w-full h-10 px-3 bg-[#031716]/80 border border-white/15 rounded-xl text-white">
                            <option value="">Titik Lepas Pantai / Koordinat Langsung</option>
                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                {{ loc.code }} &bull; {{ loc.name }} ({{ loc.country }})
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-[#D6E6EA] mb-1">Status Progresi <span class="text-rose-400">*</span></label>
                            <select v-model="checkpointForm.status" required class="w-full h-10 px-3 bg-[#031716]/80 border border-white/15 rounded-xl text-white">
                                <option value="CREATED">CREATED</option>
                                <option value="BOOKED">BOOKED</option>
                                <option value="PICKED_UP">PICKED_UP</option>
                                <option value="IN_TRANSIT">IN_TRANSIT</option>
                                <option value="DELIVERED">DELIVERED</option>
                                <option value="CANCELLED">CANCELLED</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-[#D6E6EA] mb-1">Tipe Event <span class="text-rose-400">*</span></label>
                            <select v-model="checkpointForm.event_type" required class="w-full h-10 px-3 bg-[#031716]/80 border border-white/15 rounded-xl text-white">
                                <option value="NORMAL">NORMAL</option>
                                <option value="EXCEPTION">EXCEPTION</option>
                                <option value="SYSTEM">SYSTEM</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-[#D6E6EA] mb-1">Latitude</label>
                            <input v-model="checkpointForm.latitude" type="number" step="0.0000001" placeholder="Contoh: 1.290270" class="w-full h-10 px-3 bg-[#031716]/80 border border-white/15 rounded-xl font-mono text-white" />
                        </div>
                        <div>
                            <label class="block font-semibold text-[#D6E6EA] mb-1">Longitude</label>
                            <input v-model="checkpointForm.longitude" type="number" step="0.0000001" placeholder="Contoh: 103.851959" class="w-full h-10 px-3 bg-[#031716]/80 border border-white/15 rounded-xl font-mono text-white" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#D6E6EA] mb-1">Waktu Kejadian (Timestamp) <span class="text-rose-400">*</span></label>
                        <input v-model="checkpointForm.occurred_at" type="datetime-local" required class="w-full h-10 px-3 bg-[#031716]/80 border border-white/15 rounded-xl text-white" />
                        <span class="text-[10px] text-[#9ABBC8]">Waktu tidak boleh lebih lampau dari checkpoint terakhir.</span>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#D6E6EA] mb-1">Deskripsi Checkpoint <span class="text-rose-400">*</span></label>
                        <textarea v-model="checkpointForm.description" rows="2" placeholder="Catatan aktivitas kargo di pelabuhan / lepas pantai..." required class="w-full p-2.5 bg-[#031716]/80 border border-white/15 rounded-xl text-white placeholder-[#9ABBC8]/60"></textarea>
                    </div>

                    <div class="pt-3 border-t border-white/10 flex justify-end gap-2">
                        <button type="button" @click="showCheckpointModal = false" class="px-4 py-2 border border-white/15 bg-white/5 hover:bg-white/10 rounded-full text-[#D6E6EA] font-semibold cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" :disabled="checkpointForm.processing" class="px-5 py-2 bg-[#274D60] hover:bg-[#0A7075] text-white font-semibold rounded-full disabled:opacity-60 cursor-pointer shadow-lg shadow-black/25">
                            Simpan Checkpoint
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal 2: Transition Status -->
        <div v-if="showTransitionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#031716]/85 backdrop-blur-sm">
            <div class="bg-[#032324]/95 backdrop-blur-2xl rounded-3xl border border-white/15 p-6 max-w-md w-full shadow-2xl">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-white/10">
                    <h3 class="text-sm font-bold text-white">Transisi Status Pengiriman</h3>
                    <button @click="showTransitionModal = false" aria-label="Tutup Modal" class="text-[#9ABBC8] hover:text-white cursor-pointer">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form @submit.prevent="submitTransition" class="space-y-4 text-xs">
                    <p class="text-[#9ABBC8]">Status saat ini: <strong class="text-[#7DE2E6] font-mono">{{ shipment.current_status }}</strong></p>

                    <div>
                        <label class="block font-semibold text-[#D6E6EA] mb-1">Status Baru <span class="text-rose-400">*</span></label>
                        <select v-model="transitionForm.status" required class="w-full h-10 px-3 bg-[#031716]/80 border border-white/15 rounded-xl text-white">
                            <option value="">Pilih Status Baru...</option>
                            <option v-for="st in ['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED']" :key="st" :value="st">
                                {{ st }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-[#D6E6EA] mb-1">Alasan Perubahan Status</label>
                        <textarea v-model="transitionForm.reason" rows="2" placeholder="Catatan log audit transisi status..." class="w-full p-2.5 bg-[#031716]/80 border border-white/15 rounded-xl text-white placeholder-[#9ABBC8]/60"></textarea>
                    </div>

                    <div class="pt-3 border-t border-white/10 flex justify-end gap-2">
                        <button type="button" @click="showTransitionModal = false" class="px-4 py-2 border border-white/15 bg-white/5 hover:bg-white/10 rounded-full text-[#D6E6EA] font-semibold cursor-pointer">
                            Batal
                        </button>
                        <button type="submit" :disabled="transitionForm.processing" class="px-5 py-2 bg-[#274D60] hover:bg-[#0A7075] text-white font-semibold rounded-full disabled:opacity-60 cursor-pointer shadow-lg shadow-black/25">
                            Perbarui Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>