<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    locations: {
        type: Array,
        default: () => [],
    },
    sampleShipments: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({
            total_shipments: 8,
            active_carriers: 7,
            total_locations: 8,
        }),
    },
});

const activeTab = ref('track'); // 'track' | 'tariff' | 'hubs'

// Animated Stat Counters
const displayShipments = ref(0);
const displayCarriers = ref(0);
const displayLocations = ref(0);

const animateCounter = (targetRef, endValue, duration = 800) => {
    const start = 0;
    const startTime = performance.now();
    const step = (now) => {
        const elapsed = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        targetRef.value = Math.round(start + (endValue - start) * eased);
        if (progress < 1) {
            requestAnimationFrame(step);
        }
    };
    requestAnimationFrame(step);
};

onMounted(() => {
    animateCounter(displayShipments, props.stats?.total_shipments ?? 8, 800);
    animateCounter(displayCarriers, props.stats?.active_carriers ?? 7, 800);
    animateCounter(displayLocations, props.stats?.total_locations ?? 8, 800);
});

// Tab 1: Public Tracking
const trackingInput = ref(props.sampleShipments[0] || '');
const trackingLoading = ref(false);
const trackingError = ref('');
const trackingResult = ref(null);

const runPublicTrack = async (customCode = null) => {
    const code = (customCode ?? trackingInput.value).trim();
    if (!code) {
        trackingError.value = 'Silakan masukkan nomor resi atau PO referensi.';
        return;
    }

    if (customCode) {
        trackingInput.value = customCode;
    }

    trackingLoading.value = true;
    trackingError.value = '';
    trackingResult.value = null;

    try {
        const res = await fetch(`/track-public?tracking_number=${encodeURIComponent(code)}`, {
            headers: { Accept: 'application/json' },
        });
        const data = await res.json();

        if (!res.ok || !data.found) {
            trackingError.value = data.message || 'Nomor resi tidak ditemukan dalam sistem Delixie.';
        } else {
            trackingResult.value = data.shipment;
        }
    } catch {
        trackingError.value = 'Gagal menghubungi server pelacakan. Silakan coba lagi.';
    } finally {
        trackingLoading.value = false;
    }
};

const lifecycleSteps = ['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED'];
const isStepReached = (stepName, currentStatus) => {
    if (currentStatus === 'CANCELLED') return false;
    const currentIndex = lifecycleSteps.indexOf(currentStatus);
    const stepIndex = lifecycleSteps.indexOf(stepName);
    return stepIndex !== -1 && currentIndex >= stepIndex;
};

// Tab 2: Tariff & ETA Estimator
const calcOriginId = ref(props.locations[0]?.id || '');
const calcDestId = ref(props.locations[1]?.id || '');
const calcMode = ref('SEA');
const calcWeight = ref(250);
const calcLoading = ref(false);
const calcError = ref('');
const calcResult = ref(null);

