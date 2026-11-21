<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DashboardCharts from '@/Components/DashboardCharts.vue';
import ShipmentRouteMap from '@/Components/ShipmentRouteMap.vue';
import VehicleGraphic from '@/Components/VehicleGraphic.vue';
import BillOfLadingModal from '@/Components/BillOfLadingModal.vue';
import { estimateCarbonFootprint } from '@/Utils/carbonCalculator';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const isAdmin = computed(() => user.value.role === 'admin');
const isOperator = computed(() => user.value.role === 'operator');
const isCustomer = computed(() => user.value.role === 'customer');

// Customer Interactive Real-Time Tracking State
const customerShipments = computed(() => props.stats.recent_shipments || []);
const selectedShipmentId = ref(null);
const shipmentSearch = ref('');
const statusFilterTab = ref('ALL'); // ALL, IN_TRANSIT, DELIVERED, DELAYED
const showBolModal = ref(false);
const copiedLink = ref(false);

const copyPublicTrackingLink = (shipmentNumber) => {
    if (!shipmentNumber) return;
    const url = `${window.location.origin}/?track=${encodeURIComponent(shipmentNumber)}`;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            copiedLink.value = true;
            setTimeout(() => {
                copiedLink.value = false;
            }, 2500);
        });
    }
};

const selectedCarbon = computed(() => {
    return estimateCarbonFootprint(selectedShipment.value);
});

const latestWeather = computed(() => {
    if (!selectedShipment.value) return null;
    const events = selectedShipment.value.tracking_events || [];
    for (let i = events.length - 1; i >= 0; i--) {
        if (events[i].weather_snapshot) {
            return events[i].weather_snapshot;
        }
    }
    return null;
});

watch(
    () => customerShipments.value,
    (list) => {
        if (!selectedShipmentId.value && list.length > 0) {
            selectedShipmentId.value = list[0].id;
        } else if (selectedShipmentId.value && !list.some((s) => s.id === selectedShipmentId.value)) {
            selectedShipmentId.value = list[0]?.id || null;
        }
    },
    { immediate: true }
);

const selectedShipment = computed(() => {
    return (
        customerShipments.value.find((s) => s.id === selectedShipmentId.value) ||
        customerShipments.value[0] ||
        null
    );
});

const filteredCustomerShipments = computed(() => {
    let list = customerShipments.value;
    if (shipmentSearch.value.trim()) {
        const q = shipmentSearch.value.trim().toLowerCase();
        list = list.filter(
            (s) =>
                (s.shipment_number || '').toLowerCase().includes(q) ||
                (s.cargo_type || '').toLowerCase().includes(q) ||
                (s.carrier?.name || '').toLowerCase().includes(q) ||
                (s.origin_location?.name || '').toLowerCase().includes(q) ||
                (s.destination_location?.name || '').toLowerCase().includes(q)
        );
    }
    if (statusFilterTab.value === 'IN_TRANSIT') {
        list = list.filter((s) => s.current_status === 'IN_TRANSIT');
    } else if (statusFilterTab.value === 'DELIVERED') {
        list = list.filter((s) => s.current_status === 'DELIVERED');
    } else if (statusFilterTab.value === 'DELAYED') {
        list = list.filter((s) => s.is_delayed);
    }
    return list;
});

// Real-Time Polling & Live Telemetry Sync
const isAutoSync = ref(true);
const isSyncing = ref(false);
const lastSyncedAt = ref(new Date());
let pollTimer = null;

const syncLive = () => {
    if (isSyncing.value) return;
    isSyncing.value = true;
    router.reload({
        only: ['stats'],
        preserveState: true,
        preserveScroll: true,
        onFinish: () => {
            isSyncing.value = false;
            lastSyncedAt.value = new Date();
        },
    });
};

const toggleAutoSync = () => {
    isAutoSync.value = !isAutoSync.value;
    if (isAutoSync.value) {
        startPolling();
    } else {
        stopPolling();
    }
};

const startPolling = () => {
    stopPolling();
    pollTimer = setInterval(() => {
        if (isAutoSync.value && typeof document !== 'undefined' && !document.hidden) {
            syncLive();
        }
    }, 30000);
};

const stopPolling = () => {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
};

onMounted(() => {
    if (isCustomer.value) {
        startPolling();
    }
});

onUnmounted(() => {
    stopPolling();
});

