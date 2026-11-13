<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import VehicleGraphic from '@/Components/VehicleGraphic.vue';
import BillOfLadingModal from '@/Components/BillOfLadingModal.vue';
import { estimateCarbonFootprint } from '@/Utils/carbonCalculator';

const props = defineProps({
    shipments: {
        type: Object,
        required: true,
    },
    carriers: {
        type: Array,
        default: () => [],
    },
    locations: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const isAdmin = computed(() => user.value.role === 'admin');
const isOperator = computed(() => user.value.role === 'operator');
const isCustomer = computed(() => user.value.role === 'customer');

// View mode: default 'grid' for customer visual cards, 'table' for admin/operator
const viewMode = ref(isCustomer.value ? 'grid' : 'table');

const showAdvancedFilters = ref(false);
const showBolModal = ref(false);
const bolShipment = ref(null);
const copiedShipmentId = ref(null);

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const carrierId = ref(props.filters.carrier_id || '');
const originId = ref(props.filters.origin_id || '');
const destinationId = ref(props.filters.destination_id || '');
const delay = ref(props.filters.delay || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const sortBy = ref(props.filters.sort_by || 'id');
const sortDirection = ref(props.filters.sort_direction || 'desc');
const isLoading = ref(false);

const filter = () => {
    isLoading.value = true;
    router.get('/shipments', {
        search: search.value || undefined,
        status: status.value || undefined,
        carrier_id: carrierId.value || undefined,
        origin_id: originId.value || undefined,
        destination_id: destinationId.value || undefined,
        delay: delay.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        sort_by: sortBy.value || undefined,
        sort_direction: sortDirection.value || undefined,
    }, {
        preserveState: true,
        replace: true,
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const setQuickStatus = (st) => {
    if (st === 'DELAYED') {
        status.value = '';
        delay.value = 'delayed';
    } else {
        delay.value = '';
        status.value = st;
    }
    filter();
};

const resetFilter = () => {
    isLoading.value = true;
    search.value = '';
    status.value = '';
    carrierId.value = '';
    originId.value = '';
    destinationId.value = '';
    delay.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    sortBy.value = 'id';
    sortDirection.value = 'desc';
    router.get('/shipments', {}, {
        preserveState: true,
        replace: true,
        onFinish: () => {
            isLoading.value = false;
        },
    });
};

const toggleSort = (col) => {
    if (sortBy.value === col) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = col;
        sortDirection.value = 'desc';
    }
    filter();
};

const copyPublicTrackingLink = (shipment) => {
    if (!shipment?.shipment_number) return;
    const url = `${window.location.origin}/?track=${encodeURIComponent(shipment.shipment_number)}`;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            copiedShipmentId.value = shipment.id;
            setTimeout(() => {
                copiedShipmentId.value = null;
            }, 2500);
        });
    }
};

const openBol = (shipment) => {
    bolShipment.value = shipment;
    showBolModal.value = true;
};

const shipmentToDelete = ref(null);
const deleting = ref(false);

const deleteShipment = (shipment) => {
    shipmentToDelete.value = shipment;
};

const executeDeleteShipment = () => {
    if (!shipmentToDelete.value) return;
    deleting.value = true;
    router.delete(`/shipments/${shipmentToDelete.value.id}`, {
        onFinish: () => {
            deleting.value = false;
            shipmentToDelete.value = null;
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
        <Head :title="isCustomer ? 'Kargo Saya (My Shipments)' : 'Daftar Pengiriman Global'" />

        <!-- Top Header Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 mb-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-[#13293D]">
                        {{ isCustomer ? 'Kargo Saya (My Shipments)' : 'Daftar Pengiriman Global (Shipments)' }}
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-[#13293D] text-[#A7E4DB] text-xs font-mono font-bold">
                        {{ shipments.total || 0 }} Kargo
                    </span>
                </div>
                <p class="text-xs text-[#265D7A]">
                    {{ isCustomer ? 'Pantau pergerakan kontainer armada, salin link pelacakan, dan cetak dokumen resmi muatan Anda.' : 'Pantau pergerakan kontainer kargo laut, udara, dan darat secara real-time.' }}
                </p>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-auto">
                <!-- Dual-View Toggle (Cards vs Table) -->
                <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200 text-xs font-semibold">
                    <button
                        type="button"
                        @click="viewMode = 'grid'"
                        :class="[
                            'px-3 py-1.5 rounded-lg transition inline-flex items-center gap-1.5 cursor-pointer',
                            viewMode === 'grid' ? 'bg-white text-[#13293D] shadow-2xs font-bold' : 'text-slate-500 hover:text-[#13293D]'
                        ]"
                        title="Tampilan Kartu Visual"
                    >
                        <i class="bi bi-grid-fill"></i>
                        <span class="hidden sm:inline">Kartu</span>
                    </button>
                    <button
                        type="button"
                        @click="viewMode = 'table'"
                        :class="[
                            'px-3 py-1.5 rounded-lg transition inline-flex items-center gap-1.5 cursor-pointer',
                            viewMode === 'table' ? 'bg-white text-[#13293D] shadow-2xs font-bold' : 'text-slate-500 hover:text-[#13293D]'
                        ]"
                        title="Tampilan Tabel Komprehensif"
                    >
                        <i class="bi bi-table"></i>
                        <span class="hidden sm:inline">Tabel</span>
                    </button>
                </div>

                <Link
                    v-if="isAdmin || isOperator"
                    href="/shipments/create"
                    class="px-4 py-2 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-xl transition inline-flex items-center gap-2 shadow-xs"
                >
                    <i class="bi bi-plus-lg"></i>
                    <span>Buat Pengiriman</span>
                </Link>
            </div>
        </div>

        <!-- Multi-Parameter Search & Filter Panel (White De-noised Surface) -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 mb-5 shadow-xs space-y-3">
            <!-- Tier 1: Search & Quick Status Pills -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                <!-- Quick Filter Tabs (TransGlobal style) -->
                <div class="flex flex-wrap items-center gap-1 text-xs font-semibold">
                    <button
                        type="button"
                        @click="setQuickStatus('')"
                        :class="[
                            'px-3 py-1.5 rounded-xl border transition cursor-pointer',
                            !status && delay !== 'delayed' ? 'bg-[#13293D] text-[#A7E4DB] border-[#13293D] shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'
                        ]"
                    >
                        Semua
                    </button>
                    <button
                        type="button"
                        @click="setQuickStatus('IN_TRANSIT')"
                        :class="[
                            'px-3 py-1.5 rounded-xl border transition cursor-pointer',
                            status === 'IN_TRANSIT' ? 'bg-[#13293D] text-[#A7E4DB] border-[#13293D] shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'
                        ]"
                    >
                        Dalam Pelayaran
                    </button>
                    <button
                        type="button"
                        @click="setQuickStatus('DELIVERED')"
                        :class="[
                            'px-3 py-1.5 rounded-xl border transition cursor-pointer',
                            status === 'DELIVERED' ? 'bg-[#13293D] text-[#A7E4DB] border-[#13293D] shadow-2xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100'
                        ]"
                    >
                        Tuntas Terkirim
                    </button>
                    <button
                        type="button"
                        @click="setQuickStatus('DELAYED')"
                        :class="[
                            'px-3 py-1.5 rounded-xl border transition cursor-pointer',
                            delay === 'delayed' ? 'bg-rose-700 text-white border-rose-700 shadow-2xs' : 'bg-slate-50 border-slate-200 text-rose-700 hover:bg-rose-50'
                        ]"
                    >
                        Terlambat (Delayed)
                    </button>
                </div>

                <!-- Search Input & Advanced Toggle -->
                <div class="flex items-center gap-2">
                    <div class="relative w-full sm:w-64">
                        <i class="bi bi-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                        <input
                            v-model="search"
                            @keyup.enter="filter"
                            type="text"
                            placeholder="Cari No. Resi, Ref, atau Kargo..."
                            class="w-full h-9 pl-8 pr-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-[#13293D] placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-[#67C7C6] focus:border-[#265D7A]"
                        />
                    </div>

                    <button
                        type="button"
                        @click="showAdvancedFilters = !showAdvancedFilters"
                        :class="[
                            'h-9 px-3 border rounded-xl text-xs font-semibold transition inline-flex items-center gap-1.5 cursor-pointer shrink-0',
                            showAdvancedFilters ? 'bg-[#13293D] text-[#A7E4DB] border-[#13293D]' : 'bg-slate-50 hover:bg-slate-100 border-slate-200 text-[#13293D]'
                        ]"
                        title="Filter Lebih Lengkap"
                    >
                        <i class="bi bi-sliders"></i>
                        <span class="hidden sm:inline">Filter</span>
                    </button>

                    <button
                        type="button"
                        @click="resetFilter"
                        title="Reset Semua Filter"
                        aria-label="Reset Filter"
                        class="h-9 px-3 border border-slate-200 bg-slate-50 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-300 rounded-xl text-xs text-slate-600 transition cursor-pointer shrink-0"
                    >
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>

            <!-- Tier 2: Advanced Collapsible Filters -->
            <div v-show="showAdvancedFilters" class="pt-3 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div>
                    <label class="block font-bold mb-1 text-[#13293D]">Carrier / Pelayaran</label>
                    <select v-model="carrierId" @change="filter" class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-[#13293D]">
                        <option value="">Semua Carrier</option>
                        <option v-for="c in carriers" :key="c.id" :value="c.id">{{ c.name }} ({{ c.transport_mode }})</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold mb-1 text-[#13293D]">Pelabuhan Asal</label>
                    <select v-model="originId" @change="filter" class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-[#13293D]">
                        <option value="">Semua Lokasi Asal</option>
                        <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.code }} &bull; {{ l.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold mb-1 text-[#13293D]">Pelabuhan Tujuan</label>
                    <select v-model="destinationId" @change="filter" class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-[#13293D]">
                        <option value="">Semua Lokasi Tujuan</option>
                        <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.code }} &bull; {{ l.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold mb-1 text-[#13293D]">Keberangkatan Sejak</label>
                    <input v-model="dateFrom" @change="filter" type="date" class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-[#13293D]" />
                </div>
            </div>
        </div>

        <!-- 1. CARD GRID VIEW (Yellow Express & TransGlobal Style) -->
        <div v-if="viewMode === 'grid'">
            <div v-if="shipments.data?.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div
                    v-for="s in shipments.data"
                    :key="s.id"
                    class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs hover:border-[#67C7C6] transition flex flex-col justify-between"
                >
                    <!-- Card Header -->
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="px-2 py-0.5 rounded-full bg-[#13293D] text-[#A7E4DB] text-[10px] font-mono font-bold uppercase tracking-wider">
                                {{ s.carrier?.transport_mode || 'SEA' }} FREIGHT
                            </span>
                            <span :class="['px-2 py-0.5 rounded border text-[10px] font-mono font-bold', statusBadgeClass(s.current_status)]">
                                {{ s.current_status }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-2 mb-1">
                            <Link :href="`/shipments/${s.id}`" class="text-base font-black font-mono text-[#13293D] hover:text-[#265D7A] transition">
                                {{ s.shipment_number }}
                            </Link>
                            <span v-if="s.is_delayed" class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-mono font-bold">
                                +{{ s.delay_duration_hours }}j Delay
                            </span>
                            <span v-else class="text-[10px] text-emerald-700 font-bold font-mono">
                                On-Time
                            </span>
                        </div>

                        <div class="text-[11px] text-[#265D7A] font-medium mb-3">
                            {{ s.cargo_type || 'General Cargo' }} &bull; {{ s.carrier?.name || 'Carrier' }}
                        </div>

                        <!-- 3D Vehicle Render Showcase -->
                        <div class="bg-gradient-to-b from-slate-50 to-white rounded-xl p-3 border border-slate-100 flex items-center justify-center my-3 relative overflow-hidden">
                            <VehicleGraphic
                                :mode="s.carrier?.transport_mode"
                                class-name="h-28 w-auto max-w-full drop-shadow-md"
                            />
                        </div>

                        <!-- Route Stepper -->
                        <div class="bg-slate-50 rounded-xl p-3 border border-slate-100 mb-3 space-y-1.5 text-xs font-mono">
                            <div class="flex items-center justify-between">
                                <div class="text-left">
                                    <span class="text-[9px] uppercase text-slate-400 block font-bold">Asal</span>
                                    <strong class="text-[#13293D]">{{ s.origin_location?.code }}</strong>
                                    <span class="text-[10px] text-slate-500 block truncate max-w-[100px]">{{ s.origin_location?.city }}</span>
                                </div>
                                <div class="flex-1 px-3 text-center">
                                    <i class="bi bi-arrow-right text-[#265D7A]"></i>
                                    <div class="text-[9px] text-slate-400 font-sans">
                                        {{ estimateCarbonFootprint(s).distanceKm }} km
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[9px] uppercase text-slate-400 block font-bold">Tujuan</span>
                                    <strong class="text-emerald-700">{{ s.destination_location?.code }}</strong>
                                    <span class="text-[10px] text-slate-500 block truncate max-w-[100px]">{{ s.destination_location?.city }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Telemetry Badges (Carbon & Payload) -->
                        <div class="grid grid-cols-2 gap-2 text-[11px] font-mono mb-4">
                            <div class="p-2 bg-slate-50 rounded-lg border border-slate-100">
                                <span class="text-[9px] text-slate-400 uppercase block">Muatan</span>
                                <strong class="text-[#13293D]">{{ s.total_weight_kg ? `${s.total_weight_kg} kg` : 'Container' }}</strong>
                            </div>
                            <div class="p-2 bg-emerald-50/70 rounded-lg border border-emerald-100">
                                <span class="text-[9px] text-emerald-800 uppercase block font-sans font-semibold">Emisi CO₂</span>
                                <strong class="text-emerald-900">{{ estimateCarbonFootprint(s).emissionsKg }} kg</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Card Action Footer -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                        <button
                            type="button"
                            @click="copyPublicTrackingLink(s)"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 hover:border-[#67C7C6] bg-slate-50 text-[11px] font-semibold text-[#13293D] inline-flex items-center gap-1 transition cursor-pointer"
                            :title="'Salin Link Lacak'"
                        >
                            <i :class="['bi', copiedShipmentId === s.id ? 'bi-check2 text-emerald-600 font-bold' : 'bi-share text-[#265D7A]']"></i>
                            <span>{{ copiedShipmentId === s.id ? 'Tersalin' : 'Share' }}</span>
                        </button>

                        <button
                            type="button"
                            @click="openBol(s)"
                            class="px-2.5 py-1.5 rounded-lg border border-slate-200 hover:border-[#67C7C6] bg-slate-50 text-[11px] font-semibold text-[#13293D] inline-flex items-center gap-1 transition cursor-pointer"
                            title="Lihat e-Bill of Lading"
                        >
                            <i class="bi bi-file-earmark-text text-[#265D7A]"></i>
                            <span>e-B/L</span>
                        </button>

                        <Link
                            :href="`/shipments/${s.id}`"
                            class="px-3.5 py-1.5 rounded-lg bg-[#265D7A] hover:bg-[#13293D] text-white text-[11px] font-bold inline-flex items-center gap-1 transition shadow-xs cursor-pointer ml-auto"
                        >
                            <span>Lacak</span>
                            <i class="bi bi-arrow-right"></i>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State for Grid -->
            <div v-else class="bg-white border border-slate-200/80 rounded-2xl p-8 shadow-xs">
                <EmptyState
                    icon="bi-box-seam"
                    title="Tidak Ada Kargo Ditemukan"
                    description="Sesuaikan kata kunci pencarian atau reset filter untuk menampilkan kargo lainnya."
                >
                    <template #action>
                        <button
                            type="button"
                            @click="resetFilter"
                            class="px-4 py-1.5 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-full transition cursor-pointer shadow-xs"
                        >
                            Reset Filter
                        </button>
                    </template>
                </EmptyState>
            </div>
        </div>

        <!-- 2. TABLE VIEW (Executive High-Density) -->
        <div v-else class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-[#265D7A] font-mono font-bold uppercase tracking-wider text-[10px]">
                            <th @click="toggleSort('shipment_number')" class="py-3 px-4 cursor-pointer hover:text-[#13293D] transition">
                                <span class="inline-flex items-center gap-1">
                                    No. Shipment
                                    <i v-if="sortBy === 'shipment_number'" :class="sortDirection === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                </span>
                            </th>
                            <th class="py-3 px-4">Carrier &amp; Moda</th>
                            <th class="py-3 px-4">Rute (Origin &rarr; Dest)</th>
                            <th v-if="!isCustomer" class="py-3 px-4">Customer</th>
                            <th @click="toggleSort('current_status')" class="py-3 px-4 cursor-pointer hover:text-[#13293D] transition">
                                <span class="inline-flex items-center gap-1">
                                    Status
                                    <i v-if="sortBy === 'current_status'" :class="sortDirection === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                </span>
                            </th>
                            <th @click="toggleSort('expected_departure')" class="py-3 px-4 cursor-pointer hover:text-[#13293D] transition">
                                <span class="inline-flex items-center gap-1">
                                    Keberangkatan
                                    <i v-if="sortBy === 'expected_departure'" :class="sortDirection === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                </span>
                            </th>
                            <th @click="toggleSort('expected_arrival')" class="py-3 px-4 cursor-pointer hover:text-[#13293D] transition">
                                <span class="inline-flex items-center gap-1">
                                    Estimasi Tiba (ETA)
                                    <i v-if="sortBy === 'expected_arrival'" :class="sortDirection === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                </span>
                            </th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <!-- Skeleton Loading State -->
                    <tbody v-if="isLoading" class="divide-y divide-slate-100 animate-pulse" aria-busy="true">
                        <tr v-for="i in 5" :key="`skel-${i}`" class="hover:bg-slate-50">
                            <td class="py-3.5 px-4"><div class="h-4 w-28 bg-slate-200 rounded"></div></td>
                            <td class="py-3.5 px-4"><div class="h-4 w-24 bg-slate-200 rounded"></div></td>
                            <td class="py-3.5 px-4"><div class="h-4 w-32 bg-slate-200 rounded"></div></td>
                            <td v-if="!isCustomer" class="py-3.5 px-4"><div class="h-4 w-24 bg-slate-200 rounded"></div></td>
                            <td class="py-3.5 px-4"><div class="h-5 w-20 bg-slate-200 rounded-md"></div></td>
                            <td class="py-3.5 px-4"><div class="h-4 w-20 bg-slate-200 rounded"></div></td>
                            <td class="py-3.5 px-4"><div class="h-4 w-24 bg-slate-200 rounded"></div></td>
                            <td class="py-3.5 px-4 text-right"><div class="h-6 w-14 bg-slate-200 rounded-lg ml-auto"></div></td>
                        </tr>
                    </tbody>

                    <!-- Loaded Shipments Data -->
                    <tbody v-else class="divide-y divide-slate-100">
                        <tr v-for="s in shipments.data" :key="s.id" class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-mono font-bold">
                                <Link :href="`/shipments/${s.id}`" class="hover:underline text-[#13293D]">
                                    {{ s.shipment_number }}
                                </Link>
                                <div v-if="s.reference_number" class="text-[10px] text-slate-500 font-mono font-normal">
                                    Ref: {{ s.reference_number }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-[#13293D]">{{ s.carrier?.name || '-' }}</div>
                                <span class="text-[10px] font-mono text-[#265D7A] uppercase">{{ s.carrier?.transport_mode }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-1.5 font-mono text-[11px] font-bold text-[#13293D]">
                                    <span>{{ s.origin_location?.code }}</span>
                                    <i class="bi bi-arrow-right text-[#265D7A]"></i>
                                    <span>{{ s.destination_location?.code }}</span>
                                </div>
                                <div class="text-[10px] text-slate-500">
                                    {{ s.origin_location?.city }} &rarr; {{ s.destination_location?.city }}
                                </div>
                            </td>
                            <td v-if="!isCustomer" class="py-3 px-4">
                                <div class="font-semibold text-[#13293D]">{{ s.customer?.name }}</div>
                                <div class="text-[10px] text-slate-500">{{ s.customer?.company_name || 'Individual' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span :class="['px-2.5 py-0.5 rounded-md border font-mono font-bold text-[10px]', statusBadgeClass(s.current_status)]">
                                    {{ s.current_status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono text-[11px] text-[#265D7A]">
                                {{ new Date(s.expected_departure).toLocaleDateString() }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-mono text-[11px] text-[#13293D] font-semibold">
                                    {{ new Date(s.actual_arrival || s.estimated_arrival || s.expected_arrival).toLocaleDateString() }}
                                </div>
                                <span v-if="s.is_delayed" class="px-1.5 py-0.2 rounded bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold inline-flex items-center gap-0.5 mt-0.5">
                                    <i class="bi bi-clock-history"></i>
                                    <span>DELAY (+{{ s.delay_duration_hours }}j)</span>
                                </span>
                                <span v-else class="text-[10px] text-emerald-700 font-bold inline-flex items-center gap-0.5 mt-0.5">
                                    <i class="bi bi-check-circle"></i>
                                    <span>ON-TIME</span>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        type="button"
                                        @click="openBol(s)"
                                        class="px-2 py-1 text-xs border border-slate-200 bg-white hover:bg-slate-100 text-[#13293D] rounded-lg transition inline-flex items-center gap-1 font-semibold cursor-pointer"
                                        title="Lihat e-B/L"
                                    >
                                        <i class="bi bi-file-earmark-text text-[#265D7A]"></i>
                                    </button>
                                    <Link :href="`/shipments/${s.id}`" class="px-2.5 py-1 text-xs border border-slate-200 bg-white hover:bg-[#13293D] hover:text-[#A7E4DB] text-[#13293D] rounded-lg transition inline-flex items-center gap-1 font-semibold">
                                        <i class="bi bi-eye"></i>
                                        <span>Detail</span>
                                    </Link>
                                    <Link v-if="isAdmin || isOperator" :href="`/shipments/${s.id}/edit`" class="px-2.5 py-1 text-xs border border-slate-200 bg-white hover:bg-[#13293D] hover:text-[#A7E4DB] text-[#13293D] rounded-lg transition font-semibold">
                                        Edit
                                    </Link>
                                    <button v-if="isAdmin" @click="deleteShipment(s)" aria-label="Hapus Shipment" class="px-2 py-1 text-xs text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition cursor-pointer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!shipments.data?.length">
                            <td :colspan="isCustomer ? 7 : 8" class="p-0">
                                <EmptyState
                                    icon="bi-box-seam"
                                    title="Tidak Ada Kargo Ditemukan"
                                    description="Ubah kata kunci pencarian atau sesuaikan filter status, carrier, dan tanggal."
                                >
                                    <template #action>
                                        <button
                                            type="button"
                                            @click="resetFilter"
                                            class="px-4 py-1.5 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-full transition cursor-pointer shadow-xs"
                                        >
                                            Reset Filter
                                        </button>
                                    </template>
                                </EmptyState>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination Bar -->
        <div v-if="shipments.links?.length > 3" class="mt-4 bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[#265D7A]">
            <div>
                Menampilkan <strong class="text-[#13293D]">{{ shipments.from || 0 }}</strong> - <strong class="text-[#13293D]">{{ shipments.to || 0 }}</strong> dari <strong class="text-[#13293D]">{{ shipments.total }}</strong> kargo
            </div>
            <div class="flex gap-1 flex-wrap">
                <template v-for="(link, i) in shipments.links" :key="i">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        :class="['px-3 py-1 rounded-lg border font-mono text-xs transition', link.active ? 'bg-[#13293D] text-[#A7E4DB] font-bold border-[#13293D] shadow-2xs' : 'border-slate-200 bg-white hover:bg-slate-50 text-[#13293D]']"
                        v-html="link.label"
                        preserve-state
                    />
                    <span v-else class="px-3 py-1 rounded-lg border border-transparent text-slate-400 font-mono text-xs" v-html="link.label" />
                </template>
            </div>
        </div>

        <!-- Confirm Delete Shipment Modal -->
        <ConfirmDialog
            :show="!!shipmentToDelete"
            title="Hapus Data Pengiriman?"
            message="Manifest kargo beserta seluruh riwayat tracking event dan log auditnya akan dihapus secara permanen."
            :item-name="shipmentToDelete?.shipment_number || ''"
            confirm-text="Ya, Hapus Shipment"
            :loading="deleting"
            @close="shipmentToDelete = null"
            @confirm="executeDeleteShipment"
        />

        <!-- e-Bill of Lading Document Modal -->
        <BillOfLadingModal
            :show="showBolModal"
            :shipment="bolShipment"
            @close="showBolModal = false"
        />
    </AuthenticatedLayout>
</template>
