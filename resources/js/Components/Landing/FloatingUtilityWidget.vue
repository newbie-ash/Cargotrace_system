<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    locations: {
        type: Array,
        default: () => [],
    },
    sampleShipments: {
        type: Array,
        default: () => [],
    },
});

// Active Tab
const activeTab = ref('track'); // 'track' | 'tariff' | 'hubs'

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

// Expose public method for Fixie or parent to trigger track or tab switch
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
    <div id="utility-hub" class="relative -mt-16 sm:-mt-24 z-30 max-w-5xl mx-auto px-4 sm:px-6">
        <div class="rounded-3xl bg-slate-900/90 backdrop-blur-2xl border border-white/15 p-6 sm:p-8 shadow-[0_25px_60px_rgba(0,0,0,0.8)] relative overflow-hidden transition-all duration-300">
            <!-- Top Subtle Light Accent -->
            <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-96 h-28 bg-blue-600/20 blur-3xl pointer-events-none" aria-hidden="true"></div>

            <!-- 3-Tab Selector Bar with High Contrast Royal Blue Pill -->
            <div class="flex flex-wrap gap-2 p-1.5 bg-slate-950/80 rounded-2xl mb-6 border border-white/10 relative z-10">
                <button
                    type="button"
                    @click="activeTab = 'track'"
                    :class="[
                        'flex-1 min-w-[120px] py-2.5 px-3 rounded-xl text-xs font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer',
                        activeTab === 'track'
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30 font-bold'
                            : 'text-slate-400 hover:text-white hover:bg-white/5'
                    ]"
                >
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <span>Cek Resi Instan</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'tariff'"
                    :class="[
                        'flex-1 min-w-[120px] py-2.5 px-3 rounded-xl text-xs font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer',
                        activeTab === 'tariff'
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30 font-bold'
                            : 'text-slate-400 hover:text-white hover:bg-white/5'
                    ]"
                >
                    <i class="bi bi-calculator" aria-hidden="true"></i>
                    <span>Cek Tarif & ETA</span>
                </button>

                <button
                    type="button"
                    @click="activeTab = 'hubs'"
                    :class="[
                        'flex-1 min-w-[120px] py-2.5 px-3 rounded-xl text-xs font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer',
                        activeTab === 'hubs'
                            ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30 font-bold'
                            : 'text-slate-400 hover:text-white hover:bg-white/5'
                    ]"
                >
                    <i class="bi bi-geo-alt" aria-hidden="true"></i>
                    <span>Jaringan Hub ({{ locations.length }})</span>
                </button>
            </div>

            <!-- TAB 1: CEK RESI INSTAN -->
            <div v-if="activeTab === 'track'" class="space-y-4 relative z-10">
                <div>
                    <label for="tracking-main-input" class="block text-xs font-bold text-slate-300 mb-1.5">
                        Nomor Resi / Referensi Pengiriman (Pencarian Aman Tanpa Login)
                    </label>
                    <div class="flex flex-col sm:flex-row gap-2.5">
                        <div class="relative flex-1">
                            <i class="bi bi-upc-scan absolute left-3.5 top-3.5 text-slate-500" aria-hidden="true"></i>
                            <input
                                id="tracking-main-input"
                                v-model="trackingInput"
                                @keyup.enter="runPublicTrack()"
                                type="text"
                                placeholder="Contoh: CT-2026-XXXXXX atau PO-JKT-RTM-001"
                                class="w-full h-11 pl-10 pr-4 bg-slate-950/70 border border-white/15 rounded-xl text-xs font-mono text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:border-blue-500"
                            />
                        </div>
                        <button
                            type="button"
                            @click="runPublicTrack()"
                            :disabled="trackingLoading"
                            class="h-11 px-7 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition inline-flex items-center justify-center gap-2 shadow-lg shadow-blue-600/25 disabled:opacity-60 cursor-pointer shrink-0"
                        >
                            <i :class="trackingLoading ? 'bi bi-hourglass-split animate-spin' : 'bi bi-search'" aria-hidden="true"></i>
                            <span>{{ trackingLoading ? 'Melacak...' : 'Lacak Resi' }}</span>
                        </button>
                    </div>

                    <!-- 1-Click Sample Demo Pills -->
                    <div v-if="sampleShipments.length" class="flex flex-wrap items-center gap-1.5 mt-3">
                        <span class="text-[11px] text-slate-400">Resi Sampel Uji Coba:</span>
                        <button
                            v-for="sample in sampleShipments"
                            :key="sample"
                            @click="runPublicTrack(sample)"
                            type="button"
                            class="px-2.5 py-1 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-300 border border-blue-500/30 text-[11px] font-mono transition cursor-pointer"
                        >
                            {{ sample }}
                        </button>
                    </div>
                </div>

                <!-- Error Notification -->
                <div v-if="trackingError" class="p-3.5 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2">
                    <i class="bi bi-exclamation-circle-fill text-rose-400" aria-hidden="true"></i>
                    <span>{{ trackingError }}</span>
                </div>

                <!-- Public Tracking Result Card in Glassmorphism -->
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                >
                    <div v-if="trackingResult" class="p-5 rounded-2xl bg-slate-950/80 border border-white/15 space-y-4">
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-white/10">
                            <div>
                                <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400 block">NOMOR PENGIRIMAN</span>
                                <strong class="text-base font-extrabold font-mono text-blue-400">{{ trackingResult.shipment_number }}</strong>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    v-if="trackingResult.is_delayed"
                                    class="px-2.5 py-1 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[11px] font-bold"
                                >
                                    DELAY (+{{ trackingResult.delay_duration_hours }}j)
                                </span>
                                <span
                                    v-else
                                    class="px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[11px] font-bold"
                                >
                                    ON-TIME SLA
                                </span>
                                <span class="px-2.5 py-1 rounded-lg bg-white text-slate-950 font-mono text-[11px] font-bold">
                                    {{ trackingResult.current_status }}
                                </span>
                            </div>
                        </div>

                        <!-- Route Grid -->
                        <div class="grid grid-cols-3 items-center gap-2 text-xs">
                            <div>
                                <span class="text-[10px] text-slate-400 block font-mono">ASAL (ORIGIN)</span>
                                <strong class="font-mono text-white text-sm">{{ trackingResult.origin?.code }}</strong>
                                <div class="text-[11px] text-slate-300 truncate">{{ trackingResult.origin?.city }}</div>
                            </div>
                            <div class="text-center">
                                <span class="text-[10px] font-mono text-blue-400 font-bold block">{{ trackingResult.carrier?.transport_mode }}</span>
                                <div class="h-0.5 bg-slate-700 my-1 relative">
                                    <i class="bi bi-caret-right-fill text-blue-400 absolute -top-2 right-0 text-xs" aria-hidden="true"></i>
                                </div>
                                <span class="text-[10px] text-slate-400 truncate block">{{ trackingResult.carrier?.name }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-slate-400 block font-mono">TUJUAN (DESTINATION)</span>
                                <strong class="font-mono text-white text-sm">{{ trackingResult.destination?.code }}</strong>
                                <div class="text-[11px] text-slate-300 truncate">{{ trackingResult.destination?.city }}</div>
                            </div>
                        </div>

                        <!-- Stepper Progress Bar -->
                        <div class="flex items-center justify-between gap-1 pt-1">
                            <div
                                v-for="step in lifecycleSteps"
                                :key="step"
                                class="flex-1 text-center"
                            >
                                <div
                                    class="h-1.5 rounded-full mb-1 transition-colors"
                                    :class="isStepReached(step, trackingResult.current_status) ? 'bg-blue-500 shadow-[0_0_8px_#3b82f6]' : 'bg-slate-800'"
                                ></div>
                                <span class="text-[9px] font-mono font-bold block truncate" :class="isStepReached(step, trackingResult.current_status) ? 'text-blue-300' : 'text-slate-500'">
                                    {{ step }}
                                </span>
                            </div>
                        </div>

                        <!-- 3 Checkpoints Terakhir -->
                        <div v-if="trackingResult.recent_events?.length" class="pt-3 border-t border-white/10 space-y-2">
                            <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-400">
                                3 Checkpoint Terbaru
                            </div>
                            <div
                                v-for="evt in trackingResult.recent_events"
                                :key="evt.id"
                                class="p-2.5 rounded-xl bg-slate-900/90 border border-white/10 text-xs flex items-start justify-between gap-3"
                            >
                                <div class="space-y-0.5">
                                    <div class="font-bold text-white flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full" :class="evt.event_type === 'EXCEPTION' ? 'bg-amber-400 shadow-[0_0_6px_#f59e0b]' : 'bg-blue-400 shadow-[0_0_6px_#60a5fa]'"></span>
                                        <span>{{ evt.status }}</span>
                                        <span v-if="evt.location" class="text-[11px] font-normal text-slate-400">({{ evt.location.code }} &bull; {{ evt.location.city }})</span>
                                    </div>
                                    <p class="text-[11px] text-slate-300 leading-relaxed">{{ evt.description }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-[10px] font-mono text-slate-400 block">
                                        {{ new Date(evt.occurred_at).toLocaleDateString() }}
                                    </span>
                                    <span v-if="evt.weather_snapshot" class="text-[10px] font-mono text-amber-400 inline-flex items-center gap-1">
                                        <i class="bi bi-cloud-sun" aria-hidden="true"></i> {{ evt.weather_snapshot.temperature }}°C
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- TAB 2: CEK TARIF & ETA -->
            <div v-if="activeTab === 'tariff'" class="space-y-4 relative z-10">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label for="origin-select" class="block font-bold text-slate-300 mb-1">Pelabuhan / Hub Asal</label>
                        <select id="origin-select" v-model="calcOriginId" class="w-full h-10 px-3 bg-slate-950/70 border border-white/15 rounded-xl text-white">
                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                {{ loc.code }} - {{ loc.city }}, {{ loc.country }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label for="dest-select" class="block font-bold text-slate-300 mb-1">Pelabuhan / Hub Tujuan</label>
                        <select id="dest-select" v-model="calcDestId" class="w-full h-10 px-3 bg-slate-950/70 border border-white/15 rounded-xl text-white">
                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                {{ loc.code }} - {{ loc.city }}, {{ loc.country }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label for="mode-select" class="block font-bold text-slate-300 mb-1">Moda Pengiriman</label>
                        <select id="mode-select" v-model="calcMode" class="w-full h-10 px-3 bg-slate-950/70 border border-white/15 rounded-xl text-white font-semibold">
                            <option value="SEA">SEA: Kapal Kontainer Laut (FCL/LCL)</option>
                            <option value="AIR">AIR: Express Air Cargo Udara</option>
                            <option value="LAND">LAND: Inland Intermodal Darat</option>
                        </select>
                    </div>
                    <div>
                        <label for="weight-input" class="block font-bold text-slate-300 mb-1">Total Bobot Kargo (Kg)</label>
                        <input
                            id="weight-input"
                            v-model="calcWeight"
                            type="number"
                            min="1"
                            max="500000"
                            class="w-full h-10 px-3 bg-slate-950/70 border border-white/15 rounded-xl font-mono text-white"
                        />
                    </div>
                </div>

                <button
                    type="button"
                    @click="runTariffEstimate"
                    :disabled="calcLoading"
                    class="w-full h-11 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold rounded-xl transition inline-flex items-center justify-center gap-2 shadow-lg shadow-blue-600/30 cursor-pointer disabled:opacity-60"
                >
                    <i class="bi bi-lightning-charge-fill text-amber-300" aria-hidden="true"></i>
                    <span>{{ calcLoading ? 'Mengkalkulasi...' : 'Kalkulasi Estimasi Biaya & Durasi Transit' }}</span>
                </button>

                <div v-if="calcError" class="p-3 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs">
                    {{ calcError }}
                </div>

                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                >
                    <div v-if="calcResult" class="p-5 rounded-2xl bg-slate-950/80 border border-white/15 space-y-3 text-xs">
                        <div class="flex items-center justify-between border-b border-white/10 pb-3">
                            <div>
                                <span class="text-[10px] font-mono font-bold uppercase text-blue-400 block">{{ calcResult.service_name }}</span>
                                <div class="font-bold text-white">
                                    {{ calcResult.origin.code }} ({{ calcResult.origin.city }}) &rarr; {{ calcResult.destination.code }} ({{ calcResult.destination.city }})
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg bg-blue-500/20 text-blue-300 border border-blue-500/30 font-mono font-bold">
                                ~{{ calcResult.distance_km }} km
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 py-1">
                            <div class="p-3.5 rounded-xl bg-slate-900/90 border border-white/10">
                                <span class="text-[10px] text-slate-400 block font-mono">ESTIMASI BIAYA</span>
                                <div class="text-lg font-extrabold font-mono text-blue-300 mt-0.5">
                                    {{ formatIdr(calcResult.total_cost_idr) }}
                                </div>
                                <span class="text-[11px] font-mono text-slate-400">
                                    &asymp; ${{ Number(calcResult.total_cost_usd).toFixed(2) }} USD
                                </span>
                            </div>
                            <div class="p-3.5 rounded-xl bg-slate-900/90 border border-white/10">
                                <span class="text-[10px] text-slate-400 block font-mono">ESTIMASI TRANSIT</span>
                                <div class="text-lg font-extrabold font-mono text-emerald-400 mt-0.5">
                                    {{ calcResult.estimated_transit_days }} Hari
                                </div>
                                <span class="text-[11px] font-mono text-slate-400">
                                    &asymp; {{ calcResult.estimated_transit_hours }} Jam (Bobot {{ calcResult.effective_weight_kg }} kg)
                                </span>
                            </div>
                        </div>

                        <div class="text-[10px] text-slate-400 font-mono flex flex-wrap justify-between gap-2 pt-1">
                            <span>Base: ${{ calcResult.breakdown.base_fee_usd }}</span>
                            <span>Weight: ${{ calcResult.breakdown.weight_cost_usd }}</span>
                            <span>Distance: ${{ calcResult.breakdown.distance_cost_usd }}</span>
                        </div>
                    </div>
                </Transition>
            </div>

            <!-- TAB 3: JARINGAN HUB -->
            <div v-if="activeTab === 'hubs'" class="space-y-3 relative z-10">
                <div class="relative">
                    <i class="bi bi-search absolute left-3.5 top-3 text-slate-500 text-xs" aria-hidden="true"></i>
                    <input
                        v-model="hubQuery"
                        type="text"
                        placeholder="Cari kode pelabuhan, nama kota, atau negara..."
                        class="w-full h-10 pl-9 pr-3 bg-slate-950/70 border border-white/15 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500/40"
                    />
                </div>

                <div class="max-h-64 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 gap-2.5 pr-1">
                    <div
                        v-for="hub in filteredHubs"
                        :key="hub.id"
                        class="p-3 rounded-xl bg-slate-950/70 hover:bg-white/5 border border-white/10 transition flex items-start justify-between gap-2"
                    >
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="font-mono font-extrabold text-xs text-blue-400">{{ hub.code }}</span>
                                <span class="text-[9px] px-1.5 py-0.5 rounded bg-white/10 text-slate-300 font-bold font-mono">{{ hub.type }}</span>
                            </div>
                            <div class="font-bold text-xs text-white truncate mt-0.5">{{ hub.name }}</div>
                            <div class="text-[11px] text-slate-400">{{ hub.city }}, {{ hub.country }}</div>
                        </div>
                        <div class="text-right font-mono text-[10px] text-slate-500 shrink-0">
                            {{ parseFloat(hub.latitude).toFixed(2) }},<br>{{ parseFloat(hub.longitude).toFixed(2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
