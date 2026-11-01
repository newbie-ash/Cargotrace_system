<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

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
        case 'CREATED': return 'bg-gray-100 text-gray-700 border-gray-300';
        case 'BOOKED':
        case 'PICKED_UP': return 'bg-blue-50 text-blue-700 border-blue-200';
        case 'IN_TRANSIT': return 'bg-amber-50 text-amber-800 border-amber-300';
        case 'DELIVERED': return 'bg-emerald-50 text-emerald-700 border-emerald-300';
        case 'CANCELLED': return 'bg-rose-50 text-rose-700 border-rose-300';
        default: return 'bg-gray-50 text-gray-700 border-gray-200';
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Shipment ${shipment.shipment_number}`" />

        <!-- Header -->
        <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 mb-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h1 class="text-xl font-bold font-mono tracking-tight text-[#1A1A1C]">{{ shipment.shipment_number }}</h1>
                    <span :class="['px-2.5 py-0.5 rounded-md border font-mono font-bold text-xs', statusBadgeClass(shipment.current_status)]">
                        {{ shipment.current_status }}
                    </span>
                    <span v-if="shipment.is_delayed" class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 text-xs font-semibold inline-flex items-center gap-1">
                        <i class="bi bi-clock-history"></i>
                        <span>DELAYED (+{{ shipment.delay_duration_hours }}j)</span>
                    </span>
                    <span v-else class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-semibold inline-flex items-center gap-1">
                        <i class="bi bi-check-circle"></i>
                        <span>ON-TIME</span>
                    </span>
                </div>
                <p class="text-xs text-[#706E6B]">
                    Didaftarkan pada {{ new Date(shipment.created_at).toLocaleDateString() }} &bull; Customer: <strong>{{ shipment.customer?.name || 'N/A' }}</strong>
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Link href="/shipments" class="px-3 py-1.5 border border-[#D8D2C4] hover:bg-[#F2EFE9] text-xs font-semibold rounded-xl text-[#1A1A1C] transition inline-flex items-center gap-1.5">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </Link>

                <template v-if="isAdmin || isOperator">
                    <button @click="showCheckpointModal = true" class="px-3.5 py-1.5 bg-[#1A1A1C] hover:bg-[#2C2C2E] text-white text-xs font-semibold rounded-xl transition inline-flex items-center gap-1.5 shadow-xs">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>Catat Checkpoint</span>
                    </button>
                    <button @click="showTransitionModal = true" class="px-3.5 py-1.5 bg-[#F2EFE9] hover:bg-[#E5E0D5] border border-[#D8D2C4] text-xs font-semibold rounded-xl text-[#1A1A1C] transition inline-flex items-center gap-1.5">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Ubah Status</span>
                    </button>
                    <Link :href="`/shipments/${shipment.id}/edit`" class="p-2 border border-[#D8D2C4] hover:bg-[#F2EFE9] text-[#1A1A1C] rounded-xl text-xs" title="Edit Metadata">
                        <i class="bi bi-pencil"></i>
                    </Link>
                </template>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Route Banner, Timeline & Line Items -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Route Card -->
                <div class="bg-white/80 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 shadow-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-3 items-center gap-4 text-center sm:text-left">
                        <div>
                            <span class="text-[10px] font-bold text-[#706E6B] uppercase tracking-wider block">Origin (Asal)</span>
                            <div class="text-lg font-mono font-extrabold text-[#1A1A1C]">{{ shipment.origin_location?.code }}</div>
                            <div class="text-xs font-semibold text-[#1A1A1C]">{{ shipment.origin_location?.name }}</div>
                            <div class="text-[11px] text-[#706E6B]">{{ shipment.origin_location?.city }}, {{ shipment.origin_location?.country }}</div>
                            <div class="text-[11px] text-[#706E6B] mt-2">
                                ETD: <strong>{{ new Date(shipment.expected_departure).toLocaleDateString() }}</strong>
                            </div>
                        </div>

                        <div class="flex flex-col items-center justify-center text-center">
                            <span class="px-2 py-0.5 rounded bg-gray-100 border border-[#D8D2C4] text-[10px] font-mono text-[#1A1A1C] mb-1">
                                {{ shipment.carrier?.code }} &bull; {{ shipment.carrier?.transport_mode }}
                            </span>
                            <i class="bi bi-arrow-right text-2xl text-[#706E6B] hidden sm:block"></i>
                            <i class="bi bi-arrow-down text-2xl text-[#706E6B] sm:hidden"></i>
                            <div class="text-[10px] text-[#706E6B] font-semibold">{{ shipment.carrier?.name }}</div>
                        </div>

                        <div class="sm:text-right">
                            <span class="text-[10px] font-bold text-[#706E6B] uppercase tracking-wider block">Destination (Tujuan)</span>
                            <div class="text-lg font-mono font-extrabold text-[#1A1A1C]">{{ shipment.destination_location?.code }}</div>
                            <div class="text-xs font-semibold text-[#1A1A1C]">{{ shipment.destination_location?.name }}</div>
                            <div class="text-[11px] text-[#706E6B]">{{ shipment.destination_location?.city }}, {{ shipment.destination_location?.country }}</div>
                            <div class="text-[11px] text-[#706E6B] mt-2">
                                ETA: <strong>{{ new Date(shipment.actual_arrival || shipment.estimated_arrival || shipment.expected_arrival).toLocaleDateString() }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chronological Tracking Timeline -->
                <div class="bg-white/80 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 shadow-xs">
                    <div class="flex items-center justify-between pb-3 mb-6 border-b border-[#E5E0D5]">
                        <div>
                            <h2 class="text-sm font-bold text-[#1A1A1C]">Riwayat Perjalanan (Tracking Timeline)</h2>
                            <p class="text-xs text-[#706E6B]">Append-only audit trail checkpoint secara kronologis.</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg bg-gray-100 text-[#1A1A1C] font-mono font-bold text-xs">
                            {{ shipment.tracking_events?.length || 0 }} Checkpoints
                        </span>
                    </div>

                    <div class="relative pl-6 space-y-6 before:content-[''] before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-[#E5E0D5]">
                        <div v-for="event in shipment.tracking_events" :key="event.id" class="relative group">
                            <!-- Bullet Marker -->
                            <div class="absolute -left-6 top-1 w-4 h-4 rounded-full border-2 border-white flex items-center justify-center text-[8px]" :class="event.event_type === 'EXCEPTION' ? 'bg-rose-600 text-white' : (event.status === 'DELIVERED' ? 'bg-emerald-600 text-white' : 'bg-[#1A1A1C] text-white')">
                                <i :class="event.event_type === 'EXCEPTION' ? 'bi bi-exclamation' : (event.status === 'DELIVERED' ? 'bi bi-check' : 'bi bi-dot')"></i>
                            </div>

                            <div class="bg-white/90 border border-[#E5E0D5] rounded-xl p-4 shadow-2xs">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                    <div class="flex items-center gap-2">
                                        <span :class="['px-2 py-0.5 rounded text-[10px] font-mono font-bold border', statusBadgeClass(event.status)]">
                                            {{ event.status }}
                                        </span>
                                        <span class="text-xs font-bold text-[#1A1A1C]">
                                            {{ event.location ? `${event.location.name} (${event.location.code})` : 'In Transit / High Seas' }}
                                        </span>
                                    </div>
                                    <span class="text-[11px] font-mono text-[#706E6B]">
                                        <i class="bi bi-clock me-1"></i>{{ new Date(event.occurred_at).toLocaleString() }}
                                    </span>
                                </div>

                                <p class="text-xs text-[#1A1A1C] mb-2 leading-relaxed">{{ event.description }}</p>

                                <!-- Weather Snapshot Badge -->
                                <div v-if="event.weather_snapshot" class="my-2 p-2 rounded-lg bg-[#F6F3EC] border border-[#E5E0D5] inline-flex items-center gap-2 text-xs">
                                    <i class="bi bi-cloud-sun text-blue-600"></i>
                                    <span class="font-semibold text-[#1A1A1C]">{{ event.weather_snapshot.condition }}</span>
                                    <span class="text-[#706E6B]">&bull;</span>
                                    <span>{{ event.weather_snapshot.temperature }}&deg;C</span>
                                    <span class="text-[#706E6B]">&bull;</span>
                                    <span class="text-[#706E6B]">Angin: {{ event.weather_snapshot.windspeed }} km/h</span>
                                </div>

                                <div class="flex items-center justify-between pt-2 border-t border-[#E5E0D5] text-[10px] text-[#706E6B]">
                                    <span v-if="event.latitude && event.longitude" class="font-mono">
                                        <i class="bi bi-geo-alt me-1"></i>{{ event.latitude }}, {{ event.longitude }}
                                    </span>
                                    <span v-else></span>
                                    <span>Dicatat oleh: <strong>{{ event.creator?.name || 'System' }}</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manifest Items Table -->
                <div class="bg-white/80 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 shadow-xs">
                    <h2 class="text-sm font-bold text-[#1A1A1C] pb-3 mb-4 border-b border-[#E5E0D5]">Manifest Muatan Kargo (Line Items)</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-[#F2EFE9] border-b border-[#E5E0D5] text-[#706E6B] font-bold uppercase tracking-wider text-[11px]">
                                    <th class="py-2.5 px-3">SKU</th>
                                    <th class="py-2.5 px-3">Deskripsi</th>
                                    <th class="py-2.5 px-3 text-center">Qty</th>
                                    <th class="py-2.5 px-3 text-right">Berat (kg)</th>
                                    <th class="py-2.5 px-3 text-right">Volume (CBM)</th>
                                    <th class="py-2.5 px-3 text-right">Declared Value</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E0D5]">
                                <tr v-for="item in shipment.items" :key="item.id">
                                    <td class="py-2.5 px-3 font-mono font-semibold">{{ item.sku || '-' }}</td>
                                    <td class="py-2.5 px-3">{{ item.description }}</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold">{{ item.quantity }}</td>
                                    <td class="py-2.5 px-3 text-right font-mono">{{ Number(item.weight_kg).toFixed(2) }} kg</td>
                                    <td class="py-2.5 px-3 text-right font-mono">{{ Number(item.volume_cbm).toFixed(2) }} CBM</td>
                                    <td class="py-2.5 px-3 text-right font-mono">${{ Number(item.declared_value).toLocaleString() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right: Specs Box -->
            <div class="space-y-6">
                <div class="bg-white/80 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 shadow-xs space-y-4 text-xs">
                    <h2 class="text-sm font-bold text-[#1A1A1C] pb-2 border-b border-[#E5E0D5]">Spesifikasi Muatan</h2>

                    <div class="flex justify-between py-1.5 border-b border-[#E5E0D5]">
                        <span class="text-[#706E6B]">Total Berat:</span>
                        <span class="font-mono font-bold text-[#1A1A1C]">{{ Number(shipment.total_weight_kg).toFixed(2) }} kg</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-[#E5E0D5]">
                        <span class="text-[#706E6B]">Total Volume:</span>
                        <span class="font-mono font-bold text-[#1A1A1C]">{{ Number(shipment.total_volume_cbm).toFixed(2) }} CBM</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-[#E5E0D5]">
                        <span class="text-[#706E6B]">Klasifikasi:</span>
                        <span class="font-semibold text-[#1A1A1C]">{{ shipment.cargo_type }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-[#E5E0D5]">
                        <span class="text-[#706E6B]">No. Referensi PO:</span>
                        <span class="font-mono text-[#1A1A1C]">{{ shipment.reference_number || '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-[#E5E0D5]">
                        <span class="text-[#706E6B]">Koordinat Terakhir:</span>
                        <span class="font-mono text-[#1A1A1C]">
                            {{ shipment.current_lat && shipment.current_lng ? `${shipment.current_lat}, ${shipment.current_lng}` : 'Origin Hub' }}
                        </span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-[#706E6B]">Dibuat Oleh:</span>
                        <span>{{ shipment.creator?.name || 'System' }}</span>
                    </div>

                    <div v-if="shipment.notes" class="pt-3 border-t border-[#E5E0D5]">
                        <span class="text-[11px] font-semibold text-[#706E6B] block mb-1">Catatan Operasional:</span>
                        <div class="p-2.5 rounded-xl bg-[#F6F3EC] border border-[#E5E0D5] text-xs whitespace-pre-line">{{ shipment.notes }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal 1: Append Checkpoint -->
        <div v-if="showCheckpointModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
            <div class="bg-white rounded-2xl border border-[#E5E0D5] p-6 max-w-lg w-full shadow-lg">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#E5E0D5]">
                    <h3 class="text-sm font-bold text-[#1A1A1C]">Catat Checkpoint Tracking Baru</h3>
                    <button @click="showCheckpointModal = false" class="text-[#706E6B] hover:text-black">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form @submit.prevent="submitCheckpoint" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold mb-1">Lokasi Checkpoint</label>
                        <select v-model="checkpointForm.location_id" @change="onLocationSelect" class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl">
                            <option value="">Titik Lepas Pantai / Koordinat Langsung</option>
                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                {{ loc.code }} &bull; {{ loc.name }} ({{ loc.country }})
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold mb-1">Status Progresi <span class="text-rose-600">*</span></label>
                            <select v-model="checkpointForm.status" required class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl">
                                <option value="CREATED">CREATED</option>
                                <option value="BOOKED">BOOKED</option>
                                <option value="PICKED_UP">PICKED_UP</option>
                                <option value="IN_TRANSIT">IN_TRANSIT</option>
                                <option value="DELIVERED">DELIVERED</option>
                                <option value="CANCELLED">CANCELLED</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Tipe Event <span class="text-rose-600">*</span></label>
                            <select v-model="checkpointForm.event_type" required class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl">
                                <option value="NORMAL">NORMAL</option>
                                <option value="EXCEPTION">EXCEPTION</option>
                                <option value="SYSTEM">SYSTEM</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold mb-1">Latitude</label>
                            <input v-model="checkpointForm.latitude" type="number" step="0.0000001" placeholder="Contoh: 1.290270" class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl font-mono" />
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Longitude</label>
                            <input v-model="checkpointForm.longitude" type="number" step="0.0000001" placeholder="Contoh: 103.851959" class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl font-mono" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold mb-1">Waktu Kejadian (Timestamp) <span class="text-rose-600">*</span></label>
                        <input v-model="checkpointForm.occurred_at" type="datetime-local" required class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl" />
                        <span class="text-[10px] text-[#706E6B]">Waktu tidak boleh lebih lampau dari checkpoint terakhir.</span>
                    </div>

                    <div>
                        <label class="block font-semibold mb-1">Deskripsi Checkpoint <span class="text-rose-600">*</span></label>
                        <textarea v-model="checkpointForm.description" rows="2" placeholder="Catatan aktivitas kargo di pelabuhan / lepas pantai..." required class="w-full p-2.5 bg-white border border-[#D8D2C4] rounded-xl"></textarea>
                    </div>

                    <div class="pt-3 border-t border-[#E5E0D5] flex justify-end gap-2">
                        <button type="button" @click="showCheckpointModal = false" class="px-4 py-2 border border-[#D8D2C4] rounded-xl hover:bg-[#F2EFE9] font-semibold">
                            Batal
                        </button>
                        <button type="submit" :disabled="checkpointForm.processing" class="px-5 py-2 bg-[#1A1A1C] hover:bg-[#2C2C2E] text-white font-semibold rounded-xl disabled:opacity-75">
                            Simpan Checkpoint
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal 2: Transition Status -->
        <div v-if="showTransitionModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
            <div class="bg-white rounded-2xl border border-[#E5E0D5] p-6 max-w-md w-full shadow-lg">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#E5E0D5]">
                    <h3 class="text-sm font-bold text-[#1A1A1C]">Transisi Status Pengiriman</h3>
                    <button @click="showTransitionModal = false" class="text-[#706E6B] hover:text-black">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <form @submit.prevent="submitTransition" class="space-y-4 text-xs">
                    <p class="text-[#706E6B]">Status saat ini: <strong class="text-black">{{ shipment.current_status }}</strong></p>

                    <div>
                        <label class="block font-semibold mb-1">Status Baru <span class="text-rose-600">*</span></label>
                        <select v-model="transitionForm.status" required class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl">
                            <option value="">Pilih Status Baru...</option>
                            <option v-for="st in ['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED']" :key="st" :value="st">
                                {{ st }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold mb-1">Alasan Perubahan Status</label>
                        <textarea v-model="transitionForm.reason" rows="2" placeholder="Catatan log audit transisi status..." class="w-full p-2.5 bg-white border border-[#D8D2C4] rounded-xl"></textarea>
                    </div>

                    <div class="pt-3 border-t border-[#E5E0D5] flex justify-end gap-2">
                        <button type="button" @click="showTransitionModal = false" class="px-4 py-2 border border-[#D8D2C4] rounded-xl hover:bg-[#F2EFE9] font-semibold">
                            Batal
                        </button>
                        <button type="submit" :disabled="transitionForm.processing" class="px-5 py-2 bg-[#1A1A1C] hover:bg-[#2C2C2E] text-white font-semibold rounded-xl disabled:opacity-75">
                            Perbarui Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
