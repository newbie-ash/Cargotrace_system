<script setup>
import { defineAsyncComponent } from 'vue';

const HeroGlobe3D = defineAsyncComponent(() =>
    import('@/Components/Landing/HeroGlobe3D.vue')
);

defineProps({
    locations: {
        type: Array,
        default: () => [],
    },
});

defineEmits(['scrollToTrack']);
</script>

<template>
    <section
        aria-label="Hero Section Delixie"
        class="relative min-h-[88vh] lg:min-h-[92vh] overflow-hidden bg-transparent text-[#FAEFDB] pt-32 pb-20 sm:pb-24 px-6 sm:px-12 lg:px-16 flex flex-col justify-center"
    >
        <!-- Background Layer: Ambient Lights, Geospatial Grid & Subtle Cargo Silhouettes -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none" aria-hidden="true">
            <!-- 1. Ambient Deep Teal & Magenta Backlight Glows -->
            <div class="absolute -top-32 -right-24 w-[720px] h-[720px] rounded-full bg-gradient-to-br from-[#02332A] via-[#0A3D32]/50 to-[#BE2388]/18 blur-[110px]"></div>
            <div class="absolute top-1/4 right-1/4 w-[480px] h-[480px] rounded-full bg-[#BE2388]/15 blur-[120px]"></div>
            <div class="absolute -bottom-36 -left-28 w-[580px] h-[580px] rounded-full bg-[#001F18]/80 blur-[120px]"></div>

            <!-- 2. High-Precision Geospatial SVG Logistics Grid & Animated Route Arcs -->
            <svg
                class="absolute inset-0 w-full h-full opacity-60"
                xmlns="http://www.w3.org/2000/svg"
                preserveAspectRatio="xMidYMid slice"
                viewBox="0 0 1440 900"
            >
                <defs>
                    <pattern id="hero-geo-grid" width="80" height="80" patternUnits="userSpaceOnUse">
                        <path d="M 80 0 L 0 0 0 80" fill="none" stroke="rgba(250, 239, 219, 0.035)" stroke-width="1" />
                        <circle cx="80" cy="80" r="1" fill="rgba(250, 239, 219, 0.12)" />
                    </pattern>

                    <linearGradient id="route-asia-eu" x1="0%" y1="100%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#BE2388" stop-opacity="0.2" />
                        <stop offset="40%" stop-color="#D42B8A" stop-opacity="0.85" />
                        <stop offset="75%" stop-color="#FAEFDB" stop-opacity="0.75" />
                        <stop offset="100%" stop-color="#BE2388" stop-opacity="0.25" />
                    </linearGradient>

                    <linearGradient id="route-transpacific" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#FAEFDB" stop-opacity="0.12" />
                        <stop offset="50%" stop-color="#BE2388" stop-opacity="0.7" />
                        <stop offset="100%" stop-color="#D42B8A" stop-opacity="0.2" />
                    </linearGradient>

                    <linearGradient id="route-intermodal-land" x1="100%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#FAEFDB" stop-opacity="0.6" />
                        <stop offset="100%" stop-color="#BE2388" stop-opacity="0.15" />
                    </linearGradient>
                </defs>

                <!-- Full Area Geodetic Grid -->
                <rect width="1440" height="900" fill="url(#hero-geo-grid)" />

                <!-- Dynamic Curving Logistics Trajectories -->
                <g fill="none">
                    <path
                        d="M 60 740 C 340 660, 520 460, 840 470 C 1120 480, 1260 300, 1420 220"
                        stroke="url(#route-asia-eu)"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        class="hero-route-line-1"
                    />
                    <path
                        d="M 960 780 C 1040 610, 1180 500, 1420 480"
                        stroke="url(#route-transpacific)"
                        stroke-width="2"
                        stroke-linecap="round"
                        class="hero-route-line-2"
                    />
                    <path
                        d="M 220 860 C 440 820, 640 760, 820 620"
                        stroke="url(#route-intermodal-land)"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        class="hero-route-line-3"
                    />
                </g>
            </svg>

            <!-- 3. SUBTLE CARGO TRANSPORT SILHOUETTES (Plane, Container Ship, Freight Truck) -->
            <!-- Opacity: 0.07, soft blend, logistics identity without typographic interference -->
            <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.07] [mask-image:radial-gradient(ellipse_at_center,black_40%,transparent_80%)]">
                <svg
                    class="w-full h-full object-cover"
                    viewBox="0 0 1600 900"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <!-- Air Cargo Jet Silhouette (High Altitude Sky Corridor) -->
                    <g transform="translate(780, 160) scale(1.1) rotate(-8)">
                        <!-- Fuselage -->
                        <path
                            d="M 20 65 Q 120 52, 280 54 Q 380 56, 460 72 Q 490 80, 500 88 Q 480 94, 420 90 Q 280 88, 140 82 Q 60 80, 20 65 Z"
                            fill="#FAEFDB"
                        />
                        <!-- Swept Main Wing -->
                        <path d="M 190 70 L 110 -10 L 155 -10 L 295 72 Z" fill="#FAEFDB" />
                        <!-- Winglet / Far Wing Tip -->
                        <path d="M 240 85 L 340 165 L 315 165 L 210 82 Z" fill="#FAEFDB" />
                        <!-- T-Tail & Vertical Stabilizer -->
                        <path d="M 40 68 L 0 -5 L 35 -5 L 95 65 Z" fill="#FAEFDB" />
                        <!-- Turbofan Engines -->
                        <ellipse cx="205" cy="50" rx="22" ry="8" fill="#FAEFDB" />
                        <ellipse cx="255" cy="115" rx="22" ry="8" fill="#FAEFDB" />
                    </g>

                    <!-- Maritime Container Ship Silhouette (Mid/Lower Ocean Horizon) -->
                    <g transform="translate(180, 560) scale(0.95)">
                        <!-- Ship Hull with Bow & Keel Lines -->
                        <path
                            d="M 60 120 L 780 120 C 820 120, 850 140, 870 170 L 40 170 C 32 155, 36 135, 60 120 Z"
                            fill="#FAEFDB"
                        />
                        <!-- Stern Bridge / Deckhouse -->
                        <rect x="90" y="45" width="75" height="75" rx="3" fill="#FAEFDB" />
                        <rect x="115" y="20" width="18" height="25" rx="2" fill="#FAEFDB" />
                        <rect x="80" y="40" width="95" height="8" rx="2" fill="#FAEFDB" />
                        <!-- Container Stacks (Bays) -->
                        <rect x="190" y="55" width="85" height="65" rx="2" fill="#FAEFDB" />
                        <rect x="290" y="55" width="85" height="65" rx="2" fill="#FAEFDB" />
                        <rect x="390" y="55" width="85" height="65" rx="2" fill="#FAEFDB" />
                        <rect x="490" y="55" width="85" height="65" rx="2" fill="#FAEFDB" />
                        <rect x="590" y="65" width="85" height="55" rx="2" fill="#FAEFDB" />
                        <rect x="690" y="80" width="75" height="40" rx="2" fill="#FAEFDB" />
                        <!-- Bow Forecastle Deck -->
                        <polygon points="775,120 840,120 865,160 775,160" fill="#FAEFDB" />
                    </g>

                    <!-- Freight Logistics Semi-Truck Silhouette (Lower Ground Highway Corridor) -->
                    <g transform="translate(980, 680) scale(0.85)">
                        <!-- 40ft Container Box Trailer -->
                        <rect x="20" y="20" width="340" height="130" rx="4" fill="#FAEFDB" />
                        <!-- Cab / Tractor Unit -->
                        <path
                            d="M 370 40 L 450 35 L 490 90 L 515 110 L 515 150 L 370 150 Z"
                            fill="#FAEFDB"
                        />
                        <!-- Aero Roof Deflector -->
                        <polygon points="370,40 450,35 440,20 370,25" fill="#FAEFDB" />
                        <!-- Wheels -->
                        <circle cx="80" cy="165" r="18" fill="#FAEFDB" />
                        <circle cx="130" cy="165" r="18" fill="#FAEFDB" />
                        <circle cx="310" cy="165" r="18" fill="#FAEFDB" />
                        <circle cx="360" cy="165" r="18" fill="#FAEFDB" />
                        <circle cx="475" cy="165" r="18" fill="#FAEFDB" />
                    </g>
                </svg>
            </div>
        </div>

        <!-- Main Content Grid: Headline on Left, 3D Globe on Right -->
        <div class="max-w-6xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-14 items-center relative z-10">
            <!-- Kolom Kiri: Editorial Typography & High-Impact CTA -->
            <div class="lg:col-span-7 flex flex-col justify-center space-y-7">
                <!-- Status Kicker Pill -->
                <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-[#001F18]/85 border border-[#FAEFDB]/18 text-xs font-mono font-bold text-[#FAEFDB] w-fit shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-[#BE2388] animate-pulse"></span>
                    <span>VISIBILITAS LOGISTIK GLOBAL</span>
                </div>

                <!-- Massive Editorial Headline -->
                <h1 class="text-4xl sm:text-6xl lg:text-[66px] font-extrabold tracking-tight leading-[1.08] text-[#FAEFDB] drop-shadow-md">
                    End-to-End
                    <span class="block text-transparent bg-clip-text bg-gradient-to-r from-[#FAEFDB] via-[#F0D5E7] to-[#BE2388]">
                        Global Shipment Visibility.
                    </span>
                </h1>

                <!-- Sub-Headline -->
                <p class="text-base sm:text-lg text-[#FAEFDB]/85 font-normal leading-relaxed max-w-xl">
                    Pantau pergerakan kontainer interkontinental secara terverifikasi dengan validasi rute geospasial maritim, kalkulasi tarif transparan, dan peringatan keterlambatan real-time.
                </p>

                <!-- Action Button Group -->
                <div class="pt-2 flex flex-wrap items-center gap-4">
                    <button
                        type="button"
                        @click="$emit('scrollToTrack')"
                        class="px-8 py-4 rounded-full bg-gradient-to-r from-[#BE2388] via-[#C82A8F] to-[#D42B8A] hover:brightness-110 text-white font-extrabold text-sm sm:text-base transition-all duration-300 shadow-[0_12px_32px_rgba(190,35,136,0.40)] hover:-translate-y-0.5 flex items-center gap-2.5 group cursor-pointer"
                    >
                        <span>Lacak Kargo Sekarang</span>
                        <i class="bi bi-arrow-right text-xs transition-transform duration-200 group-hover:translate-x-1" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            <!-- Kolom Kanan: Interactive 3D Globe & Minimalist Telemetry Overlay -->
            <div class="lg:col-span-5 relative flex items-center justify-center min-h-[380px] sm:min-h-[420px]">
                <div class="relative w-full max-w-md">
                    <!-- 3D Globe -->
                    <div class="relative flex items-center justify-center opacity-95 hover:opacity-100 transition-opacity">
                        <HeroGlobe3D :locations="locations" />
                    </div>

                    <!-- Floating Frosted Glass Telemetry Card -->
                    <div class="relative z-20 mt-4 sm:mt-0 sm:absolute -bottom-2 right-0 sm:right-2 backdrop-blur-2xl bg-[#012B23]/85 border border-[#FAEFDB]/20 rounded-2xl p-4 sm:p-5 shadow-[0_18px_40px_rgba(0,0,0,0.50)] max-w-xs mx-auto transition-transform duration-300 hover:-translate-y-1">
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
