<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ShipmentRouteMap from '@/Components/ShipmentRouteMap.vue';
import Modal from '@/Components/Modal.vue';
import VehicleGraphic from '@/Components/VehicleGraphic.vue';
import BillOfLadingModal from '@/Components/BillOfLadingModal.vue';
import { estimateCarbonFootprint } from '@/Utils/carbonCalculator';

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
const isCustomer = computed(() => user.value.role === 'customer');

const showCheckpointModal = ref(false);
const showTransitionModal = ref(false);
const showBolModal = ref(false);
const copiedLink = ref(false);

const carbon = computed(() => {
    return estimateCarbonFootprint(props.shipment);
});

const copyPublicTrackingLink = () => {
    const url = `${window.location.origin}/?track=${encodeURIComponent(props.shipment.shipment_number)}`;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            copiedLink.value = true;
            setTimeout(() => {
                copiedLink.value = false;
            }, 2500);
        });
    }
};

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
    const loc = props.locations.find((l) => l.id === Number(checkpointForm.location_id));
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
        case 'CREATED': return 'bg-slate-100 text-slate-800 border-slate-300';
        case 'BOOKED':
        case 'PICKED_UP': return 'bg-[#EAF6F4] text-[#13293D] border-[#67C7C6]/40';
        case 'IN_TRANSIT': return 'bg-amber-500/15 text-amber-900 border-amber-600/30';
        case 'DELIVERED': return 'bg-emerald-500/15 text-emerald-900 border-emerald-600/30';
        case 'CANCELLED': return 'bg-rose-500/15 text-rose-900 border-rose-600/30';
        default: return 'bg-slate-100 text-slate-800 border-slate-300';
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Shipment ${shipment.shipment_number}`" />

        <!-- Top Header Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 mb-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <h1 class="text-xl sm:text-2xl font-black font-mono tracking-tight text-[#13293D]">
                        {{ shipment.shipment_number }}
                    </h1>
                    <span :class="['px-2.5 py-0.5 rounded border font-mono font-bold text-xs', statusBadgeClass(shipment.current_status)]">
                        {{ shipment.current_status }}
                    </span>
                    <span v-if="shipment.is_delayed" class="px-2.5 py-0.5 rounded bg-rose-50 border border-rose-200 text-rose-700 text-xs font-mono font-bold inline-flex items-center gap-1">
                        <i class="bi bi-clock-history"></i>
                        <span>DELAY (+{{ shipment.delay_duration_hours }}j)</span>
                    </span>
                    <span v-else class="px-2.5 py-0.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-mono font-bold inline-flex items-center gap-1">
                        <i class="bi bi-check-circle"></i>
                        <span>ON-TIME</span>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full bg-[#13293D] text-[#A7E4DB] text-xs font-mono font-bold uppercase">
                        {{ shipment.carrier?.transport_mode || 'SEA' }} FREIGHT
                    </span>
                </div>
                <p class="text-xs text-[#265D7A]">
                    Didaftarkan: {{ new Date(shipment.created_at).toLocaleDateString() }} &bull; Customer: <strong class="text-[#13293D]">{{ shipment.customer?.name || 'Customer' }}</strong> ({{ shipment.customer?.company_name || 'Client Corp' }})
                </p>
            </div>

            <!-- Action Controls -->
            <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                <Link
                    href="/shipments"
                    class="px-3.5 py-1.5 border border-slate-200 hover:border-slate-300 bg-slate-50 hover:bg-white text-xs font-semibold rounded-xl text-[#13293D] transition inline-flex items-center gap-1.5 shadow-2xs"
                >
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </Link>

                <!-- Copy Shareable Tracking Link -->
                <button
                    type="button"
                    @click="copyPublicTrackingLink"
                    class="px-3.5 py-1.5 rounded-xl border border-slate-200 hover:border-[#67C7C6] bg-slate-50 hover:bg-white text-xs font-semibold text-[#13293D] inline-flex items-center gap-1.5 transition cursor-pointer shadow-2xs"
                    title="Salin tautan pelacakan publik untuk consignee"
                >
                    <i :class="['bi text-xs', copiedLink ? 'bi-check2 text-emerald-600 font-bold' : 'bi-share text-[#265D7A]']"></i>
                    <span>{{ copiedLink ? 'Link Tersalin!' : 'Bagi Link Lacak' }}</span>
                </button>

                <!-- Print / View e-Bill of Lading -->
                <button
                    type="button"
                    @click="showBolModal = true"
                    class="px-3.5 py-1.5 rounded-xl border border-slate-200 hover:border-[#67C7C6] bg-slate-50 hover:bg-white text-xs font-semibold text-[#13293D] inline-flex items-center gap-1.5 transition cursor-pointer shadow-2xs"
                    title="Cetak atau unduh dokumen Bill of Lading resmi"
                >
                    <i class="bi bi-file-earmark-text text-[#265D7A]"></i>
                    <span>e-Bill of Lading</span>
                </button>

                <!-- Operator / Admin Buttons -->
                <template v-if="isAdmin || isOperator">
                    <button
                        type="button"
                        @click="showCheckpointModal = true"
                        class="px-4 py-1.5 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-xl transition inline-flex items-center gap-1.5 shadow-xs cursor-pointer"
                    >
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>Catat Checkpoint</span>
                    </button>
                    <button
                        type="button"
                        @click="showTransitionModal = true"
                        class="px-3.5 py-1.5 bg-slate-50 hover:bg-white border border-slate-200 text-xs font-semibold rounded-xl text-[#13293D] transition inline-flex items-center gap-1.5 cursor-pointer shadow-2xs"
                    >
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Ubah Status</span>
                    </button>
                    <Link
                        :href="`/shipments/${shipment.id}/edit`"
                        class="p-2 border border-slate-200 bg-slate-50 hover:bg-white text-[#13293D] rounded-xl text-xs transition shadow-2xs"
                        title="Edit Metadata"
                        aria-label="Edit Metadata"
                    >
                        <i class="bi bi-pencil"></i>
                    </Link>
                </template>
            </div>
        </div>

        <!-- Hero Vehicle & Cockpit Overview Card (Haulboard Style) -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 mb-5 shadow-xs overflow-hidden">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-center bg-gradient-to-b from-slate-50/80 to-white p-4 rounded-xl border border-slate-100">
                <!-- Left Telemetry Column -->
                <div class="space-y-3">
                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 shadow-2xs">
                        <span class="text-[10px] font-mono uppercase text-[#265D7A] font-bold block">Armada Pengangkut</span>
                        <div class="font-extrabold text-[#13293D] text-sm truncate mt-0.5">{{ shipment.carrier?.name || 'Carrier' }}</div>
                        <div class="text-[10px] text-slate-500 font-mono">{{ shipment.carrier?.code }} &bull; {{ shipment.carrier?.transport_mode }}</div>
                    </div>

                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 shadow-2xs">
                        <span class="text-[10px] font-mono uppercase text-[#265D7A] font-bold block">Muatan &amp; Klasifikasi</span>
                        <div class="font-extrabold text-[#13293D] text-sm truncate mt-0.5">{{ shipment.cargo_type || 'General Cargo' }}</div>
                        <div class="text-[10px] text-slate-500 font-mono">
                            {{ Number(shipment.total_weight_kg).toFixed(1) }} kg &bull; {{ Number(shipment.total_volume_cbm).toFixed(2) }} CBM
                        </div>
                    </div>
                </div>

                <!-- Center: Vehicle Graphic (3D Render) -->
                <div class="lg:col-span-2 flex flex-col items-center justify-center p-2 relative">
                    <VehicleGraphic
                        :mode="shipment.carrier?.transport_mode"
                        class-name="w-full max-w-md h-40 sm:h-48"
                    />
                    <div class="mt-1 text-center">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#13293D] text-[#A7E4DB] text-[10px] font-mono font-bold tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#67C7C6] animate-pulse"></span>
                            <span>ARMADA AKTIF &bull; {{ shipment.carrier?.transport_mode === 'AIR' ? 'CARGO FREIGHTER' : (shipment.carrier?.transport_mode === 'LAND' ? 'SEMI-TRAILER TRUCK' : 'CONTAINER VESSEL') }}</span>
                        </span>
                    </div>
                </div>

                <!-- Right Telemetry Column (Carbon & Reference) -->
                <div class="space-y-3">
                    <!-- Carbon Footprint -->
                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 shadow-2xs">
                        <div class="flex items-center justify-between mb-0.5">
                            <span class="text-[10px] font-mono uppercase text-emerald-800 font-bold">Jejak Karbon (CO₂)</span>
                            <i class="bi bi-tree text-emerald-600 text-xs"></i>
                        </div>
                        <div class="font-black text-emerald-900 text-sm font-mono">
                            {{ carbon.emissionsKg }} kg CO₂e
                        </div>
                        <div class="text-[10px] text-emerald-700 font-medium">
                            {{ carbon.ecoRating }} &bull; GLEC Standard
                        </div>
                    </div>

                    <!-- Reference PO & Last Coordinate -->
                    <div class="bg-white p-3 rounded-xl border border-slate-200/70 shadow-2xs">
                        <span class="text-[10px] font-mono uppercase text-[#265D7A] font-bold block">No. Referensi PO / Dokumen</span>
                        <div class="font-bold text-[#13293D] text-xs font-mono truncate mt-0.5">
                            {{ shipment.reference_number || 'TIDAK TERTERA' }}
                        </div>
                        <div class="text-[10px] text-slate-500 font-mono mt-0.5">
                            Koordinat: {{ shipment.current_lat && shipment.current_lng ? `${Number(shipment.current_lat).toFixed(4)}, ${Number(shipment.current_lng).toFixed(4)}` : 'Origin Port' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Route Progression Stepper Bar -->
            <div class="mt-4 pt-4 border-t border-slate-100">
                <div class="flex items-center justify-between text-xs font-mono">
                    <div class="text-left">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Pelabuhan Muat (Origin)</span>
                        <strong class="text-[#13293D] font-bold text-sm">{{ shipment.origin_location?.code }}</strong>
                        <span class="text-[11px] text-[#265D7A] block truncate max-w-[160px]">{{ shipment.origin_location?.name }}</span>
                        <span class="text-[10px] text-slate-400">
                            ETD: {{ new Date(shipment.expected_departure).toLocaleDateString() }}
                        </span>
                    </div>

                    <div class="flex-1 px-4 text-center">
                        <div class="relative flex items-center justify-center">
                            <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                <div
                                    class="h-full bg-gradient-to-r from-[#265D7A] to-[#67C7C6] rounded-full transition-all duration-500"
                                    :style="{ width: shipment.current_status === 'DELIVERED' ? '100%' : (shipment.current_status === 'IN_TRANSIT' ? '60%' : '20%') }"
                                ></div>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-[#265D7A] mt-1 inline-block">
                            {{ shipment.current_status === 'DELIVERED' ? 'Tuntas Terkirim ke Tujuan Akhir' : 'Dalam Perjalanan Menuju Destinasi' }}
                        </span>
                    </div>

                    <div class="text-right">
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Pelabuhan Bongkar (Dest)</span>
                        <strong class="text-emerald-700 font-bold text-sm">{{ shipment.destination_location?.code }}</strong>
                        <span class="text-[11px] text-[#265D7A] block truncate max-w-[160px]">{{ shipment.destination_location?.name }}</span>
                        <span class="text-[10px] text-slate-400">
                            ETA: {{ new Date(shipment.actual_arrival || shipment.estimated_arrival || shipment.expected_arrival).toLocaleDateString() }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <!-- Left: Geospatial Map, Timeline & Manifest Line Items -->
            <div class="lg:col-span-2 space-y-5">
                <!-- Interactive Dark Canvas Map with Live Radar Beacon -->
                <ShipmentRouteMap :shipment="shipment" height="420px" :live-mode="true" />

                <!-- Chronological Tracking Timeline -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs">
                    <div class="flex items-center justify-between pb-3 mb-6 border-b border-slate-100">
                        <div>
                            <h2 class="text-sm font-bold text-[#13293D] uppercase tracking-wider">
                                Riwayat Perjalanan (Tracking Timeline)
                            </h2>
                            <p class="text-xs text-[#265D7A]">Catatan checkpoint kronologis dan kondisi cuaca telemetri maritim.</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg bg-[#13293D] text-[#A7E4DB] font-mono font-bold text-xs">
                            {{ shipment.tracking_events?.length || 0 }} Checkpoints
                        </span>
                    </div>

                    <div v-if="shipment.tracking_events?.length" class="relative pl-6 space-y-6 before:content-[''] before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                        <div v-for="event in shipment.tracking_events" :key="event.id" class="relative group">
                            <!-- Bullet Marker -->
                            <div
                                class="absolute -left-6 top-1 w-4 h-4 rounded-full border-2 border-white flex items-center justify-center text-[8px] shadow-xs"
                                :class="event.event_type === 'EXCEPTION' ? 'bg-rose-600 text-white' : (event.status === 'DELIVERED' ? 'bg-emerald-600 text-white' : 'bg-[#265D7A] text-white')"
                            >
                                <i :class="event.event_type === 'EXCEPTION' ? 'bi bi-exclamation' : (event.status === 'DELIVERED' ? 'bi bi-check' : 'bi bi-dot')"></i>
                            </div>

                            <div class="bg-slate-50/80 border border-slate-200/70 rounded-xl p-4 shadow-2xs">
                                <div class="flex flex-wrap items-center justify-between gap-2 mb-2">
                                    <div class="flex items-center gap-2">
                                        <span :class="['px-2 py-0.5 rounded text-[10px] font-mono font-bold border', statusBadgeClass(event.status)]">
                                            {{ event.status }}
                                        </span>
                                        <span class="text-xs font-bold text-[#13293D]">
                                            {{ event.location ? `${event.location.name} (${event.location.code})` : 'Lepas Pantai / High Seas In-Transit' }}
                                        </span>
                                    </div>
                                    <span class="text-[11px] font-mono text-slate-500">
                                        <i class="bi bi-clock me-1"></i>{{ new Date(event.occurred_at).toLocaleString() }}
                                    </span>
                                </div>

                                <p class="text-xs text-[#265D7A] mb-2 leading-relaxed">{{ event.description }}</p>

                                <!-- Weather Telemetry Snapshot Chip -->
                                <div v-if="event.weather_snapshot" class="my-2 px-3 py-1.5 rounded-xl bg-white border border-slate-200 inline-flex items-center gap-2 text-xs shadow-2xs">
                                    <i class="bi bi-cloud-sun text-amber-500"></i>
                                    <span class="font-bold text-[#13293D]">{{ event.weather_snapshot.condition }}</span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span class="font-mono font-bold text-[#13293D]">{{ event.weather_snapshot.temperature }}&deg;C</span>
                                    <span class="text-slate-300">&bull;</span>
                                    <span class="text-[#265D7A] font-mono">Angin: {{ event.weather_snapshot.wind_speed ?? event.weather_snapshot.windspeed ?? '-' }} km/h</span>
                                </div>

                                <div class="flex items-center justify-between pt-2 border-t border-slate-200/50 text-[10px] text-slate-500">
                                    <span v-if="event.latitude && event.longitude" class="font-mono">
                                        <i class="bi bi-geo-alt me-1 text-slate-400"></i>{{ Number(event.latitude).toFixed(4) }}, {{ Number(event.longitude).toFixed(4) }}
                                    </span>
                                    <span v-else></span>
                                    <span>Dicatat oleh: <strong class="text-[#13293D]">{{ event.creator?.name || 'System Auto' }}</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="py-6 text-center text-xs text-slate-400">
                        Belum ada checkpoint tercatat. Checkpoint baru akan muncul di sini secara kronologis.
                    </div>
                </div>

                <!-- Manifest Items Table (Line Items) -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                        <h2 class="text-sm font-bold text-[#13293D] uppercase tracking-wider">
                            Manifest Muatan Kargo (Line Items)
                        </h2>
                        <button
                            type="button"
                            @click="showBolModal = true"
                            class="text-xs font-bold text-[#265D7A] hover:text-[#13293D] inline-flex items-center gap-1 transition cursor-pointer"
                        >
                            <i class="bi bi-printer"></i>
                            <span>Cetak Dokumen</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-[#265D7A] font-mono font-bold uppercase tracking-wider text-[10px]">
                                    <th class="py-2.5 px-3">SKU</th>
                                    <th class="py-2.5 px-3">Deskripsi</th>
                                    <th class="py-2.5 px-3 text-center">Qty</th>
                                    <th class="py-2.5 px-3 text-right">Berat (kg)</th>
                                    <th class="py-2.5 px-3 text-right">Volume (CBM)</th>
                                    <th class="py-2.5 px-3 text-right">Declared Value</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <tr v-for="item in shipment.items" :key="item.id" class="hover:bg-slate-50">
                                    <td class="py-2.5 px-3 font-mono font-bold text-[#265D7A]">{{ item.sku || '-' }}</td>
                                    <td class="py-2.5 px-3 text-[#13293D] font-medium">{{ item.description }}</td>
                                    <td class="py-2.5 px-3 text-center font-mono font-bold text-[#13293D]">{{ item.quantity }}</td>
                                    <td class="py-2.5 px-3 text-right font-mono text-slate-600">{{ Number(item.weight_kg).toFixed(2) }} kg</td>
                                    <td class="py-2.5 px-3 text-right font-mono text-slate-600">{{ Number(item.volume_cbm).toFixed(2) }} CBM</td>
                                    <td class="py-2.5 px-3 text-right font-mono text-emerald-800 font-bold">${{ Number(item.declared_value).toLocaleString() }}</td>
                                </tr>
                                <tr v-if="!(shipment.items && shipment.items.length)">
                                    <td colspan="6" class="py-4 text-center text-slate-400">
                                        Tidak ada rincian item spesifik pada manifest ini. Muatan dicatat secara gelondongan (Full Container Load).
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column: Specs & Operational Notes -->
            <div class="space-y-5">
                <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs space-y-4 text-xs">
                    <h2 class="text-sm font-bold text-[#13293D] pb-2 border-b border-slate-100 uppercase tracking-wider">
                        Spesifikasi Teknis Muatan
                    </h2>

                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Total Berat:</span>
                        <span class="font-mono font-bold text-[#13293D]">{{ Number(shipment.total_weight_kg).toFixed(2) }} kg</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Total Volume:</span>
                        <span class="font-mono font-bold text-[#13293D]">{{ Number(shipment.total_volume_cbm).toFixed(2) }} CBM</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Klasifikasi Kargo:</span>
                        <span class="font-bold text-[#13293D]">{{ shipment.cargo_type }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">No. Referensi PO:</span>
                        <span class="font-mono font-bold text-[#265D7A]">{{ shipment.reference_number || '-' }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Koordinat Live:</span>
                        <span class="font-mono text-[#13293D]">
                            {{ shipment.current_lat && shipment.current_lng ? `${Number(shipment.current_lat).toFixed(4)}, ${Number(shipment.current_lng).toFixed(4)}` : 'Origin Port' }}
                        </span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-slate-100">
                        <span class="text-slate-500">Emisi Karbon:</span>
                        <span class="font-mono font-bold text-emerald-800">{{ carbon.emissionsKg }} kg CO₂e</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-slate-500">Didaftarkan Oleh:</span>
                        <span class="text-[#13293D] font-bold">{{ shipment.creator?.name || 'System' }}</span>
                    </div>

                    <div v-if="shipment.notes" class="pt-3 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-500 block mb-1.5">Catatan Operasional:</span>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-[#13293D] whitespace-pre-line leading-relaxed">
                            {{ shipment.notes }}
                        </div>
                    </div>
                </div>

                <!-- Green Logistics GLEC Badge -->
                <div class="bg-gradient-to-br from-emerald-500/10 to-teal-500/10 border border-emerald-500/25 rounded-2xl p-5 shadow-xs text-xs space-y-2">
                    <div class="flex items-center gap-2 text-emerald-800 font-bold">
                        <i class="bi bi-shield-check text-base"></i>
                        <span>Green Logistics Certified</span>
                    </div>
                    <p class="text-emerald-900 leading-relaxed text-[11px]">
                        Rute pengiriman ini diperhitungkan menggunakan protokol GLEC framework dengan faktor emisi moda {{ shipment.carrier?.transport_mode }}.
                    </p>
                    <div class="pt-2 flex items-center justify-between text-[10px] font-mono text-emerald-800 border-t border-emerald-500/20">
                        <span>Efisiensi: {{ carbon.ecoRating }}</span>
                        <span>{{ carbon.factorLabel }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal 1: Append Checkpoint (Admin/Operator) -->
        <Modal :show="showCheckpointModal" max-width="lg" @close="showCheckpointModal = false">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-200">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#265D7A]"></span>
                    <h3 class="text-sm font-bold text-[#13293D]">Catat Checkpoint Tracking Baru</h3>
                </div>
                <button @click="showCheckpointModal = false" aria-label="Tutup Modal" class="text-slate-400 hover:text-[#13293D] cursor-pointer p-1">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form @submit.prevent="submitCheckpoint" class="space-y-4 text-xs">
                <div>
                    <label class="block font-bold text-[#13293D] mb-1">Lokasi Checkpoint</label>
                    <select v-model="checkpointForm.location_id" @change="onLocationSelect" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-[#13293D]">
                        <option value="">Titik Lepas Pantai / Koordinat Langsung</option>
                        <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                            {{ loc.code }} &bull; {{ loc.name }} ({{ loc.country }})
                        </option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#13293D] mb-1">Status Progresi <span class="text-rose-600">*</span></label>
                        <select v-model="checkpointForm.status" required class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-[#13293D]">
                            <option value="CREATED">CREATED</option>
                            <option value="BOOKED">BOOKED</option>
                            <option value="PICKED_UP">PICKED_UP</option>
                            <option value="IN_TRANSIT">IN_TRANSIT</option>
                            <option value="DELIVERED">DELIVERED</option>
                            <option value="CANCELLED">CANCELLED</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-[#13293D] mb-1">Tipe Event <span class="text-rose-600">*</span></label>
                        <select v-model="checkpointForm.event_type" required class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-[#13293D]">
                            <option value="NORMAL">NORMAL</option>
                            <option value="EXCEPTION">EXCEPTION</option>
                            <option value="SYSTEM">SYSTEM</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-[#13293D] mb-1">Latitude</label>
                        <input v-model="checkpointForm.latitude" type="number" step="0.0000001" placeholder="Contoh: 1.290270" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl font-mono text-[#13293D]" />
                    </div>
                    <div>
                        <label class="block font-bold text-[#13293D] mb-1">Longitude</label>
                        <input v-model="checkpointForm.longitude" type="number" step="0.0000001" placeholder="Contoh: 103.851959" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl font-mono text-[#13293D]" />
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-[#13293D] mb-1">Waktu Kejadian (Timestamp) <span class="text-rose-600">*</span></label>
                    <input v-model="checkpointForm.occurred_at" type="datetime-local" required class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-[#13293D]" />
                    <span class="text-[10px] text-slate-500">Waktu tidak boleh lebih lampau dari checkpoint terakhir.</span>
                </div>

                <div>
                    <label class="block font-bold text-[#13293D] mb-1">Deskripsi Checkpoint <span class="text-rose-600">*</span></label>
                    <textarea v-model="checkpointForm.description" rows="2" placeholder="Catatan aktivitas kargo di pelabuhan / lepas pantai..." required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-[#13293D] placeholder-slate-400"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="showCheckpointModal = false" class="px-4 py-2 border border-slate-200 bg-white hover:bg-slate-50 rounded-xl text-[#13293D] font-semibold cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" :disabled="checkpointForm.processing" class="px-5 py-2 bg-[#265D7A] hover:bg-[#13293D] text-white font-semibold rounded-xl disabled:opacity-60 cursor-pointer shadow-xs">
                        Simpan Checkpoint
                    </button>
                </div>
            </form>
        </Modal>

        <!-- Modal 2: Transition Status (Admin/Operator) -->
        <Modal :show="showTransitionModal" max-width="md" @close="showTransitionModal = false">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-200">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-[#67C7C6]"></span>
                    <h3 class="text-sm font-bold text-[#13293D]">Transisi Status Pengiriman</h3>
                </div>
                <button @click="showTransitionModal = false" aria-label="Tutup Modal" class="text-slate-400 hover:text-[#13293D] cursor-pointer p-1">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <form @submit.prevent="submitTransition" class="space-y-4 text-xs">
                <p class="text-slate-500">Status saat ini: <strong class="text-[#13293D] font-mono">{{ shipment.current_status }}</strong></p>

                <div>
                    <label class="block font-bold text-[#13293D] mb-1">Status Baru <span class="text-rose-600">*</span></label>
                    <select v-model="transitionForm.status" required class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-[#13293D]">
                        <option value="">Pilih Status Baru...</option>
                        <option v-for="st in ['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED']" :key="st" :value="st">
                            {{ st }}
                        </option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-[#13293D] mb-1">Alasan Perubahan Status</label>
                    <textarea v-model="transitionForm.reason" rows="2" placeholder="Catatan log audit transisi status..." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-[#13293D] placeholder-slate-400"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-200 flex justify-end gap-2">
                    <button type="button" @click="showTransitionModal = false" class="px-4 py-2 border border-slate-200 bg-white hover:bg-slate-50 rounded-xl text-[#13293D] font-semibold cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" :disabled="transitionForm.processing" class="px-5 py-2 bg-[#265D7A] hover:bg-[#13293D] text-white font-semibold rounded-xl disabled:opacity-60 cursor-pointer shadow-xs">
                        Perbarui Status
                    </button>
                </div>
            </form>
        </Modal>

        <!-- e-Bill of Lading Document Modal -->
        <BillOfLadingModal
            :show="showBolModal"
            :shipment="shipment"
            @close="showBolModal = false"
        />
    </AuthenticatedLayout>
</template>
