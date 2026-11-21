<script setup>
import { ref, onMounted, defineAsyncComponent } from 'vue';

const HeroGlobe3D = defineAsyncComponent(() =>
    import('@/Components/Landing/HeroGlobe3D.vue')
);

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    locations: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['scrollToTrack']);

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

onMounted(() => {
    animateCounter(displayShipments, props.stats.total_shipments || 8, 900);
    animateCounter(displayCarriers, props.stats.active_carriers || 7, 900);
    animateCounter(displayLocations, props.stats.total_locations || 8, 900);
});
</script>

<template>
    <section
        aria-label="Hero Section Delixie"
        class="relative min-h-[92vh] overflow-hidden bg-transparent text-[#FAEFDB] pt-32 pb-28 px-6 sm:px-12 lg:px-16 flex flex-col justify-center"
    >
        <!-- Ultra-Crisp Vector & Procedural Logistics Background (Deep Teal + Bone + Magenta Palette) -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none" aria-hidden="true">
            <!-- 1. Ambient Deep Teal & Magenta Backlight Glows -->
            <div class="absolute -top-32 -right-24 w-[720px] h-[720px] rounded-full bg-gradient-to-br from-[#02332A] via-[#0A3D32]/60 to-[#BE2388]/20 blur-[100px]"></div>
            <div class="absolute top-1/4 right-1/4 w-[480px] h-[480px] rounded-full bg-[#BE2388]/18 blur-[120px]"></div>
            <div class="absolute -bottom-36 -left-28 w-[580px] h-[580px] rounded-full bg-[#001F18]/80 blur-[120px]"></div>

            <!-- 2. High-Precision Geospatial SVG Logistics Grid & Animated Magenta Route Arcs -->
            <svg
                class="absolute inset-0 w-full h-full opacity-75"
                xmlns="http://www.w3.org/2000/svg"
                preserveAspectRatio="xMidYMid slice"
                viewBox="0 0 1440 900"
            >
                <defs>
                    <!-- Coordinate Grid Pattern -->
                    <pattern id="hero-geo-grid" width="80" height="80" patternUnits="userSpaceOnUse">
                        <path d="M 80 0 L 0 0 0 80" fill="none" stroke="rgba(250, 239, 219, 0.04)" stroke-width="1" />
                        <circle cx="80" cy="80" r="1.2" fill="rgba(250, 239, 219, 0.14)" />
                    </pattern>

                    <!-- Route Gradient Arcs (Magenta & Bone) -->
                    <linearGradient id="route-asia-eu" x1="0%" y1="100%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#BE2388" stop-opacity="0.25" />
                        <stop offset="40%" stop-color="#D42B8A" stop-opacity="0.95" />
                        <stop offset="75%" stop-color="#FAEFDB" stop-opacity="0.85" />
                        <stop offset="100%" stop-color="#BE2388" stop-opacity="0.35" />
                    </linearGradient>

                    <linearGradient id="route-transpacific" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#FAEFDB" stop-opacity="0.15" />
                        <stop offset="50%" stop-color="#BE2388" stop-opacity="0.75" />
                        <stop offset="100%" stop-color="#D42B8A" stop-opacity="0.25" />
                    </linearGradient>

                    <linearGradient id="route-intermodal-land" x1="100%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#FAEFDB" stop-opacity="0.65" />
                        <stop offset="100%" stop-color="#BE2388" stop-opacity="0.20" />
                    </linearGradient>

                    <!-- Route Glow Filter -->
                    <filter id="route-glow" x="-20%" y="-20%" width="140%" height="140%">
                        <feGaussianBlur stdDeviation="3" result="blur" />
                        <feMerge>
                            <feMergeNode in="blur" />
                            <feMergeNode in="SourceGraphic" />
                        </feMerge>
                    </filter>
                </defs>

                <!-- Full Area Geodetic Grid -->
                <rect width="100%" height="100%" fill="url(#hero-geo-grid)" />

                <!-- Global Latitude / Longitude Curvature Guides -->
                <path d="M-100,280 C400,220 900,340 1600,240" fill="none" stroke="rgba(250, 239, 219, 0.06)" stroke-width="1.2" stroke-dasharray="6 6" />
                <path d="M-100,520 C500,480 1000,600 1600,490" fill="none" stroke="rgba(250, 239, 219, 0.06)" stroke-width="1.2" stroke-dasharray="6 6" />
                <path d="M-100,760 C300,710 850,820 1600,730" fill="none" stroke="rgba(250, 239, 219, 0.05)" stroke-width="1" />

                <!-- Trade Lane 1: Asia - Middle East - Europe Maritime Supercorridor (Animated Magenta Arc) -->
                <path
                    d="M 660,700 C 820,630 960,470 1200,310"
                    fill="none"
                    stroke="url(#route-asia-eu)"
                    stroke-width="2.5"
                    filter="url(#route-glow)"
                    class="hero-route-line-1"
                />
                <!-- Trade Lane 2: Intra-Asia Aerial Express Corridor (Animated) -->
                <path
                    d="M 740,800 C 940,710 1100,640 1340,530"
                    fill="none"
                    stroke="url(#route-transpacific)"
                    stroke-width="2"
                    stroke-dasharray="8 8"
                    class="hero-route-line-2"
                />
                <!-- Trade Lane 3: Trans-Continental Feeder Arc (Animated) -->
                <path
                    d="M 960,250 C 1120,210 1260,270 1420,370"
                    fill="none"
                    stroke="url(#route-intermodal-land)"
                    stroke-width="1.8"
                    stroke-dasharray="5 7"
                    class="hero-route-line-3"
                />

                <!-- Hub Blips & Radar Rings with Real Port Coordinates -->
                <!-- Hub 1: Jakarta Tanjung Priok (IDJKT) -->
                <g transform="translate(710, 680)">
                    <circle r="14" fill="none" stroke="#BE2388" stroke-width="1" class="animate-ping opacity-35" />
                    <circle r="4" fill="#BE2388" />
                    <text x="12" y="-6" fill="#FAEFDB" font-size="9" font-family="monospace" font-weight="bold" letter-spacing="1">IDJKT [06°S 106°E]</text>
                </g>

                <!-- Hub 2: Singapore Malacca Strait (SGSIN) -->
                <g transform="translate(850, 570)">
                    <circle r="12" fill="none" stroke="#D42B8A" stroke-width="1" class="animate-ping opacity-30" style="animation-delay: 0.6s;" />
                    <circle r="3.5" fill="#D42B8A" />
                    <text x="12" y="-6" fill="#FAEFDB" font-size="9" font-family="monospace" letter-spacing="1">SGSIN HUB</text>
                </g>

                <!-- Hub 3: Dubai Port (AEDXB) -->
                <g transform="translate(1040, 420)">
                    <circle r="12" fill="none" stroke="#BE2388" stroke-width="1" class="animate-ping opacity-30" style="animation-delay: 1.2s;" />
                    <circle r="3.5" fill="#BE2388" />
                    <text x="12" y="-6" fill="#FAEFDB" font-size="9" font-family="monospace" letter-spacing="1">AEDXB TRANSIT</text>
                </g>

                <!-- Hub 4: Rotterdam Gateway (NLRTM) -->
                <g transform="translate(1220, 300)">
                    <circle r="16" fill="none" stroke="#FAEFDB" stroke-width="1" class="animate-ping opacity-40" style="animation-delay: 1.8s;" />
                    <circle r="4.5" fill="#FAEFDB" />
                    <text x="12" y="-8" fill="#FAEFDB" font-size="9" font-family="monospace" font-weight="bold" letter-spacing="1">NLRTM [51°N 004°E]</text>
                </g>

                <!-- Geospatial Matrix Target Crosshairs -->
                <g transform="translate(1140, 170)" stroke="rgba(190, 35, 136, 0.35)" stroke-width="1">
                    <line x1="-12" y1="0" x2="12" y2="0" />
                    <line x1="0" y1="-12" x2="0" y2="12" />
                    <circle r="6" fill="none" />
                    <text x="16" y="4" fill="rgba(250, 239, 219, 0.50)" font-size="8" font-family="monospace">RADAR-SYNC 21.4K</text>
                </g>
            </svg>

            <!-- 3. Left Gradient Vignette (Ensures 100% crisp readability for headline & CTA) -->
            <div class="absolute inset-0 bg-gradient-to-r from-[#012B23] via-[#012B23]/85 to-transparent w-full lg:w-3/5"></div>
            <!-- Bottom Seamless Vignette into Utility Hub -->
            <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-[#012B23] to-transparent"></div>
        </div>

        <!-- Main Hero Grid: Split Asymmetrical Editorial Layout -->
        <div class="relative z-10 max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
            <!-- Kolom Kiri: Headline, Narrative & Metrics -->
            <div class="lg:col-span-7 flex flex-col justify-between space-y-8">
                <!-- Eyebrow Category -->
                <div class="flex items-center gap-3">
                    <span class="w-8 h-px bg-[#BE2388]" aria-hidden="true"></span>
                    <span class="text-xs font-mono font-bold tracking-widest text-[#FAEFDB]/80 uppercase drop-shadow-xs">
                        VISIBILITAS LOGISTIK GLOBAL
                    </span>
                </div>

                <!-- Massive Editorial Headline -->
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.08] text-[#FAEFDB] drop-shadow-md">
                    End-to-End
                    <span class="block">Global Shipment</span>
                    Visibility.
                </h1>

                <!-- Concise Lightweight Narrative -->
                <p class="text-base sm:text-lg text-[#FAEFDB]/85 font-normal leading-relaxed max-w-xl drop-shadow-xs">
                    Pantau pergerakan kontainer interkontinental secara terverifikasi dengan validasi rute geospasial maritim, kalkulasi tarif transparan, dan peringatan keterlambatan real-time.
                </p>

                <!-- Solid Pill Primary CTA: Magenta Glow Button -->
                <div class="pt-2 flex flex-wrap items-center gap-4">
                    <button
                        type="button"
                        @click="emit('scrollToTrack')"
                        class="px-8 py-3.5 rounded-full bg-gradient-to-r from-[#BE2388] via-[#C82A8F] to-[#D42B8A] hover:brightness-110 text-white font-bold text-sm transition-all duration-300 shadow-xl shadow-[#BE2388]/35 hover:-translate-y-0.5 inline-flex items-center gap-3 cursor-pointer group"
                    >
                        <span>Lacak Kargo Sekarang</span>
                        <i class="bi bi-arrow-right text-xs transition-transform duration-200 group-hover:translate-x-1" aria-hidden="true"></i>
                    </button>
                </div>

                <!-- Bottom Left: 3 Real Statistics Separated by Vertical Dividers -->
                <div class="pt-8 mt-4 border-t border-[#FAEFDB]/20 grid grid-cols-3 gap-6 max-w-lg">
                    <div class="pr-4 border-r border-[#FAEFDB]/20">
                        <div class="text-3xl sm:text-4xl font-extrabold font-mono text-[#FAEFDB] tracking-tight drop-shadow-xs">
                            {{ displayShipments }}<span class="text-[#BE2388]">+</span>
                        </div>
                        <div class="text-[11px] font-semibold text-[#FAEFDB]/70 mt-1 font-mono">
                            Pengiriman Aktif
                        </div>
                    </div>
                    <div class="pr-4 border-r border-[#FAEFDB]/20">
                        <div class="text-3xl sm:text-4xl font-extrabold font-mono text-[#FAEFDB] tracking-tight drop-shadow-xs">
                            {{ displayCarriers }}
                        </div>
                        <div class="text-[11px] font-semibold text-[#FAEFDB]/70 mt-1 font-mono">
                            Mitra Ekspedisi
                        </div>
                    </div>
                    <div>
                        <div class="text-3xl sm:text-4xl font-extrabold font-mono text-[#FAEFDB] tracking-tight drop-shadow-xs">
                            {{ displayLocations }}
                        </div>
                        <div class="text-[11px] font-semibold text-[#FAEFDB]/70 mt-1 font-mono">
                            Pelabuhan &amp; Hub
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Interactive 3D Globe & Telemetry Overlay -->
            <div class="lg:col-span-5 relative flex items-center justify-center min-h-[400px]">
                <div class="relative w-full max-w-md">
                    <!-- 3D Globe positioned alongside vector route grid -->
                    <div class="relative flex items-center justify-center opacity-95 hover:opacity-100 transition-opacity">
                        <HeroGlobe3D :locations="locations" />
                    </div>

                    <!-- Floating Frosted Glass Telemetry Overlay Card (Deep Teal & Bone with Magenta Badge) -->
                    <div class="relative z-20 mt-4 sm:mt-0 sm:absolute -bottom-4 right-0 sm:right-2 backdrop-blur-2xl bg-[#012B23]/80 border border-[#FAEFDB]/25 rounded-2xl p-4 sm:p-5 shadow-[0_18px_40px_rgba(0,0,0,0.50)] max-w-xs mx-auto transition-transform duration-300 hover:-translate-y-1">
                        <div class="flex items-center justify-between gap-3 pb-2.5 border-b border-[#FAEFDB]/15">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-[0_0_8px_#34D399]"></span>
                                <span class="text-xs font-mono font-bold text-[#FAEFDB] uppercase tracking-wider">Navigasi Aktif</span>
                            </div>
                            <span class="text-[10px] font-mono font-bold px-2.5 py-0.5 rounded-full bg-gradient-to-r from-[#BE2388] to-[#D42B8A] text-white shadow-xs">
                                21.4 KTS
                            </span>
                        </div>
                        <div class="mt-2.5 flex items-center justify-between text-xs">
                            <div>
                                <span class="text-[10px] text-[#FAEFDB]/65 font-mono font-semibold block">KORIDOR MARITIM</span>
                                <strong class="font-mono text-[#FAEFDB] text-xs">IDJKT &rarr; NLRTM</strong>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-[#FAEFDB]/65 font-mono font-semibold block">STATUS</span>
                                <span class="text-xs font-mono font-bold text-emerald-400">On Schedule</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes heroRouteFlow {
    from {
        stroke-dashoffset: 160;
    }
    to {
        stroke-dashoffset: 0;
    }
}
.hero-route-line-1 {
    stroke-dasharray: 12 8;
    animation: heroRouteFlow 14s linear infinite;
}
.hero-route-line-2 {
    stroke-dasharray: 8 6;
    animation: heroRouteFlow 10s linear infinite reverse;
}
.hero-route-line-3 {
    stroke-dasharray: 6 10;
    animation: heroRouteFlow 16s linear infinite;
}
</style>
