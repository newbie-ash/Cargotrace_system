<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    stats: {
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
    tariffs: {
        type: Array,
        default: () => [],
    },
    sampleShipments: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const authUser = computed(() => page.props.auth?.user || null);

// Animated Hero Counters
const displayShipments = ref(0);
const displayCarriers = ref(0);
const displayLocations = ref(0);

const animateCounter = (targetRef, endValue, duration = 900) => {
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

// Mouse Parallax on Hero Typography
const parallaxX = ref(0);
const parallaxY = ref(0);
const handleHeroMouseMove = (e) => {
    const rect = e.currentTarget.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width - 0.5;
    const y = (e.clientY - rect.top) / rect.height - 0.5;
    parallaxX.value = x * 24;
    parallaxY.value = y * 14;
};

// Winzy Bottom-Right Mode Slider (01/03)
const activeSlideIndex = ref(0);
const heroSlides = computed(() => {
    const modes = ['SEA', 'AIR', 'LAND'];
    return modes.map((mode, idx) => {
        const tariff = props.tariffs.find(t => t.transport_mode === mode);
        const modeCarriers = props.carriers.filter(c => c.transport_mode === mode);
        return {
            indexLabel: `0${idx + 1}/03`,
            mode,
            title: tariff?.service_name || `${mode} Global Logistics`,
            carriersCount: modeCarriers.length,
            baseFee: tariff?.base_fee_usd || '95.00',
            speed: tariff?.avg_speed_kmh || 45,
            icon: mode === 'SEA' ? 'bi-water' : (mode === 'AIR' ? 'bi-airplane-engines' : 'bi-truck'),
        };
    });
});

let slideTimer = null;

// Veluno Stage 2: 3-Tab Logistics Utility
const activeUtilityTab = ref('track'); // 'track' | 'tariff' | 'hubs'

// Tab 1: Instant Public Tracking
const trackingInput = ref(props.sampleShipments[0] || '');
const trackingLoading = ref(false);
const trackingError = ref('');
const trackingResult = ref(null);

const runPublicTrack = async (customCode = null) => {
    const code = (customCode ?? trackingInput.value).trim();
    if (!code) {
        trackingError.value = 'Masukkan nomor resi atau referensi terlebih dahulu.';
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
            trackingError.value = data.message || 'Data pengiriman tidak ditemukan.';
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

// Tab 2: Tariff & Route Estimator
const calcOriginId = ref(props.locations[0]?.id || '');
const calcDestId = ref(props.locations[1]?.id || '');
const calcMode = ref('SEA');
const calcWeight = ref(250);
const calcLoading = ref(false);
const calcError = ref('');
const calcResult = ref(null);

const runTariffEstimate = async () => {
    if (!calcOriginId.value || !calcDestId.value) {
        calcError.value = 'Pilih pelabuhan asal dan tujuan.';
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
            calcError.value = payload.message || 'Gagal menghitung estimasi tarif.';
        } else {
            calcResult.value = payload.data;
        }
    } catch {
        calcError.value = 'Terjadi kendala saat menghitung estimasi tarif.';
    } finally {
        calcLoading.value = false;
    }
};

// Tab 3: Hubs Filter
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

const scrollToUtility = (tab = 'track') => {
    activeUtilityTab.value = tab;
    const el = document.getElementById('utility-hub');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
};

onMounted(() => {
    animateCounter(displayShipments, props.stats.total_shipments || 0, 900);
    animateCounter(displayCarriers, props.stats.active_carriers || 0, 900);
    animateCounter(displayLocations, props.stats.total_locations || 0, 900);

    slideTimer = setInterval(() => {
        activeSlideIndex.value = (activeSlideIndex.value + 1) % heroSlides.value.length;
    }, 5000);
});

onUnmounted(() => {
    if (slideTimer) clearInterval(slideTimer);
});
</script>

<template>
    <Head title="Global Shipment Visibility & Real-Time Logistics Tracking" />

    <div class="min-h-screen font-sans selection:bg-cyan-400 selection:text-slate-950">
        <!-- ======================================================================= -->
        <!-- FLOATING GLASS PILL NAVBAR (WINZY STYLE)                                -->
        <!-- ======================================================================= -->
        <div class="fixed top-4 inset-x-0 z-50 flex justify-center px-4 pointer-events-none">
            <nav class="pointer-events-auto backdrop-blur-xl bg-slate-900/75 border border-white/15 rounded-full px-4 sm:px-6 py-2.5 shadow-[0_8px_32px_0_rgba(0,0,0,0.36)] flex items-center justify-between gap-4 sm:gap-8 max-w-4xl w-full text-white">
                <Link href="/" class="flex items-center gap-2 font-extrabold text-sm sm:text-base tracking-tight">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 shadow-[0_0_12px_#22d3ee] animate-pulse"></span>
                    <span>CargoTrace</span>
                </Link>

                <div class="hidden md:flex items-center gap-6 text-xs font-medium text-slate-300">
                    <button @click="scrollToUtility('track')" class="hover:text-white transition">Cek Resi</button>
                    <button @click="scrollToUtility('tariff')" class="hover:text-white transition">Cek Tarif</button>
                    <button @click="scrollToUtility('hubs')" class="hover:text-white transition">Jaringan Hub</button>
                    <a href="#bento" class="hover:text-white transition">Arsitektur</a>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        v-if="authUser"
                        href="/dashboard"
                        class="px-4 py-1.5 rounded-full bg-white text-slate-950 hover:bg-cyan-50 text-xs font-bold transition inline-flex items-center gap-1.5 shadow-sm"
                    >
                        <span>Dashboard</span>
                        <i class="bi bi-arrow-up-right text-[11px]"></i>
                    </Link>
                    <Link
                        v-else
                        href="/login"
                        class="px-4 py-1.5 rounded-full bg-white text-slate-950 hover:bg-cyan-50 text-xs font-bold transition inline-flex items-center gap-1.5 shadow-sm"
                    >
                        <span>Masuk Portal</span>
                        <i class="bi bi-arrow-up-right text-[11px]"></i>
                    </Link>
                </div>
            </nav>
        </div>

        <!-- ======================================================================= -->
        <!-- STAGE 1: WINZY DARK CINEMATIC GLASS HERO                                -->
        <!-- ======================================================================= -->
        <section
            @mousemove="handleHeroMouseMove"
            class="relative min-h-[92vh] overflow-hidden bg-gradient-to-br from-slate-950 via-[#0B162C] to-slate-900 text-white pt-28 pb-12 px-4 sm:px-8 flex flex-col justify-between"
        >
            <!-- Atmospheric Glowing Spotlights & Architectural Grid -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-24 -right-24 w-[480px] h-[480px] rounded-full bg-amber-500/15 blur-[130px]"></div>
                <div class="absolute bottom-10 -left-24 w-[520px] h-[520px] rounded-full bg-cyan-500/15 blur-[140px]"></div>
                <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff08_1px,transparent_1px),linear-gradient(to_bottom,#ffffff08_1px,transparent_1px)] bg-[size:48px_48px]"></div>
            </div>

            <!-- Top Sub-tag -->
            <div class="relative z-10 max-w-7xl mx-auto w-full flex items-center justify-between text-[11px] font-mono text-slate-400">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/5 border border-white/10">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                    REAL-TIME GLOBAL FREIGHT TELEMETRY
                </span>
                <span class="hidden sm:inline">MULTIMODAL: SEA &bull; AIR &bull; LAND</span>
            </div>

            <!-- Giant Layered Depth Typography + Foreground Visual Route Layer -->
            <div class="relative z-10 max-w-7xl mx-auto w-full my-auto py-10 flex flex-col items-center justify-center">
                <!-- Back Layer: Giant Text with Mouse Parallax -->
                <div
                    class="select-none pointer-events-none text-center transition-transform duration-200 ease-out"
                    :style="{ transform: `translate3d(${parallaxX * -0.5}px, ${parallaxY * -0.5}px, 0)` }"
                >
                    <h2 class="text-[14vw] sm:text-[11.5vw] font-black tracking-tighter leading-none text-transparent bg-clip-text bg-gradient-to-b from-white/25 via-white/10 to-transparent">
                        CARGOTRACE
                    </h2>
                </div>

                <!-- Foreground Layer: Floating Maritime Arc Card + Sharp Headline -->
                <div
                    class="-mt-8 sm:-mt-14 relative z-20 max-w-3xl mx-auto text-center transition-transform duration-300 ease-out"
                    :style="{ transform: `translate3d(${parallaxX * 0.25}px, ${parallaxY * 0.25}px, 0)` }"
                >
                    <div class="inline-flex items-center gap-3 px-4 py-2 rounded-2xl bg-slate-900/60 backdrop-blur-xl border border-white/15 shadow-2xl mb-5">
                        <span class="font-mono text-xs font-bold text-cyan-400">IDJKT</span>
                        <div class="flex items-center gap-1.5 w-20 sm:w-32">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                            <div class="h-0.5 flex-1 bg-gradient-to-r from-cyan-400 via-amber-400 to-emerald-400 relative">
                                <span class="w-2 h-2 rounded-full bg-white shadow-[0_0_8px_#fff] absolute -top-[3px] left-1/2 animate-pulse"></span>
                            </div>
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        </div>
                        <span class="font-mono text-xs font-bold text-emerald-400">NLRTM</span>
                        <span class="text-[10px] px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 font-semibold">
                            ACTIVE ROUTE
                        </span>
                    </div>

                    <h1 class="text-3xl sm:text-5xl md:text-6xl font-extrabold tracking-tight leading-[1.1] text-white mb-4">
                        End-to-End Global Shipment Visibility.
                    </h1>
                    <p class="text-sm sm:text-base text-slate-300 max-w-xl mx-auto leading-relaxed">
                        Pantau pergerakan kontainer interkontinental secara deterministik dengan validasi rute geospasial, kalkulasi tarif transparan, dan peringatan keterlambatan real-time.
                    </p>
                </div>
            </div>

            <!-- Bottom Stage Row: Left Metrics + CTA | Right Glass Slider Card -->
            <div class="relative z-20 max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-8 items-end">
                <!-- Bottom-Left Live DB Metrics & Pill CTA -->
                <div class="lg:col-span-7 flex flex-col sm:flex-row sm:items-end justify-between gap-6">
                    <div class="grid grid-cols-3 gap-6 sm:gap-10">
                        <div>
                            <div class="text-3xl sm:text-4xl font-black font-mono text-white">
                                {{ displayShipments }}<span class="text-cyan-400">+</span>
                            </div>
                            <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold mt-1">
                                Active Shipments
                            </div>
                        </div>
                        <div>
                            <div class="text-3xl sm:text-4xl font-black font-mono text-white">
                                {{ displayCarriers }}
                            </div>
                            <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold mt-1">
                                Global Carriers
                            </div>
                        </div>
                        <div>
                            <div class="text-3xl sm:text-4xl font-black font-mono text-white">
                                {{ displayLocations }}
                            </div>
                            <div class="text-[11px] uppercase tracking-wider text-slate-400 font-semibold mt-1">
                                Port & Hubs
                            </div>
                        </div>
                    </div>

                    <button
                        @click="scrollToUtility('track')"
                        class="group self-start sm:self-end pl-5 pr-2 py-2 rounded-full bg-white text-slate-950 font-bold text-xs sm:text-sm inline-flex items-center gap-3 hover:bg-cyan-50 transition-all shadow-xl hover:-translate-y-0.5 cursor-pointer"
                    >
                        <span>Lacak Kargo</span>
                        <span class="w-8 h-8 rounded-full bg-slate-950 text-white flex items-center justify-center group-hover:rotate-45 transition-transform duration-300">
                            <i class="bi bi-arrow-up-right text-xs"></i>
                        </span>
                    </button>
                </div>

                <!-- Bottom-Right Glass Slider Card (01/03) -->
                <div class="lg:col-span-5 flex justify-start lg:justify-end">
                    <div class="w-full max-w-sm bg-slate-900/45 backdrop-blur-xl border border-white/15 rounded-2xl p-5 shadow-[0_8px_32px_0_rgba(0,0,0,0.36)]">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[10px] font-mono uppercase tracking-widest text-cyan-400 font-bold">
                                MODA EKSPEDISI AKTIF
                            </span>
                            <span class="font-mono text-xs font-bold text-white/80">
                                {{ heroSlides[activeSlideIndex].indexLabel }}
                            </span>
                        </div>

                        <div class="flex items-center gap-4 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-white/10 border border-white/15 flex items-center justify-center text-cyan-400 text-xl shrink-0">
                                <i :class="['bi', heroSlides[activeSlideIndex].icon]"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold text-sm text-white truncate">
                                    {{ heroSlides[activeSlideIndex].title }}
                                </div>
                                <div class="text-xs text-slate-300 mt-0.5">
                                    {{ heroSlides[activeSlideIndex].carriersCount }} Carrier Mitra &bull; Base ${{ heroSlides[activeSlideIndex].baseFee }}
                                </div>
                            </div>
                        </div>

                        <!-- Progress Indicator Bars -->
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                v-for="(slide, idx) in heroSlides"
                                :key="slide.mode"
                                @click="activeSlideIndex = idx"
                                class="h-1.5 rounded-full transition-all cursor-pointer"
                                :class="activeSlideIndex === idx ? 'bg-cyan-400' : 'bg-white/20 hover:bg-white/40'"
                                :title="slide.mode"
                            ></button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================================================================= -->
        <!-- STAGE 2: VELUNO EDITORIAL SPLIT + 3-TAB LOGISTICS UTILITY               -->
        <!-- ======================================================================= -->
        <section id="utility-hub" class="relative bg-[#F8FAFC] text-slate-900 py-20 px-4 sm:px-8 border-t border-slate-200 overflow-hidden">
            <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center relative">
                <!-- Centerpiece: Rotating Circular SVG Badge (Desktop overlapping boundary) -->
                <div class="hidden xl:flex absolute left-[38%] top-1/2 -translate-y-1/2 -translate-x-1/2 z-30 pointer-events-auto">
                    <button
                        @click="scrollToUtility('track')"
                        title="Cek Resi Sekarang"
                        class="group relative w-32 h-32 rounded-full bg-white/80 backdrop-blur-md border border-slate-200 shadow-2xl flex items-center justify-center hover:scale-110 transition-transform duration-300 cursor-pointer"
                    >
                        <svg viewBox="0 0 140 140" class="w-full h-full animate-[spin_14s_linear_infinite]">
                            <defs>
                                <path id="circleTextPath" d="M 70, 70 m -50, 0 a 50,50 0 1,1 100,0 a 50,50 0 1,1 -100,0" />
                            </defs>
                            <text class="fill-slate-800 text-[10px] font-mono font-bold uppercase tracking-[0.2em]">
                                <textPath href="#circleTextPath" startOffset="0%">
                                    REAL-TIME TRACKING &bull; GLOBAL VISIBILITY &bull;
                                </textPath>
                            </text>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="w-11 h-11 rounded-full bg-slate-950 text-white flex items-center justify-center shadow-md group-hover:bg-blue-600 transition-colors">
                                <i class="bi bi-arrow-up-right text-sm"></i>
                            </span>
                        </div>
                    </button>
                </div>

                <!-- Left Column: Veluno Clean Editorial Copy -->
                <div class="lg:col-span-5 space-y-6 pr-0 xl:pr-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700 text-[11px] font-bold uppercase tracking-wider">
                        <i class="bi bi-compass"></i>
                        <span>Editorial Freight Command</span>
                    </div>

                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-slate-950 leading-[1.12]">
                        Timeless Precision Meets Global Visibility.
                    </h2>

                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                        Menggabungkan kemudahan cek resi instan ala kurir ekspres dengan ketangguhan analitik kargo interkontinental. Pantau status kontainer, hitung tarif transparan, dan eksplorasi jaringan pelabuhan tanpa harus login.
                    </p>

                    <div class="flex flex-wrap items-center gap-3 pt-1">
                        <Link
                            :href="authUser ? '/dashboard' : '/login'"
                            class="group pl-6 pr-2 py-2.5 rounded-full bg-slate-950 text-white text-xs sm:text-sm font-bold inline-flex items-center gap-3 hover:bg-blue-700 transition-all shadow-lg"
                        >
                            <span>{{ authUser ? 'Buka Operational Dashboard' : 'Masuk Portal Klien' }}</span>
                            <span class="w-8 h-8 rounded-full bg-white text-slate-950 flex items-center justify-center group-hover:rotate-45 transition-transform">
                                <i class="bi bi-arrow-up-right text-xs"></i>
                            </span>
                        </Link>
                    </div>

                    <!-- Mini Feature Card (Left Bottom) -->
                    <div class="p-4 rounded-2xl bg-white/80 backdrop-blur-lg border border-slate-200 shadow-lg shadow-slate-900/5 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center font-bold text-lg">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-900">SLA On-Time Reliability</div>
                                <div class="text-[11px] text-slate-500">Live Open-Meteo & Nominatim Active</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-2xl font-black font-mono text-emerald-600">{{ stats.on_time_rate }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Cutout Showcase Card + 3-Tab Sentral/J&T/Deliveree Utility -->
                <div class="lg:col-span-7">
                    <div class="rounded-3xl rounded-tr-[4.5rem] bg-white/90 backdrop-blur-xl border border-slate-200/90 shadow-2xl shadow-slate-900/10 p-5 sm:p-8 relative overflow-hidden">
                        <!-- 3-Tab Selector Bar -->
                        <div class="flex flex-wrap gap-2 p-1.5 bg-slate-100 rounded-2xl mb-6 border border-slate-200/70">
                            <button
                                @click="activeUtilityTab = 'track'"
                                :class="[
                                    'flex-1 min-w-[120px] py-2.5 px-3 rounded-xl text-xs font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer',
                                    activeUtilityTab === 'track'
                                        ? 'bg-slate-950 text-white shadow-sm'
                                        : 'text-slate-600 hover:text-slate-950 hover:bg-white/60'
                                ]"
                            >
                                <i class="bi bi-search"></i>
                                <span>Cek Resi Instan</span>
                            </button>

                            <button
                                @click="activeUtilityTab = 'tariff'"
                                :class="[
                                    'flex-1 min-w-[120px] py-2.5 px-3 rounded-xl text-xs font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer',
                                    activeUtilityTab === 'tariff'
                                        ? 'bg-slate-950 text-white shadow-sm'
                                        : 'text-slate-600 hover:text-slate-950 hover:bg-white/60'
                                ]"
                            >
                                <i class="bi bi-calculator"></i>
                                <span>Cek Tarif & ETA</span>
                            </button>

                            <button
                                @click="activeUtilityTab = 'hubs'"
                                :class="[
                                    'flex-1 min-w-[120px] py-2.5 px-3 rounded-xl text-xs font-bold transition-all inline-flex items-center justify-center gap-2 cursor-pointer',
                                    activeUtilityTab === 'hubs'
                                        ? 'bg-slate-950 text-white shadow-sm'
                                        : 'text-slate-600 hover:text-slate-950 hover:bg-white/60'
                                ]"
                            >
                                <i class="bi bi-geo-alt"></i>
                                <span>Hubs ({{ locations.length }})</span>
                            </button>
                        </div>

                        <!-- =============================================================== -->
                        <!-- TAB 1: CEK RESI INSTAN (SENTRAL / J&T STYLE)                    -->
                        <!-- =============================================================== -->
                        <div v-if="activeUtilityTab === 'track'" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                    Nomor Resi / PO Referensi (Public Safe Lookup)
                                </label>
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <div class="relative flex-1">
                                        <i class="bi bi-upc-scan absolute left-3.5 top-3.5 text-slate-400"></i>
                                        <input
                                            v-model="trackingInput"
                                            @keyup.enter="runPublicTrack()"
                                            type="text"
                                            placeholder="Contoh: CT-2026-XXXXXX atau PO-JKT-RTM-001"
                                            class="w-full h-11 pl-10 pr-4 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono text-slate-900 focus:outline-none focus:ring-2 focus:ring-blue-600/30 focus:border-blue-600"
                                        />
                                    </div>
                                    <button
                                        @click="runPublicTrack()"
                                        :disabled="trackingLoading"
                                        class="h-11 px-6 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition inline-flex items-center justify-center gap-2 shadow-sm disabled:opacity-60 cursor-pointer"
                                    >
                                        <i :class="trackingLoading ? 'bi bi-hourglass-split animate-spin' : 'bi bi-search'"></i>
                                        <span>{{ trackingLoading ? 'Melacak...' : 'Lacak' }}</span>
                                    </button>
                                </div>

                                <!-- 1-Click Sample Pills -->
                                <div v-if="sampleShipments.length" class="flex flex-wrap items-center gap-1.5 mt-2.5">
                                    <span class="text-[11px] text-slate-500">Coba resi demo:</span>
                                    <button
                                        v-for="sample in sampleShipments"
                                        :key="sample"
                                        @click="runPublicTrack(sample)"
                                        type="button"
                                        class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-blue-50 hover:text-blue-700 border border-slate-200 text-[11px] font-mono text-slate-700 transition cursor-pointer"
                                    >
                                        {{ sample }}
                                    </button>
                                </div>
                            </div>

                            <!-- Error Alert -->
                            <div v-if="trackingError" class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <span>{{ trackingError }}</span>
                            </div>

                            <!-- Smooth Public Result Card -->
                            <Transition
                                enter-active-class="transition duration-300 ease-out"
                                enter-from-class="opacity-0 -translate-y-2"
                                enter-to-class="opacity-100 translate-y-0"
                            >
                                <div v-if="trackingResult" class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                                    <!-- Top Row -->
                                    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-200">
                                        <div>
                                            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nomor Konsinyasi</div>
                                            <div class="text-base font-extrabold font-mono text-slate-950">{{ trackingResult.shipment_number }}</div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span
                                                v-if="trackingResult.is_delayed"
                                                class="px-2.5 py-1 rounded-lg bg-rose-100 text-rose-800 border border-rose-200 text-[11px] font-bold"
                                            >
                                                DELAY (+{{ trackingResult.delay_duration_hours }}j)
                                            </span>
                                            <span
                                                v-else
                                                class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 border border-emerald-200 text-[11px] font-bold"
                                            >
                                                ON-TIME SLA
                                            </span>
                                            <span class="px-2.5 py-1 rounded-lg bg-slate-900 text-white font-mono text-[11px] font-bold">
                                                {{ trackingResult.current_status }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Route Summary -->
                                    <div class="grid grid-cols-3 items-center gap-2 text-xs">
                                        <div>
                                            <span class="text-[10px] text-slate-500 block">ORIGIN</span>
                                            <strong class="font-mono text-slate-900 text-sm">{{ trackingResult.origin?.code }}</strong>
                                            <div class="text-[11px] text-slate-600 truncate">{{ trackingResult.origin?.city }}</div>
                                        </div>
                                        <div class="text-center">
                                            <span class="text-[10px] font-mono text-blue-600 font-bold block">{{ trackingResult.carrier?.transport_mode }}</span>
                                            <div class="h-0.5 bg-slate-300 my-1 relative">
                                                <i class="bi bi-caret-right-fill text-slate-500 absolute -top-2 right-0 text-xs"></i>
                                            </div>
                                            <span class="text-[10px] text-slate-500 truncate block">{{ trackingResult.carrier?.name }}</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[10px] text-slate-500 block">DESTINATION</span>
                                            <strong class="font-mono text-slate-900 text-sm">{{ trackingResult.destination?.code }}</strong>
                                            <div class="text-[11px] text-slate-600 truncate">{{ trackingResult.destination?.city }}</div>
                                        </div>
                                    </div>

                                    <!-- Status Progress Stepper -->
                                    <div class="flex items-center justify-between gap-1 pt-1">
                                        <div
                                            v-for="step in lifecycleSteps"
                                            :key="step"
                                            class="flex-1 text-center"
                                        >
                                            <div
                                                class="h-1.5 rounded-full mb-1 transition-colors"
                                                :class="isStepReached(step, trackingResult.current_status) ? 'bg-blue-600' : 'bg-slate-200'"
                                            ></div>
                                            <span class="text-[9px] font-mono font-bold block truncate" :class="isStepReached(step, trackingResult.current_status) ? 'text-blue-700' : 'text-slate-400'">
                                                {{ step }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- 3 Latest Tracking Checkpoints -->
                                    <div v-if="trackingResult.recent_events?.length" class="pt-3 border-t border-slate-200 space-y-2.5">
                                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-500">
                                            3 Aktivitas Checkpoint Terakhir
                                        </div>
                                        <div
                                            v-for="evt in trackingResult.recent_events"
                                            :key="evt.id"
                                            class="p-2.5 rounded-xl bg-white border border-slate-200/80 text-xs flex items-start justify-between gap-3"
                                        >
                                            <div class="space-y-0.5">
                                                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                                    <span class="w-2 h-2 rounded-full" :class="evt.event_type === 'EXCEPTION' ? 'bg-rose-500' : 'bg-blue-500'"></span>
                                                    <span>{{ evt.status }}</span>
                                                    <span v-if="evt.location" class="text-[11px] font-normal text-slate-500">({{ evt.location.code }} - {{ evt.location.city }})</span>
                                                </div>
                                                <p class="text-[11px] text-slate-600">{{ evt.description }}</p>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <span class="text-[10px] font-mono text-slate-400 block">
                                                    {{ new Date(evt.occurred_at).toLocaleDateString() }}
                                                </span>
                                                <span v-if="evt.weather_snapshot" class="text-[10px] font-mono text-amber-600 inline-flex items-center gap-1">
                                                    <i class="bi bi-cloud-sun"></i> {{ evt.weather_snapshot.temperature }}°C
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- =============================================================== -->
                        <!-- TAB 2: CEK RUTE & ESTIMASI TARIF (SHIPPING_TARIFFS TABLE)       -->
                        <!-- =============================================================== -->
                        <div v-if="activeUtilityTab === 'tariff'" class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Pelabuhan Asal (Origin)</label>
                                    <select v-model="calcOriginId" class="w-full h-10 px-3 bg-slate-50 border border-slate-300 rounded-xl">
                                        <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                            {{ loc.code }} - {{ loc.city }}, {{ loc.country }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Pelabuhan Tujuan (Destination)</label>
                                    <select v-model="calcDestId" class="w-full h-10 px-3 bg-slate-50 border border-slate-300 rounded-xl">
                                        <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                            {{ loc.code }} - {{ loc.city }}, {{ loc.country }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Moda Transportasi</label>
                                    <select v-model="calcMode" class="w-full h-10 px-3 bg-slate-50 border border-slate-300 rounded-xl font-semibold">
                                        <option value="SEA">SEA &mdash; Ocean Freight (Kapal Kontainer)</option>
                                        <option value="AIR">AIR &mdash; Air Freight Express (Kargo Udara)</option>
                                        <option value="LAND">LAND &mdash; Inland Intermodal (Truk Darat)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Total Berat Kargo (Kg)</label>
                                    <input
                                        v-model="calcWeight"
                                        type="number"
                                        min="1"
                                        max="500000"
                                        class="w-full h-10 px-3 bg-slate-50 border border-slate-300 rounded-xl font-mono"
                                    />
                                </div>
                            </div>

                            <button
                                @click="runTariffEstimate"
                                :disabled="calcLoading"
                                class="w-full h-11 bg-slate-950 hover:bg-blue-700 text-white text-xs font-bold rounded-xl transition inline-flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <i class="bi bi-lightning-charge-fill"></i>
                                <span>{{ calcLoading ? 'Menghitung...' : 'Hitung Estimasi Tarif & Durasi Transit' }}</span>
                            </button>

                            <div v-if="calcError" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                                {{ calcError }}
                            </div>

                            <Transition
                                enter-active-class="transition duration-300 ease-out"
                                enter-from-class="opacity-0 -translate-y-2"
                                enter-to-class="opacity-100 translate-y-0"
                            >
                                <div v-if="calcResult" class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-3 text-xs">
                                    <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                                        <div>
                                            <span class="text-[10px] font-bold uppercase text-blue-600 block">{{ calcResult.service_name }}</span>
                                            <div class="font-bold text-slate-900">
                                                {{ calcResult.origin.code }} ({{ calcResult.origin.city }}) &rarr; {{ calcResult.destination.code }} ({{ calcResult.destination.city }})
                                            </div>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-lg bg-blue-100 text-blue-800 font-mono font-bold">
                                            ~{{ calcResult.distance_km }} km
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4 py-1">
                                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                                            <span class="text-[10px] text-slate-500 block">ESTIMASI BIAYA PENGIRIMAN</span>
                                            <div class="text-lg font-extrabold font-mono text-slate-950 mt-0.5">
                                                {{ formatIdr(calcResult.total_cost_idr) }}
                                            </div>
                                            <span class="text-[11px] font-mono text-slate-500">
                                                &asymp; ${{ calcResult.total_cost_usd.toFixed(2) }} USD
                                            </span>
                                        </div>
                                        <div class="p-3 rounded-xl bg-white border border-slate-200">
                                            <span class="text-[10px] text-slate-500 block">ESTIMASI DURASI TRANSIT</span>
                                            <div class="text-lg font-extrabold font-mono text-emerald-700 mt-0.5">
                                                {{ calcResult.estimated_transit_days }} Hari
                                            </div>
                                            <span class="text-[11px] font-mono text-slate-500">
                                                &asymp; {{ calcResult.estimated_transit_hours }} Jam (Berat {{ calcResult.effective_weight_kg }} kg)
                                            </span>
                                        </div>
                                    </div>

                                    <div class="text-[10px] text-slate-500 flex flex-wrap justify-between gap-2 pt-1">
                                        <span>Base Fee: ${{ calcResult.breakdown.base_fee_usd }}</span>
                                        <span>Weight Rate: ${{ calcResult.breakdown.weight_cost_usd }}</span>
                                        <span>Distance Rate: ${{ calcResult.breakdown.distance_cost_usd }}</span>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- =============================================================== -->
                        <!-- TAB 3: EKSPLORASI JARINGAN HUB GLOBAL                           -->
                        <!-- =============================================================== -->
                        <div v-if="activeUtilityTab === 'hubs'" id="network" class="space-y-3">
                            <div class="relative">
                                <i class="bi bi-search absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                                <input
                                    v-model="hubQuery"
                                    type="text"
                                    placeholder="Filter pelabuhan, kota, atau kode UN/LOCODE (mis. IDJKT, Rotterdam)..."
                                    class="w-full h-10 pl-9 pr-3 bg-slate-50 border border-slate-300 rounded-xl text-xs"
                                />
                            </div>

                            <div class="max-h-64 overflow-y-auto grid grid-cols-1 sm:grid-cols-2 gap-2.5 pr-1">
                                <div
                                    v-for="hub in filteredHubs"
                                    :key="hub.id"
                                    class="p-3 rounded-xl bg-slate-50 hover:bg-white border border-slate-200 transition flex items-start justify-between gap-2"
                                >
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-mono font-extrabold text-xs text-blue-700">{{ hub.code }}</span>
                                            <span class="text-[9px] px-1.5 py-0.2 rounded bg-slate-200 text-slate-700 font-bold">{{ hub.type }}</span>
                                        </div>
                                        <div class="font-bold text-xs text-slate-900 truncate mt-0.5">{{ hub.name }}</div>
                                        <div class="text-[11px] text-slate-500">{{ hub.city }}, {{ hub.country }}</div>
                                    </div>
                                    <div class="text-right font-mono text-[10px] text-slate-400 shrink-0">
                                        {{ parseFloat(hub.latitude).toFixed(2) }},<br>{{ parseFloat(hub.longitude).toFixed(2) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================================================================= -->
        <!-- SECTION 3: ASYMMETRIC BENTO GRID (CAPABILITIES & SENTRAL FEATURES)      -->
        <!-- ======================================================================= -->
        <section id="bento" class="bg-[#F6F3EC] py-20 px-4 sm:px-8 border-t border-[#E5E0D5]">
            <div class="max-w-7xl mx-auto">
                <div class="max-w-2xl mb-12">
                    <span class="text-[11px] font-mono font-bold uppercase tracking-widest text-blue-700 block mb-2">
                        CARGOTRACE ARCHITECTURE
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1A1A1C]">
                        Built for Supply Chain Resilience.
                    </h2>
                    <p class="text-xs sm:text-sm text-[#706E6B] mt-2 leading-relaxed">
                        Setiap modul dibangun mengikuti standar enterprise monolith Laravel + Vue 3 Inertia dengan aturan state machine ketat dan validasi kronologis.
                    </p>
                </div>

                <!-- 12-Col Bento Grid -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <!-- Bento Box 1 (8 cols): Interactive Live Route Preview -->
                    <div class="md:col-span-8 bg-white/75 backdrop-blur-lg border border-white/80 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-900/5 hover:-translate-y-1.5 hover:shadow-2xl transition-all duration-500 flex flex-col justify-between relative overflow-hidden">
                        <div class="flex items-start justify-between gap-4 mb-6">
                            <div>
                                <span class="px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 border border-blue-200 text-[10px] font-bold uppercase tracking-wider">
                                    Leaflet.js + OpenStreetMap
                                </span>
                                <h3 class="text-xl font-bold text-[#1A1A1C] mt-3">Live Intercontinental Route Visibility</h3>
                                <p class="text-xs text-[#706E6B] mt-1 max-w-lg">
                                    Visualisasi jalur perjalanan kapal dan pesawat secara kronologis menghubungkan Origin, titik checkpoint transit, hingga Destination dengan polyline dinamis.
                                </p>
                            </div>
                            <div class="w-11 h-11 rounded-2xl bg-slate-950 text-white flex items-center justify-center shrink-0">
                                <i class="bi bi-map-fill text-lg"></i>
                            </div>
                        </div>

                        <!-- Visual Route Arc Graphic -->
                        <div class="p-4 sm:p-5 rounded-2xl bg-slate-950 text-white font-mono text-xs relative overflow-hidden">
                            <div class="flex items-center justify-between relative z-10">
                                <div class="text-center">
                                    <div class="w-4 h-4 rounded-full bg-emerald-500 ring-4 ring-emerald-500/30 mx-auto mb-1.5"></div>
                                    <div class="font-bold">IDJKT</div>
                                    <div class="text-[10px] text-slate-400">Jakarta</div>
                                </div>
                                <div class="flex-1 h-0.5 bg-gradient-to-r from-emerald-500 via-blue-500 to-cyan-400 mx-2 relative">
                                    <span class="w-2.5 h-2.5 rounded-full bg-white shadow-[0_0_12px_#fff] absolute -top-1 left-1/3 animate-ping"></span>
                                </div>
                                <div class="text-center">
                                    <div class="w-4 h-4 rounded-full bg-blue-500 ring-4 ring-blue-500/30 mx-auto mb-1.5"></div>
                                    <div class="font-bold">SGSIN</div>
                                    <div class="text-[10px] text-slate-400">Singapore</div>
                                </div>
                                <div class="flex-1 h-0.5 border-b-2 border-dashed border-slate-500 mx-2"></div>
                                <div class="text-center">
                                    <div class="w-4 h-4 rounded-full bg-rose-500 ring-4 ring-rose-500/30 mx-auto mb-1.5"></div>
                                    <div class="font-bold">NLRTM</div>
                                    <div class="text-[10px] text-slate-400">Rotterdam</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bento Box 2 (4 cols): Deterministic ETA & Delay Rule -->
                    <div class="md:col-span-4 bg-white/75 backdrop-blur-lg border border-white/80 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-900/5 hover:-translate-y-1.5 hover:shadow-2xl transition-all duration-500 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-2.5 py-1 rounded-md bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-bold uppercase">
                                    Deterministic Engine
                                </span>
                                <i class="bi bi-clock-history text-amber-600 text-lg"></i>
                            </div>
                            <h3 class="text-lg font-bold text-[#1A1A1C]">ETA & Delay Threshold Rule</h3>
                            <p class="text-xs text-[#706E6B] mt-1">
                                Klasifikasi tingkat keterlambatan otomatis tanpa spekulasi AI:
                            </p>
                        </div>

                        <div class="space-y-2 mt-5 font-mono text-xs">
                            <div class="flex items-center justify-between p-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800">
                                <span class="font-bold">LOW DELAY</span>
                                <span>1 &ndash; 12 Jam</span>
                            </div>
                            <div class="flex items-center justify-between p-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-800">
                                <span class="font-bold">MEDIUM DELAY</span>
                                <span>13 &ndash; 48 Jam</span>
                            </div>
                            <div class="flex items-center justify-between p-2 rounded-xl bg-rose-50 border border-rose-200 text-rose-800">
                                <span class="font-bold">HIGH / CRITICAL</span>
                                <span>&gt; 48 Jam</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bento Box 3 (4 cols): 3 Moda Ekspedisi + Active Carriers -->
                    <div class="md:col-span-4 bg-white/75 backdrop-blur-lg border border-white/80 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-900/5 hover:-translate-y-1.5 hover:shadow-2xl transition-all duration-500">
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 rounded-md bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-bold uppercase">
                                {{ carriers.length }} Active Carriers
                            </span>
                            <i class="bi bi-truck text-slate-800 text-lg"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#1A1A1C]">Ekspedisi Udara, Laut & Darat</h3>
                        <p class="text-xs text-[#706E6B] mt-1 mb-4">
                            Terintegrasi dengan mitra pelayaran internasional dan armada kargo darat:
                        </p>
                        <div class="flex flex-wrap gap-1.5">
                            <span
                                v-for="c in carriers.slice(0, 6)"
                                :key="c.id"
                                class="px-2.5 py-1 rounded-lg bg-[#F6F3EC] border border-[#E5E0D5] text-[11px] font-semibold text-[#1A1A1C]"
                            >
                                {{ c.code }} &bull; {{ c.transport_mode }}
                            </span>
                        </div>
                    </div>

                    <!-- Bento Box 4 (4 cols): Weather & Geocoding Integration -->
                    <div class="md:col-span-4 bg-white/75 backdrop-blur-lg border border-white/80 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-900/5 hover:-translate-y-1.5 hover:shadow-2xl transition-all duration-500 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-2.5 py-1 rounded-md bg-cyan-50 text-cyan-800 border border-cyan-200 text-[10px] font-bold uppercase">
                                    External APIs
                                </span>
                                <i class="bi bi-cloud-sun-fill text-cyan-600 text-lg"></i>
                            </div>
                            <h3 class="text-lg font-bold text-[#1A1A1C]">Cuaca Pelabuhan & Geocoding</h3>
                            <p class="text-xs text-[#706E6B] mt-1">
                                Pengambilan otomatis koordinat Nominatim OSM serta snapshot cuaca maritim Open-Meteo pada setiap checkpoint.
                            </p>
                        </div>

                        <div class="mt-4 p-3 rounded-2xl bg-[#F6F3EC] border border-[#E5E0D5] flex items-center justify-between text-xs">
                            <div>
                                <div class="font-bold text-[#1A1A1C]">Port of Singapore</div>
                                <div class="text-[11px] text-[#706E6B]">Scattered Clouds &bull; 12.8 km/h Wind</div>
                            </div>
                            <span class="text-lg font-black font-mono text-amber-600">29°C</span>
                        </div>
                    </div>

                    <!-- Bento Box 5 (4 cols): Exception Management Workflow -->
                    <div class="md:col-span-4 bg-white/75 backdrop-blur-lg border border-white/80 rounded-3xl p-6 sm:p-8 shadow-xl shadow-slate-900/5 hover:-translate-y-1.5 hover:shadow-2xl transition-all duration-500 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-2.5 py-1 rounded-md bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-bold uppercase">
                                    Incident Governance
                                </span>
                                <i class="bi bi-exclamation-triangle-fill text-rose-600 text-lg"></i>
                            </div>
                            <h3 class="text-lg font-bold text-[#1A1A1C]">Proactive Exception Handling</h3>
                            <p class="text-xs text-[#706E6B] mt-1">
                                Pencatatan kendala Customs Hold, Weather Alert, dan Port Congestion lengkap dengan siklus investigasi.
                            </p>
                        </div>

                        <div class="mt-4 flex items-center gap-1.5 text-[10px] font-mono font-bold">
                            <span class="px-2.5 py-1 rounded-lg bg-rose-100 text-rose-800">OPEN</span>
                            <i class="bi bi-arrow-right text-slate-400"></i>
                            <span class="px-2.5 py-1 rounded-lg bg-amber-100 text-amber-800">INVESTIGATING</span>
                            <i class="bi bi-arrow-right text-slate-400"></i>
                            <span class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800">RESOLVED</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================================================================= -->
        <!-- FOOTER                                                                  -->
        <!-- ======================================================================= -->
        <footer class="bg-slate-950 text-slate-400 py-12 px-4 sm:px-8 border-t border-slate-800 text-xs">
            <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div>
                    <div class="flex items-center gap-2 font-extrabold text-base text-white mb-1.5">
                        <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                        <span>CargoTrace Platform</span>
                    </div>
                    <p class="max-w-md text-[11px] leading-relaxed">
                        Enterprise Global Shipment Visibility & Tracking System. Dibangun dengan Laravel 12, Vue 3, Inertia.js, Tailwind CSS v4, Leaflet.js, dan Chart.js.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-6 text-[11px]">
                    <span class="px-3 py-1 rounded-lg bg-slate-900 border border-slate-800 font-mono text-cyan-400">
                        REST API v1 (Sanctum Protected)
                    </span>
                    <Link :href="authUser ? '/dashboard' : '/login'" class="text-white hover:text-cyan-400 font-semibold transition">
                        {{ authUser ? 'Dashboard Operasional' : 'Portal Login' }} &rarr;
                    </Link>
                </div>
            </div>

            <div class="max-w-7xl mx-auto mt-8 pt-6 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-500">
                <span>&copy; {{ new Date().getFullYear() }} CargoTrace Logistics Engineering.</span>
                <span>Map Tiles &copy; OpenStreetMap & CARTO &bull; Telemetry by Open-Meteo & Nominatim</span>
            </div>
        </footer>
    </div>
</template>