const statusBadgeClass = (st) => {
    switch (st) {
        case 'CREATED':
            return 'bg-slate-100 text-slate-800 border-slate-300';
        case 'BOOKED':
        case 'PICKED_UP':
            return 'bg-[#EAF6F4] text-[#13293D] border-[#67C7C6]/40';
        case 'IN_TRANSIT':
            return 'bg-amber-500/15 text-amber-900 border-amber-600/30';
        case 'DELIVERED':
            return 'bg-emerald-500/15 text-emerald-900 border-emerald-600/30';
        case 'CANCELLED':
            return 'bg-rose-500/15 text-rose-900 border-rose-600/30';
        default:
            return 'bg-slate-100 text-slate-800 border-slate-300';
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="isCustomer ? 'Customer Cargo Tracking' : 'Operational Dashboard'" />

        <!-- CUSTOMER VIEW (De-Noised White Surface with Live Geospatial Telemetry) -->
        <div v-if="isCustomer" class="space-y-5">
            <!-- Header Banner (Welcome & Sync Controls) -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h1 class="text-xl sm:text-2xl font-extrabold text-[#13293D] tracking-tight">
                            Pelacakan &amp; Pemantauan Kargo
                        </h1>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-[10px] font-mono font-bold text-emerald-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>LIVE</span>
                        </span>
                    </div>
                    <p class="text-xs text-[#265D7A]">
                        Selamat datang, <strong class="text-[#13293D]">{{ user.name }}</strong> ({{ user.company_name || 'Customer Portal' }}). Pantau posisi armada dan estimasi tiba kontainer Anda secara langsung.
                    </p>
                </div>

                <!-- Sync Controls -->
                <div class="flex items-center gap-2.5 shrink-0">
                    <button
                        type="button"
                        @click="toggleAutoSync"
                        :class="[
                            'px-3 py-1.5 text-xs font-semibold rounded-full border transition inline-flex items-center gap-1.5 cursor-pointer',
                            isAutoSync ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : 'bg-slate-100 border-slate-300 text-slate-600'
                        ]"
                        :title="isAutoSync ? 'Auto-sync aktif (tiap 30 detik)' : 'Auto-sync dijeda'"
                    >
                        <i :class="['bi', isAutoSync ? 'bi-arrow-repeat' : 'bi-pause-circle']"></i>
                        <span>{{ isAutoSync ? 'Auto-Sync: Aktif' : 'Auto-Sync: Jeda' }}</span>
                    </button>

                    <button
                        type="button"
                        @click="syncLive"
                        :disabled="isSyncing"
                        class="px-4 py-1.5 text-xs font-semibold bg-[#265D7A] hover:bg-[#13293D] text-white rounded-full transition shadow-xs inline-flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
                    >
                        <i :class="['bi bi-arrow-clockwise', isSyncing ? 'animate-spin' : '']"></i>
                        <span>{{ isSyncing ? 'Memperbarui...' : 'Sinkronkan' }}</span>
                    </button>
                </div>
            </div>

            <!-- 4 Clean Customer KPI Cards (Biswas / TransGlobal inspired) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Card 1: Total Kargo -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
                    <div class="flex items-center justify-between text-[#265D7A] text-[11px] font-mono font-bold uppercase tracking-wider mb-2">
                        <span>Total Kargo Saya</span>
                        <div class="w-8 h-8 rounded-xl bg-[#EAF6F4] text-[#265D7A] flex items-center justify-center text-xs">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-extrabold text-[#13293D] font-mono">{{ stats.total_shipments }}</div>
                    <div class="text-[11px] text-[#265D7A] mt-0.5">Kontainer terdaftar akun</div>
                </div>

                <!-- Card 2: Dalam Perjalanan (In Transit) -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
                    <div class="flex items-center justify-between text-amber-800 text-[11px] font-mono font-bold uppercase tracking-wider mb-2">
                        <span>Dalam Pelayaran</span>
                        <div class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-800 flex items-center justify-center text-xs">
                            <i class="bi bi-water"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-extrabold text-amber-800 font-mono">{{ stats.in_transit }}</div>
                    <div class="text-[11px] text-[#265D7A] mt-0.5">Sedang menuju destinasi</div>
                </div>

                <!-- Card 3: Tiba di Tujuan (Delivered) -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
                    <div class="flex items-center justify-between text-emerald-800 text-[11px] font-mono font-bold uppercase tracking-wider mb-2">
                        <span>Tuntas Terkirim</span>
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-800 flex items-center justify-center text-xs">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-extrabold text-emerald-800 font-mono">{{ stats.delivered }}</div>
                    <div class="text-[11px] text-[#265D7A] mt-0.5">Tiba di pelabuhan tujuan</div>
                </div>

                <!-- Card 4: Terlambat / Delay -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
                    <div class="flex items-center justify-between text-rose-800 text-[11px] font-mono font-bold uppercase tracking-wider mb-2">
                        <span>Terlambat / Delay</span>
                        <div class="w-8 h-8 rounded-xl bg-rose-500/15 text-rose-800 flex items-center justify-center text-xs">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-extrabold text-rose-800 font-mono">{{ stats.delayed }}</div>
                    <div class="text-[11px] text-[#265D7A] mt-0.5">Delay rate: <strong class="text-rose-800 font-mono">{{ stats.delay_rate }}%</strong></div>
                </div>
            </div>

            <!-- Interactive Split Real-Time Tracking Zone (Yellow Express + Biswas + TransGlobal) -->
            <div class="flex flex-col lg:flex-row gap-5 items-start">
                <!-- Left Column: Shipment Selector & Route Checkpoints Timeline -->
                <div class="w-full lg:w-80 xl:w-96 shrink-0 flex flex-col gap-4">
                    <!-- Shipment Selector Card (Yellow Express style) -->
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs w-full">
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                            <div>
                                <h2 class="text-xs font-bold text-[#13293D] uppercase tracking-wider">Kargo Aktif</h2>
                                <p class="text-[11px] text-[#265D7A]">Pilih untuk melihat pemantauan rute</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-full bg-[#EAF6F4] text-[#13293D] font-mono text-[10px] font-bold">
                                {{ filteredCustomerShipments.length }} Kargo
                            </span>
                        </div>

                        <!-- Search & Quick Filters -->
                        <div class="space-y-2 mb-3">
                            <div class="relative">
                                <i class="bi bi-search absolute left-3 top-2.5 text-slate-400 text-xs pointer-events-none"></i>
                                <input
                                    v-model="shipmentSearch"
                                    type="text"
                                    placeholder="Cari resi / jenis kargo..."
                                    class="w-full h-8 pl-8 pr-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-[#13293D] placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-[#67C7C6] focus:border-[#265D7A]"
                                />
                            </div>

                            <div class="flex items-center gap-1 text-[10px] font-semibold">
                                <button
                                    type="button"
                                    @click="statusFilterTab = 'ALL'"
                                    :class="[
                                        'px-2 py-0.5 rounded-lg transition cursor-pointer',
                                        statusFilterTab === 'ALL' ? 'bg-[#13293D] text-[#A7E4DB]' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                    ]"
                                >
                                    Semua
                                </button>
                                <button
                                    type="button"
                                    @click="statusFilterTab = 'IN_TRANSIT'"
                                    :class="[
                                        'px-2 py-0.5 rounded-lg transition cursor-pointer',
                                        statusFilterTab === 'IN_TRANSIT' ? 'bg-[#13293D] text-[#A7E4DB]' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                    ]"
                                >
                                    Berjalan
                                </button>
                                <button
                                    type="button"
                                    @click="statusFilterTab = 'DELAYED'"
                                    :class="[
                                        'px-2 py-0.5 rounded-lg transition cursor-pointer',
                                        statusFilterTab === 'DELAYED' ? 'bg-rose-700 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                    ]"
                                >
                                    Terlambat
                                </button>
                            </div>
                        </div>

                        <!-- Shipments Scrollable List -->
                        <div v-if="filteredCustomerShipments.length" class="max-h-80 overflow-y-auto space-y-2 pr-1">
                            <button
                                v-for="s in filteredCustomerShipments"
                                :key="s.id"
                                type="button"
                                @click="selectedShipmentId = s.id"
                                :class="[
                                    'w-full text-left p-3 rounded-xl border transition cursor-pointer block',
                                    selectedShipmentId === s.id
                                        ? 'bg-[#EAF6F4] border-[#67C7C6] shadow-xs'
                                        : 'bg-white hover:bg-slate-50 border-slate-200/70'
                                ]"
                            >
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <span class="font-mono font-bold text-xs text-[#13293D]">
                                        {{ s.shipment_number }}
                                    </span>
                                    <span :class="['px-2 py-0.5 rounded border text-[9px] font-mono font-bold', statusBadgeClass(s.current_status)]">
                                        {{ s.current_status }}
                                    </span>
                                </div>
                                <div class="text-[11px] text-[#265D7A] truncate font-medium">
                                    {{ s.cargo_type || 'General Cargo' }} &bull; {{ s.carrier?.name || 'Carrier' }}
                                </div>
                                <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-200/50 text-[10px] text-slate-500 font-mono">
                                    <span>{{ s.origin_location?.code }} &rarr; {{ s.destination_location?.code }}</span>
                                    <span v-if="s.is_delayed" class="text-rose-600 font-bold">
                                        +{{ s.delay_duration_hours }}j Delay
                                    </span>
                                    <span v-else class="text-emerald-700 font-semibold">
                                        On-Time
                                    </span>
                                </div>
                            </button>
                        </div>
                        <div v-else class="py-6 text-center text-xs text-slate-400">
                            Tidak ada pengiriman sesuai filter.
                        </div>
                    </div>

                    <!-- Route Checkpoints Timeline Card (TransGlobal style) -->
                    <div v-if="selectedShipment" class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs w-full">
                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100">
                            <h2 class="text-xs font-bold text-[#13293D] uppercase tracking-wider flex items-center gap-1.5">
                                <i class="bi bi-signpost-split text-[#265D7A]"></i>
                                <span>Timeline Checkpoint</span>
                            </h2>
                            <span class="text-[10px] font-mono text-[#265D7A]">
                                {{ (selectedShipment.tracking_events || []).length }} Checkpoint
                            </span>
                        </div>

                        <!-- Vertical Checkpoint List -->
                        <div class="relative pl-4 space-y-4 before:absolute before:left-1.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                            <!-- Origin Node -->
                            <div class="relative">
                                <span class="absolute -left-4 top-1 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-4 ring-white"></span>
                                <div class="text-[10px] font-mono uppercase font-bold text-emerald-700">Asal (Origin)</div>
                                <div class="text-xs font-bold text-[#13293D]">{{ selectedShipment.origin_location?.name }}</div>
                                <div class="text-[10px] text-[#265D7A] font-mono">
                                    {{ selectedShipment.expected_departure ? new Date(selectedShipment.expected_departure).toLocaleDateString([], { day: 'numeric', month: 'short' }) : '-' }}
                                </div>
                            </div>

                            <!-- Recorded Events -->
                            <div
                                v-for="(evt, idx) in (selectedShipment.tracking_events || []).slice().sort((a,b) => new Date(a.occurred_at) - new Date(b.occurred_at))"
                                :key="evt.id || idx"
                                class="relative"
                            >
                                <span class="absolute -left-4 top-1 w-2.5 h-2.5 rounded-full bg-[#67C7C6] ring-4 ring-white"></span>
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-[10px] font-mono font-bold text-[#265D7A] uppercase">
                                        {{ evt.location?.name || `Checkpoint #${idx + 1}` }}
                                    </span>
                                    <span class="text-[9px] font-mono text-slate-400">
                                        {{ new Date(evt.occurred_at).toLocaleDateString([], { day: 'numeric', month: 'short' }) }}
                                    </span>
                                </div>
                                <div class="text-xs text-[#13293D] font-medium">{{ evt.status }}</div>
                                <div v-if="evt.weather_snapshot" class="mt-1 text-[10px] text-amber-800 bg-amber-50 px-2 py-0.5 rounded-md inline-flex items-center gap-1 font-mono">
                                    <i class="bi bi-cloud-sun text-amber-600"></i>
                                    <span>{{ evt.weather_snapshot.condition }} &bull; {{ evt.weather_snapshot.temperature }}°C</span>
                                </div>
                            </div>

                            <!-- Destination Node -->
                            <div class="relative">
                                <span class="absolute -left-4 top-1 w-2.5 h-2.5 rounded-full bg-rose-500 ring-4 ring-white"></span>
                                <div class="text-[10px] font-mono uppercase font-bold text-rose-700">Tujuan (Destination)</div>
                                <div class="text-xs font-bold text-[#13293D]">{{ selectedShipment.destination_location?.name }}</div>
                                <div class="text-[10px] text-[#265D7A] font-mono">
                                    ETA: {{ (selectedShipment.estimated_arrival || selectedShipment.expected_arrival) ? new Date(selectedShipment.estimated_arrival || selectedShipment.expected_arrival).toLocaleDateString([], { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Cargo Spec Overview & Real-Time Track Monitoring Map -->
                <div class="flex-1 min-w-0 w-full flex flex-col gap-4">
                    <!-- Hero Vehicle Showcase & Telemetry Cockpit (Haulboard & TransGlobal style) -->
                    <div v-if="selectedShipment" class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs overflow-hidden">
                        <!-- Top Header & Action Buttons -->
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
                            <div>
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <span class="text-xs font-mono font-bold text-[#265D7A] uppercase">Resi Pengiriman</span>
                                    <span :class="['px-2 py-0.5 rounded border text-[10px] font-mono font-bold', statusBadgeClass(selectedShipment.current_status)]">
                                        {{ selectedShipment.current_status }}
                                    </span>
                                    <span v-if="selectedShipment.is_delayed" class="px-2 py-0.5 rounded bg-rose-50 border border-rose-200 text-rose-700 text-[10px] font-mono font-bold">
                                        TERLAMBAT (+{{ selectedShipment.delay_duration_hours }}j)
                                    </span>
                                    <span v-else class="px-2 py-0.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-mono font-bold">
                                        SESUAI JADWAL
                                    </span>
                                    <span class="px-2 py-0.5 rounded-full bg-[#13293D] text-[#A7E4DB] text-[10px] font-mono font-bold uppercase">
                                        {{ selectedShipment.carrier?.transport_mode || 'SEA' }} FREIGHT
                                    </span>
                                </div>
                                <h3 class="text-xl sm:text-2xl font-black text-[#13293D] font-mono tracking-tight">
                                    {{ selectedShipment.shipment_number }}
                                </h3>
                            </div>

                            <!-- 3 Quick Executive Action Buttons -->
                            <div class="flex flex-wrap items-center gap-2 self-start md:self-auto">
                                <!-- 1. Copy Public Tracking Link -->
                                <button
                                    type="button"
                                    @click="copyPublicTrackingLink(selectedShipment.shipment_number)"
                                    class="px-3.5 py-1.5 rounded-xl border border-slate-200 hover:border-[#67C7C6] bg-slate-50 hover:bg-white text-xs font-semibold text-[#13293D] inline-flex items-center gap-1.5 transition cursor-pointer shadow-2xs"
                                    :title="'Salin tautan pelacakan publik untuk consignee'"
                                >
                                    <i :class="['bi text-xs', copiedLink ? 'bi-check2 text-emerald-600 font-bold' : 'bi-share text-[#265D7A]']"></i>
                                    <span>{{ copiedLink ? 'Link Tersalin!' : 'Bagi Link Lacak' }}</span>
                                </button>

                                <!-- 2. Download / Print Bill of Lading (B/L) -->
                                <button
                                    type="button"
                                    @click="showBolModal = true"
                                    class="px-3.5 py-1.5 rounded-xl border border-slate-200 hover:border-[#67C7C6] bg-slate-50 hover:bg-white text-xs font-semibold text-[#13293D] inline-flex items-center gap-1.5 transition cursor-pointer shadow-2xs"
                                    title="Cetak atau unduh dokumen Bill of Lading resmi"
                                >
                                    <i class="bi bi-file-earmark-text text-[#265D7A]"></i>
                                    <span>e-Bill of Lading</span>
                                </button>

                                <!-- 3. Full Details Link -->
                                <Link
                                    :href="`/shipments/${selectedShipment.id}`"
                                    class="px-4 py-1.5 rounded-xl bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold inline-flex items-center gap-1.5 transition shadow-xs cursor-pointer"
                                >
                                    <span>Detail Kargo</span>
                                    <i class="bi bi-arrow-right text-xs"></i>
                                </Link>
                            </div>
                        </div>

                        <!-- Central Cockpit: Telemetry Chips & 3D Vehicle Graphic (Haulboard Style) -->
                        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 items-center my-1 bg-gradient-to-b from-slate-50/80 to-white p-4 rounded-xl border border-slate-100">
                            <!-- Left Telemetry Column -->
                            <div class="space-y-3">
                                <div class="bg-white p-3 rounded-xl border border-slate-200/70 shadow-2xs">
                                    <span class="text-[10px] font-mono uppercase text-[#265D7A] font-bold block">Armada Carrier</span>
                                    <div class="font-extrabold text-[#13293D] text-sm truncate mt-0.5">{{ selectedShipment.carrier?.name || 'Carrier' }}</div>
                                    <div class="text-[10px] text-slate-500 font-mono">{{ selectedShipment.carrier?.code || 'ID' }} &bull; {{ selectedShipment.carrier?.transport_mode }}</div>
                                </div>

                                <div class="bg-white p-3 rounded-xl border border-slate-200/70 shadow-2xs">
                                    <span class="text-[10px] font-mono uppercase text-[#265D7A] font-bold block">Muatan Kargo (Payload)</span>
                                    <div class="font-extrabold text-[#13293D] text-sm truncate mt-0.5">{{ selectedShipment.cargo_type || 'General Cargo' }}</div>
                                    <div class="text-[10px] text-slate-500 font-mono">
                                        {{ selectedShipment.total_weight_kg || selectedShipment.weight_kg ? `${selectedShipment.total_weight_kg || selectedShipment.weight_kg} kg` : 'Dry Container' }} &bull; {{ selectedShipment.total_volume_cbm ? `${selectedShipment.total_volume_cbm} CBM` : 'Standard' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Center: Vehicle Graphic Render (Ship / Plane / Truck) -->
                            <div class="lg:col-span-2 flex flex-col items-center justify-center p-2 relative">
                                <VehicleGraphic
                                    :mode="selectedShipment.carrier?.transport_mode"
                                    class-name="w-full max-w-md h-40 sm:h-48"
                                />
                                <div class="mt-1 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#13293D] text-[#A7E4DB] text-[10px] font-mono font-bold tracking-wider">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#67C7C6] animate-pulse"></span>
                                        <span>ARMADA AKTIF &bull; {{ selectedShipment.carrier?.transport_mode === 'AIR' ? 'CARGO FREIGHTER' : (selectedShipment.carrier?.transport_mode === 'LAND' ? 'SEMI-TRAILER TRUCK' : 'CONTAINER VESSEL') }}</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Right Telemetry Column (Carbon & Weather) -->
                            <div class="space-y-3">
                                <!-- Carbon Footprint Indicator -->
                                <div class="bg-white p-3 rounded-xl border border-slate-200/70 shadow-2xs">
                                    <div class="flex items-center justify-between mb-0.5">
                                        <span class="text-[10px] font-mono uppercase text-emerald-800 font-bold">Jejak Karbon (CO₂)</span>
                                        <i class="bi bi-tree text-emerald-600 text-xs"></i>
                                    </div>
                                    <div class="font-black text-emerald-900 text-sm font-mono">
                                        {{ selectedCarbon.emissionsKg }} kg CO₂e
                                    </div>
                                    <div class="text-[10px] text-emerald-700 font-medium">
                                        {{ selectedCarbon.ecoRating }} &bull; GLEC Std.
                                    </div>
                                </div>

                                <!-- Marine / Route Weather Telemetry -->
                                <div class="bg-white p-3 rounded-xl border border-slate-200/70 shadow-2xs">
                                    <div class="flex items-center justify-between mb-0.5">
                                        <span class="text-[10px] font-mono uppercase text-[#265D7A] font-bold">Cuaca Rute Terkini</span>
                                        <i class="bi bi-cloud-sun text-amber-500 text-xs"></i>
                                    </div>
                                    <div v-if="latestWeather" class="font-bold text-[#13293D] text-xs truncate">
                                        {{ latestWeather.condition }} &bull; {{ latestWeather.temperature }}&deg;C
                                    </div>
                                    <div v-else class="font-bold text-[#13293D] text-xs truncate">
                                        Kondisi Rute Baik &bull; 28&deg;C
                                    </div>
                                    <div class="text-[10px] text-slate-500 font-mono">
                                        Angin: {{ latestWeather?.wind_speed ?? 18 }} km/h &bull; Jalur Terbuka
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Route Progression Bar (Haulboard Stepper Style) -->
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <div class="flex items-center justify-between text-xs font-mono mb-2">
                                <div class="text-left">
                                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Keberangkatan</span>
                                    <strong class="text-[#13293D] font-bold text-sm">{{ selectedShipment.origin_location?.code }}</strong>
                                    <span class="text-[11px] text-[#265D7A] block truncate max-w-[140px]">{{ selectedShipment.origin_location?.city }}</span>
                                    <span class="text-[10px] text-slate-400">
                                        {{ selectedShipment.expected_departure ? new Date(selectedShipment.expected_departure).toLocaleDateString([], { day: 'numeric', month: 'short' }) : '-' }}
                                    </span>
                                </div>

                                <div class="flex-1 px-4 text-center">
                                    <div class="relative flex items-center justify-center">
                                        <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                            <div
                                                class="h-full bg-gradient-to-r from-[#265D7A] to-[#67C7C6] rounded-full transition-all duration-500"
                                                :style="{ width: selectedShipment.current_status === 'DELIVERED' ? '100%' : (selectedShipment.current_status === 'IN_TRANSIT' ? '60%' : '20%') }"
                                            ></div>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-bold text-[#265D7A] mt-1 inline-block">
                                        {{ selectedShipment.current_status === 'DELIVERED' ? 'Muatan Tiba di Pelabuhan Akhir' : 'Dalam Perjalanan Menuju Destinasi' }}
                                    </span>
                                </div>

                                <div class="text-right">
                                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Kedatangan (ETA)</span>
                                    <strong class="text-emerald-700 font-bold text-sm">{{ selectedShipment.destination_location?.code }}</strong>
                                    <span class="text-[11px] text-[#265D7A] block truncate max-w-[140px]">{{ selectedShipment.destination_location?.city }}</span>
                                    <span class="text-[10px] text-slate-400">
                                        {{ (selectedShipment.estimated_arrival || selectedShipment.expected_arrival) ? new Date(selectedShipment.estimated_arrival || selectedShipment.expected_arrival).toLocaleDateString([], { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Real-Time Track Monitoring Map -->
                    <div v-if="selectedShipment" class="w-full">
                        <ShipmentRouteMap
                            :shipment="selectedShipment"
                            height="460px"
                            :live-mode="true"
                        />
                    </div>
                    <div v-else class="bg-white border border-slate-200/80 rounded-2xl p-12 text-center text-slate-400">
                        <i class="bi bi-box-seam text-3xl mb-2 block"></i>
                        <p class="font-bold text-[#13293D]">Belum Ada Pengiriman Terdaftar</p>
                        <p class="text-xs text-[#265D7A] mt-1">Akun Anda belum memiliki data pengiriman yang aktif.</p>
                    </div>
                </div>
            </div>

            <!-- De-Noised Shortcut Hub: Quick Navigation Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <Link
                    href="/shipments"
                    class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs hover:border-[#67C7C6] transition group flex items-center justify-between"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#EAF6F4] text-[#265D7A] flex items-center justify-center text-base group-hover:bg-[#13293D] group-hover:text-[#A7E4DB] transition">
                            <i class="bi bi-box-seam-fill"></i>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-[#13293D]">Kargo Saya (Shipments)</div>
                            <div class="text-[11px] text-[#265D7A]">Katalog &amp; dual view tracking</div>
                        </div>
                    </div>
                    <i class="bi bi-arrow-right text-slate-400 group-hover:text-[#13293D] transition"></i>
                </Link>

                <Link
                    href="/documents"
                    class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs hover:border-[#67C7C6] transition group flex items-center justify-between"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-base group-hover:bg-[#13293D] group-hover:text-[#A7E4DB] transition">
                            <i class="bi bi-file-earmark-text-fill"></i>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-[#13293D]">Dokumen &amp; e-B/L</div>
                            <div class="text-[11px] text-emerald-800">Cetak &amp; arsip Bill of Lading resmi</div>
                        </div>
                    </div>
                    <i class="bi bi-arrow-right text-slate-400 group-hover:text-[#13293D] transition"></i>
                </Link>

                <Link
                    href="/calculator"
                    class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs hover:border-[#67C7C6] transition group flex items-center justify-between"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-base group-hover:bg-[#13293D] group-hover:text-[#A7E4DB] transition">
                            <i class="bi bi-calculator-fill"></i>
                        </div>
                        <div>
                            <div class="font-bold text-xs text-[#13293D]">Kalkulator Tarif &amp; CO₂</div>
                            <div class="text-[11px] text-amber-800">Simulasi biaya &amp; jejak emisi rute</div>
                        </div>
                    </div>
                    <i class="bi bi-arrow-right text-slate-400 group-hover:text-[#13293D] transition"></i>
                </Link>
            </div>
        </div>

        <!-- ADMIN & OPERATOR VIEW (Existing Operational Dashboard) -->
        <div v-else>
            <!-- Header Banner -->
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 mb-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)]">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2.5 mb-1">
                            <h1 class="text-2xl font-extrabold tracking-tight text-[#13293D]">Operational Dashboard</h1>
                            <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold rounded-full bg-[#13293D] text-[#A7E4DB] uppercase tracking-wider">
                                {{ user.role }}
                            </span>
                        </div>
                        <p class="text-xs text-[#265D7A] font-normal">
                            Platform visibilitas terpadu &amp; pelacakan rantai pasok kargo global interkontinental.
                        </p>
                    </div>

                    <div v-if="isAdmin || isOperator" class="flex gap-2">
                        <Link href="/shipments/create" class="px-5 py-2 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-full transition inline-flex items-center gap-2 shadow-md shadow-[#13293D]/15">
                            <i class="bi bi-plus-lg"></i>
                            <span>Buat Shipment</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- 8 Dynamic Metric KPI Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <!-- 1. Total Shipments -->
                <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-2xl p-5 shadow-[0_12px_30px_rgba(19,41,61,0.06)] hover:border-[#67C7C6] transition">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#265D7A]">Total Shipments</span>
                        <div class="w-8 h-8 rounded-xl bg-[#EAF6F4] border border-[#265D7A]/20 text-[#265D7A] flex items-center justify-center">
                            <i class="bi bi-box-seam"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-extrabold text-[#13293D] font-mono">{{ stats.total_shipments }}</div>
                    <div class="text-[11px] text-[#265D7A] mt-1">Kargo terdaftar sistem</div>
                </div>

                <!-- 2. In Transit -->
                <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-2xl p-5 shadow-[0_12px_30px_rgba(19,41,61,0.06)] hover:border-[#67C7C6] transition">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#265D7A]">In Transit</span>
                        <div class="w-8 h-8 rounded-xl bg-amber-500/15 border border-amber-600/25 text-amber-800 flex items-center justify-center">
                            <i class="bi bi-water"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-extrabold text-amber-800 font-mono">{{ stats.in_transit }}</div>
                    <div class="text-[11px] text-[#265D7A] mt-1">Sedang dalam pelayaran</div>
                </div>

                <!-- 3. Delivered -->
                <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-2xl p-5 shadow-[0_12px_30px_rgba(19,41,61,0.06)] hover:border-[#67C7C6] transition">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#265D7A]">Delivered</span>
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/15 border border-emerald-600/25 text-emerald-800 flex items-center justify-center">
                            <i class="bi bi-check2-circle"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-extrabold text-emerald-800 font-mono">{{ stats.delivered }}</div>
                    <div class="text-[11px] text-[#265D7A] mt-1">Tuntas tiba di destinasi</div>
                </div>

                <!-- 4. Delayed Shipments -->
                <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-2xl p-5 shadow-[0_12px_30px_rgba(19,41,61,0.06)] hover:border-[#67C7C6] transition">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#265D7A]">Delayed</span>
                        <div class="w-8 h-8 rounded-xl bg-rose-500/15 border border-rose-600/25 text-rose-800 flex items-center justify-center">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-extrabold text-rose-800 font-mono">{{ stats.delayed }}</div>
                    <div class="text-[11px] text-[#265D7A] mt-1">
                        Delay Rate: <strong class="text-rose-800 font-mono">{{ stats.delay_rate }}%</strong>
                    </div>
                </div>

                <!-- 5. Open Exceptions -->
                <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-2xl p-5 shadow-[0_12px_30px_rgba(19,41,61,0.06)] hover:border-[#67C7C6] transition">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#265D7A]">Open Exceptions</span>
                        <div class="w-8 h-8 rounded-xl bg-rose-500/15 border border-rose-600/25 text-rose-800 flex items-center justify-center">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-extrabold text-rose-800 font-mono">{{ stats.open_exceptions }}</div>
                    <div class="text-[11px] text-[#265D7A] mt-1">
                        Incident Rate: <strong class="text-rose-800 font-mono">{{ stats.exception_rate }}%</strong>
                    </div>
                </div>

                <!-- 6. Active Carriers -->
                <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-2xl p-5 shadow-[0_12px_30px_rgba(19,41,61,0.06)] hover:border-[#67C7C6] transition">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#265D7A]">Active Carriers</span>
                        <div class="w-8 h-8 rounded-xl bg-[#EAF6F4] border border-[#265D7A]/20 text-[#265D7A] flex items-center justify-center">
                            <i class="bi bi-truck"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-extrabold text-[#13293D] font-mono">{{ stats.active_carriers }}</div>
                    <div class="text-[11px] text-[#265D7A] mt-1">Armada pelayaran aktif</div>
                </div>

                <!-- 7. Global Hubs -->
                <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-2xl p-5 shadow-[0_12px_30px_rgba(19,41,61,0.06)] hover:border-[#67C7C6] transition">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#265D7A]">Global Hubs</span>
                        <div class="w-8 h-8 rounded-xl bg-[#EAF6F4] border border-[#265D7A]/20 text-[#265D7A] flex items-center justify-center">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-extrabold text-[#13293D] font-mono">{{ stats.total_locations }}</div>
                    <div class="text-[11px] text-[#265D7A] mt-1">Pelabuhan &amp; checkpoint transit</div>
                </div>

                <!-- 8. Users Count -->
                <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-2xl p-5 shadow-[0_12px_30px_rgba(19,41,61,0.06)] hover:border-[#67C7C6] transition">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#265D7A]">Sistem Akun</span>
                        <div class="w-8 h-8 rounded-xl bg-[#EAF6F4] border border-[#265D7A]/20 text-[#265D7A] flex items-center justify-center">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                    <div class="text-2xl font-extrabold text-[#13293D] font-mono">{{ stats.total_users }}</div>
                    <div class="text-[11px] text-[#265D7A] mt-1">Admin, operator &amp; customer</div>
                </div>
            </div>

            <!-- Interactive Visual Charts (Chart.js) -->
            <DashboardCharts
                :status-distribution="stats.status_distribution"
                :carrier-distribution="stats.carrier_distribution"
            />

            <!-- Recent Shipments Overview Table -->
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)] mb-6">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#265D7A]/15">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-[#EAF6F4] border border-[#265D7A]/20 text-[#265D7A] flex items-center justify-center text-xs">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <h2 class="text-xs font-bold text-[#13293D] uppercase tracking-wider">Pengiriman Terkini (Recent Shipments)</h2>
                            <p class="text-[11px] font-normal text-[#265D7A]">Daftar aktivitas kargo terbaru dalam sistem</p>
                        </div>
                    </div>
                    <Link href="/shipments" class="text-xs font-bold text-[#265D7A] hover:text-[#13293D] inline-flex items-center gap-1 transition">
                        <span>Lihat Semua</span>
                        <i class="bi bi-arrow-right"></i>
                    </Link>
                </div>

                <div v-if="stats.recent_shipments?.length" class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-[#265D7A]/15 text-[#265D7A] uppercase font-mono text-[10px]">
                                <th class="py-2.5 px-3">No. Shipment</th>
                                <th class="py-2.5 px-3">Carrier</th>
                                <th class="py-2.5 px-3">Rute</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3">Kondisi ETA</th>
                                <th class="py-2.5 px-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#265D7A]/10">
                            <tr v-for="s in stats.recent_shipments" :key="s.id" class="hover:bg-white/60 transition">
                                <td class="py-3 px-3 font-mono font-bold text-[#265D7A]">
                                    <Link :href="`/shipments/${s.id}`" class="hover:underline text-[#13293D]">
                                        {{ s.shipment_number }}
                                    </Link>
                                </td>
                                <td class="py-3 px-3 text-[#13293D] font-semibold">
                                    {{ s.carrier?.name || '-' }}
                                </td>
                                <td class="py-3 px-3 font-mono text-[11px] text-[#265D7A] font-semibold">
                                    {{ s.origin_location?.code }} &rarr; {{ s.destination_location?.code }}
                                </td>
                                <td class="py-3 px-3">
                                    <span :class="['px-2.5 py-0.5 rounded-md border text-[10px] font-mono font-bold', statusBadgeClass(s.current_status)]">
                                        {{ s.current_status }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <span v-if="s.is_delayed" class="px-2 py-0.5 rounded-md bg-rose-500/15 text-rose-900 border border-rose-600/30 text-[10px] font-bold inline-flex items-center gap-1">
                                        <i class="bi bi-clock-history"></i>
                                        <span>DELAY (+{{ s.delay_duration_hours }}j)</span>
                                    </span>
                                    <span v-else class="px-2 py-0.5 rounded-md bg-emerald-500/15 text-emerald-900 border border-emerald-600/30 text-[10px] font-bold inline-flex items-center gap-1">
                                        <i class="bi bi-check-circle"></i>
                                        <span>ON-TIME</span>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right">
                                    <Link :href="`/shipments/${s.id}`" class="px-3 py-1 text-xs border border-[#265D7A]/20 bg-white/80 hover:bg-[#13293D] hover:text-[#A7E4DB] text-[#13293D] rounded-lg transition inline-flex items-center gap-1 font-semibold">
                                        <span>Detail</span>
                                        <i class="bi bi-chevron-right text-[10px]"></i>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="py-8 text-center text-xs text-[#265D7A]">
                    Belum ada data pengiriman terdaftar.
                </div>
            </div>

            <!-- Recent Audit Trail Feed (For Admin) -->
            <div v-if="isAdmin && stats.recent_audits?.length" class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)]">
                <h2 class="text-xs font-bold text-[#13293D] uppercase tracking-wider mb-4 flex items-center gap-2">
                    <i class="bi bi-shield-check text-emerald-700"></i>
                    <span>Aktivitas Audit Trail Terkini</span>
                </h2>

                <div class="divide-y divide-[#265D7A]/10 text-xs">
                    <div v-for="log in stats.recent_audits" :key="log.id" class="py-2.5 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-[#265D7A] text-[11px]">{{ new Date(log.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                            <div>
                                <span class="font-bold text-[#13293D]">{{ log.user?.name || 'System' }}</span>
                                <span class="text-[#265D7A]/40 mx-1.5">&bull;</span>
                                <span class="font-mono text-[11px] px-2 py-0.5 rounded bg-[#13293D] text-[#A7E4DB] font-bold">{{ log.action }}</span>
                            </div>
                        </div>
                        <span class="text-[#265D7A] font-mono text-[11px]">{{ log.auditable_type?.split('\\').pop() }} #{{ log.auditable_id }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Electronic Bill of Lading (e-B/L) Modal -->
        <BillOfLadingModal
            :show="showBolModal"
            :shipment="selectedShipment"
            @close="showBolModal = false"
        />
    </AuthenticatedLayout>
</template>