const runTariffEstimate = async () => {
    if (!calcOriginId.value || !calcDestId.value) {
        calcError.value = 'Pilih lokasi asal dan tujuan terlebih dahulu.';
        return;
    }
    if (calcOriginId.value === calcDestId.value) {
        calcError.value = 'Lokasi asal dan tujuan tidak boleh sama.';
        return;
    }

    calcLoading.value = true;
    calcError.value = '';
    calcResult.value = null;

    try {
        const params = new URLSearchParams({
            origin_id: calcOriginId.value,
            destination_id: calcDestId.value,
            transport_mode: calcMode.value,
            weight_kg: calcWeight.value,
        });

        const res = await fetch(`/estimate-tariff?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        });
        const payload = await res.json();

        if (!res.ok || !payload.success) {
            calcError.value = payload.message || 'Gagal menghitung estimasi tarif pengiriman.';
        } else {
            calcResult.value = payload.data;
        }
    } catch {
        calcError.value = 'Terjadi gangguan saat menghitung estimasi biaya.';
    } finally {
        calcLoading.value = false;
    }
};

// Tab 3: Hub Search
const hubQuery = ref('');
const filteredHubs = computed(() => {
    const q = hubQuery.value.trim().toLowerCase();
    if (!q) return props.locations;
    return props.locations.filter(l =>
        l.code.toLowerCase().includes(q) ||
        l.name.toLowerCase().includes(q) ||
        l.city.toLowerCase().includes(q) ||
        l.country.toLowerCase().includes(q)
    );
});

const formatIdr = (num) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(num || 0);
};

defineExpose({
    setActiveTab: (tabName) => {
        activeTab.value = tabName;
    },
    triggerTrack: (code) => {
        activeTab.value = 'track';
        runPublicTrack(code);
    },
});
</script>

<template>
    <!-- SECTION 2: CENTERED TRACKING COMMAND BAR & KEY STATS STRIP -->
    <section id="utility-hub" aria-label="Tracking Command Bar" class="relative z-30 max-w-6xl mx-auto px-6 py-20 sm:py-24">
        <!-- Main Tabbed Glass Command Bar -->
        <div class="rounded-[32px] bg-[#02332A]/80 backdrop-blur-2xl border border-[#FAEFDB]/18 p-6 sm:p-10 shadow-[0_24px_60px_rgba(0,0,0,0.45)] relative overflow-hidden transition-all duration-300 text-[#FAEFDB]">
            <!-- Ambient Glow inside card -->
            <div class="absolute -top-24 -right-24 w-80 h-80 rounded-full bg-[#BE2388]/15 blur-3xl pointer-events-none"></div>

            <!-- 3-Tab Selector Bar -->
            <div class="flex flex-wrap gap-2 p-1.5 bg-[#001F18]/85 rounded-2xl mb-8 border border-[#FAEFDB]/15 relative z-10">
                <button
                    type="button"
                    @click="activeTab = 'track'"
                    :class="[
                        'flex-1 min-w-[120px] py-3 px-4 rounded-xl text-xs sm:text-sm font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer',
                        activeTab === 'track'
                            ? 'bg-gradient-to-r from-[#BE2388] to-[#D42B8A] text-white shadow-md shadow-[#BE2388]/30'
                            : 'text-[#FAEFDB]/70 hover:text-white hover:bg-white/5'
                    ]"
                >
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <span>Cek Resi Instan</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'tariff'"
                    :class="[
                        'flex-1 min-w-[120px] py-3 px-4 rounded-xl text-xs sm:text-sm font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer',
                        activeTab === 'tariff'
                            ? 'bg-gradient-to-r from-[#BE2388] to-[#D42B8A] text-white shadow-md shadow-[#BE2388]/30'
                            : 'text-[#FAEFDB]/70 hover:text-white hover:bg-white/5'
                    ]"
                >
                    <i class="bi bi-calculator" aria-hidden="true"></i>
                    <span>Cek Tarif &amp; ETA</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'hubs'"
                    :class="[
                        'flex-1 min-w-[120px] py-3 px-4 rounded-xl text-xs sm:text-sm font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer',
                        activeTab === 'hubs'
                            ? 'bg-gradient-to-r from-[#BE2388] to-[#D42B8A] text-white shadow-md shadow-[#BE2388]/30'
                            : 'text-[#FAEFDB]/70 hover:text-white hover:bg-white/5'
                    ]"
                >
                    <i class="bi bi-geo-alt" aria-hidden="true"></i>
                    <span>Jaringan Hub ({{ locations.length }})</span>
                </button>
            </div>

            <!-- TAB 1: CEK RESI INSTAN -->
            <div v-if="activeTab === 'track'" class="space-y-5 relative z-10">
                <div>
                    <label for="tracking-main-input" class="block text-xs font-bold text-[#FAEFDB] mb-2">
                        Nomor Resi / Referensi Pengiriman (Pencarian Publik Tanpa Login)
                    </label>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <i class="bi bi-upc-scan absolute left-4 top-3.5 text-[#FAEFDB]/60" aria-hidden="true"></i>
                            <input
                                id="tracking-main-input"
                                v-model="trackingInput"
                                type="text"
                                placeholder="Contoh: TRK-2026-0001 atau PO-2026-SEA-001"
                                class="w-full h-12 pl-11 pr-4 bg-[#01241D] border border-[#FAEFDB]/25 rounded-2xl text-sm text-[#FAEFDB] placeholder-[#FAEFDB]/45 focus:outline-none focus:ring-2 focus:ring-[#BE2388]/50 focus:border-transparent font-mono"
                                @keyup.enter="runPublicTrack()"
                            />
                        </div>
                        <button
                            type="button"
                            @click="runPublicTrack()"
                            :disabled="trackingLoading"
                            class="h-12 px-8 rounded-2xl bg-gradient-to-r from-[#BE2388] to-[#D42B8A] hover:brightness-110 text-white font-bold text-sm transition-all duration-300 shadow-md shadow-[#BE2388]/30 hover:-translate-y-0.5 flex items-center justify-center gap-2 disabled:opacity-60 cursor-pointer shrink-0"
                        >
                            <span v-if="trackingLoading" class="inline-block w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                            <span v-else class="flex items-center gap-2">
                                <i class="bi bi-search text-xs" aria-hidden="true"></i>
                                <span>Lacak Pengiriman</span>
                            </span>
                        </button>
                    </div>

                    <!-- Sample Quick Pills -->
                    <div v-if="sampleShipments.length" class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-[#FAEFDB]/60 text-[11px] font-mono">Resi Sampel:</span>
                        <button
                            v-for="code in sampleShipments"
                            :key="code"
                            type="button"
                            @click="runPublicTrack(code)"
                            class="px-2.5 py-1 rounded-lg bg-[#001F18]/80 hover:bg-[#BE2388]/30 text-[#FAEFDB]/80 hover:text-white border border-[#FAEFDB]/15 text-[11px] font-mono transition cursor-pointer"
                        >
                            {{ code }}
                        </button>
                    </div>

                    <!-- Error Alert -->
                    <div v-if="trackingError" class="mt-4 p-4 rounded-2xl bg-rose-950/60 border border-rose-500/30 text-rose-200 text-xs flex items-center gap-2.5">
                        <i class="bi bi-exclamation-triangle-fill text-rose-400 text-base" aria-hidden="true"></i>
                        <span>{{ trackingError }}</span>
                    </div>
                </div>

                <!-- Tracking Result Drawer -->
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                >
                    <div v-if="trackingResult" class="mt-6 pt-6 border-t border-[#FAEFDB]/15 space-y-4">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-4 rounded-2xl bg-[#001F18]/80 border border-[#FAEFDB]/15">
                            <div>
                                <span class="text-[10px] uppercase font-mono tracking-wider text-[#FAEFDB]/60 font-semibold block">HASIL PELACAKAN RESI</span>
                                <div class="text-base font-extrabold font-mono text-[#FAEFDB]">
                                    {{ trackingResult.tracking_number }}
                                </div>
                                <div class="text-xs text-[#FAEFDB]/70 mt-0.5">
                                    {{ trackingResult.origin?.city }} &rarr; {{ trackingResult.destination?.city }}
                                </div>
                            </div>
                            <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-[#BE2388] text-white">
                                {{ trackingResult.status }}
                            </span>
                        </div>

                        <!-- Progress Step Flow -->
                        <div class="p-4 rounded-2xl bg-[#001F18]/60 border border-[#FAEFDB]/10">
                            <div class="flex items-center justify-between text-xs font-mono font-bold">
                                <div
                                    v-for="step in lifecycleSteps"
                                    :key="step"
                                    class="flex flex-col items-center gap-1.5 flex-1 text-center"
                                >
                                    <div
                                        :class="[
                                            'w-7 h-7 rounded-full flex items-center justify-center text-xs transition-colors',
                                            isStepReached(step, trackingResult.status)
                                                ? 'bg-[#BE2388] text-white ring-4 ring-[#BE2388]/20'
                                                : 'bg-[#01241D] text-[#FAEFDB]/40 border border-[#FAEFDB]/15'
                                        ]"
                                    >
                                        <i v-if="isStepReached(step, trackingResult.status)" class="bi bi-check-lg" aria-hidden="true"></i>
                                        <span v-else class="w-1.5 h-1.5 rounded-full bg-[#FAEFDB]/30"></span>
                                    </div>
                                    <span
                                        :class="[
                                            'text-[10px] hidden sm:block',
                                            isStepReached(step, trackingResult.status) ? 'text-[#FAEFDB]' : 'text-[#FAEFDB]/40'
                                        ]"
                                    >
                                        {{ step }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- TAB 2: CEK TARIF & ETA -->
            <div v-if="activeTab === 'tariff'" class="space-y-5 relative z-10">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label for="origin-select" class="block font-bold text-[#FAEFDB] mb-1.5">Pelabuhan / Hub Asal</label>
                        <select id="origin-select" v-model="calcOriginId" class="w-full h-11 px-3.5 bg-[#01241D] border border-[#FAEFDB]/25 rounded-xl text-[#FAEFDB] font-medium focus:outline-none focus:border-[#BE2388]">
                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                {{ loc.code }} - {{ loc.city }}, {{ loc.country }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="dest-select" class="block font-bold text-[#FAEFDB] mb-1.5">Pelabuhan / Hub Tujuan</label>
                        <select id="dest-select" v-model="calcDestId" class="w-full h-11 px-3.5 bg-[#01241D] border border-[#FAEFDB]/25 rounded-xl text-[#FAEFDB] font-medium focus:outline-none focus:border-[#BE2388]">
                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                {{ loc.code }} - {{ loc.city }}, {{ loc.country }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="mode-select" class="block font-bold text-[#FAEFDB] mb-1.5">Moda Transportasi</label>
                        <select id="mode-select" v-model="calcMode" class="w-full h-11 px-3.5 bg-[#01241D] border border-[#FAEFDB]/25 rounded-xl text-[#FAEFDB] font-medium focus:outline-none focus:border-[#BE2388]">
                            <option value="SEA">Laut (Sea Freight Container)</option>
                            <option value="AIR">Udara (Air Freight Cargo)</option>
                            <option value="LAND">Darat (Intermodal Road / Rail)</option>
                        </select>
                    </div>

                    <div>
                        <label for="weight-input" class="block font-bold text-[#FAEFDB] mb-1.5">Estimasi Bobot Kargo (kg)</label>
                        <input
                            id="weight-input"
                            v-model.number="calcWeight"
                            type="number"
                            min="1"
                            class="w-full h-11 px-3.5 bg-[#01241D] border border-[#FAEFDB]/25 rounded-xl text-[#FAEFDB] font-medium focus:outline-none focus:border-[#BE2388]"
                            placeholder="Contoh: 250"
                        />
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button
                        type="button"
                        @click="runTariffEstimate()"
                        :disabled="calcLoading"
                        class="h-11 px-6 rounded-xl bg-gradient-to-r from-[#BE2388] to-[#D42B8A] hover:brightness-110 text-white font-bold text-xs transition-all shadow-md shadow-[#BE2388]/30 flex items-center gap-2 disabled:opacity-60 cursor-pointer"
                    >
                        <span v-if="calcLoading" class="inline-block w-3.5 h-3.5 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                        <span v-else>Hitung Estimasi Biaya &amp; ETA</span>
                    </button>
                </div>

                <!-- Tariff Error -->
                <div v-if="calcError" class="p-3.5 rounded-xl bg-rose-950/60 border border-rose-500/30 text-rose-200 text-xs flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    <span>{{ calcError }}</span>
                </div>

                <!-- Tariff Result -->
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                >
                    <div v-if="calcResult" class="p-5 rounded-2xl bg-[#001F18]/80 border border-[#FAEFDB]/15 space-y-3 text-xs">
                        <div class="flex items-center justify-between pb-3 border-b border-[#FAEFDB]/10">
                            <div>
                                <span class="text-[10px] text-[#FAEFDB]/60 font-mono font-semibold block">KORIDOR RUTE</span>
                                <div class="font-bold text-[#FAEFDB]">
                                    {{ calcResult.origin.code }} ({{ calcResult.origin.city }}) &rarr; {{ calcResult.destination.code }} ({{ calcResult.destination.city }})
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg bg-[#BE2388] text-white font-mono font-bold">
                                ~{{ calcResult.distance_km }} km
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 py-1">
                            <div class="p-4 rounded-xl bg-[#001F18]/90 border border-[#FAEFDB]/15">
                                <span class="text-[10px] text-[#FAEFDB]/70 block font-mono font-semibold">ESTIMASI BIAYA</span>
                                <div class="text-lg font-extrabold font-mono text-[#FAEFDB] mt-0.5">
                                    {{ formatIdr(calcResult.total_cost_idr) }}
                                </div>
                                <span class="text-[11px] font-mono text-[#FAEFDB]/70">
                                    &asymp; ${{ Number(calcResult.total_cost_usd).toFixed(2) }} USD
                                </span>
                            </div>
                            <div class="p-4 rounded-xl bg-[#001F18]/90 border border-[#FAEFDB]/15">
                                <span class="text-[10px] text-[#FAEFDB]/70 block font-mono font-semibold">ESTIMASI TRANSIT</span>
                                <div class="text-lg font-extrabold font-mono text-emerald-400 mt-0.5">
                                    {{ calcResult.estimated_transit_days }} Hari
                                </div>
                                <span class="text-[11px] font-mono text-[#FAEFDB]/70">
                                    &asymp; {{ calcResult.estimated_transit_hours }} Jam (Bobot {{ calcResult.effective_weight_kg }} kg)
                                </span>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- TAB 3: JARINGAN HUB -->
            <div v-if="activeTab === 'hubs'" class="space-y-4 relative z-10">
                <div class="relative">
                    <i class="bi bi-search absolute left-3.5 top-3.5 text-[#FAEFDB]/60 text-xs" aria-hidden="true"></i>
                    <input
                        v-model="hubQuery"
                        type="text"
                        placeholder="Cari kode pelabuhan, nama kota, atau negara..."
                        class="w-full h-11 pl-10 pr-4 bg-[#01241D] border border-[#FAEFDB]/25 rounded-xl text-xs text-[#FAEFDB] placeholder-[#FAEFDB]/45 focus:outline-none focus:ring-2 focus:ring-[#BE2388]/50"
                    />
                </div>

                <div class="max-h-64 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 gap-3 pr-1">
                    <div
                        v-for="hub in filteredHubs"
                        :key="hub.id"
                        class="p-3.5 rounded-xl bg-[#001F18]/85 hover:bg-[#001F18] border border-[#FAEFDB]/15 transition flex items-start justify-between gap-2 shadow-xs"
                    >
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono font-extrabold text-xs text-[#BE2388]">{{ hub.code }}</span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-[#012B23] text-[#FAEFDB] border border-[#FAEFDB]/20 font-bold font-mono">{{ hub.type }}</span>
                            </div>
                            <div class="font-bold text-xs text-[#FAEFDB] truncate mt-0.5">{{ hub.name }}</div>
                            <div class="text-[11px] font-normal text-[#FAEFDB]/70">{{ hub.city }}, {{ hub.country }}</div>
                        </div>
                        <div class="text-right font-mono text-[10px] text-[#FAEFDB]/60 shrink-0">
                            {{ parseFloat(hub.latitude).toFixed(2) }},<br>{{ parseFloat(hub.longitude).toFixed(2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3 KEY STATS: HORIZONTALLY IN A SINGLE MINIMAL ROW DIRECTLY BENEATH IT -->
        <div class="mt-8 pt-8 border-t border-[#FAEFDB]/15 grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
            <div class="sm:border-r border-[#FAEFDB]/15 sm:pr-4">
                <div class="text-3xl sm:text-4xl font-extrabold font-mono text-[#FAEFDB] tracking-tight">
                    {{ displayShipments }}<span class="text-[#BE2388]">+</span>
                </div>
                <div class="text-xs font-semibold text-[#FAEFDB]/70 mt-1.5 font-mono uppercase tracking-wider">
                    Pengiriman Aktif
                </div>
            </div>
            <div class="sm:border-r border-[#FAEFDB]/15 sm:pr-4">
                <div class="text-3xl sm:text-4xl font-extrabold font-mono text-[#FAEFDB] tracking-tight">
                    {{ displayCarriers }}
                </div>
                <div class="text-xs font-semibold text-[#FAEFDB]/70 mt-1.5 font-mono uppercase tracking-wider">
                    Mitra Ekspedisi
                </div>
            </div>
            <div>
                <div class="text-3xl sm:text-4xl font-extrabold font-mono text-[#FAEFDB] tracking-tight">
                    {{ displayLocations }}
                </div>
                <div class="text-xs font-semibold text-[#FAEFDB]/70 mt-1.5 font-mono uppercase tracking-wider">
                    Pelabuhan &amp; Hub
                </div>
            </div>
        </div>
    </section>
</template>
