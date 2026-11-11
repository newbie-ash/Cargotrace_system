<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['scrollToTrack']);

// Animated counters
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

// Subtle Mouse Parallax
const parallaxX = ref(0);
const parallaxY = ref(0);
const handleHeroMouseMove = (e) => {
    const rect = e.currentTarget.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width - 0.5;
    const y = (e.clientY - rect.top) / rect.height - 0.5;
    parallaxX.value = x * 20;
    parallaxY.value = y * 12;
};

onMounted(() => {
    animateCounter(displayShipments, props.stats.total_shipments || 8, 900);
    animateCounter(displayCarriers, props.stats.active_carriers || 7, 900);
    animateCounter(displayLocations, props.stats.total_locations || 8, 900);
});
</script>

<template>
    <section
        @mousemove="handleHeroMouseMove"
        aria-label="Hero Section Delixie"
        class="relative min-h-[92vh] overflow-hidden bg-gradient-to-br from-[#0B1120] via-[#0E172A] to-[#0B162C] text-white pt-28 pb-20 px-4 sm:px-8 flex flex-col justify-center"
    >
        <!-- Atmospheric Glowing Radial Spotlights -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <div class="absolute -top-32 -right-24 w-[560px] h-[560px] rounded-full bg-blue-600/15 blur-[140px]"></div>
            <div class="absolute bottom-10 -left-28 w-[520px] h-[520px] rounded-full bg-amber-500/10 blur-[150px]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[400px] rounded-full bg-blue-500/5 blur-[160px]"></div>
            <!-- Grid texture overlay -->
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff05_1px,transparent_1px),linear-gradient(to_bottom,#ffffff05_1px,transparent_1px)] bg-[size:48px_48px]"></div>
        </div>

        <!-- Giant Low-Opacity Background Typography (Winzy Layered Depth) -->
        <div
            class="absolute inset-0 flex items-center justify-center select-none pointer-events-none transition-transform duration-200 ease-out z-0"
            :style="{ transform: `translate3d(${parallaxX * -0.4}px, ${parallaxY * -0.4}px, 0)` }"
            aria-hidden="true"
        >
            <h2 class="text-[17vw] sm:text-[14vw] font-black tracking-tighter leading-none text-transparent bg-clip-text bg-gradient-to-b from-white/[0.08] via-white/[0.04] to-transparent">
                DELIXIE
            </h2>
        </div>

        <!-- Main Hero Content (Hybrid Winzy + Veluno Asymmetric Split) -->
        <div class="relative z-10 max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
            <!-- Kolom Kiri: Headline & Live Statistics -->
            <div class="lg:col-span-7 space-y-7">
                <!-- Top Badge -->
                <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-slate-800/60 border border-blue-500/30 backdrop-blur-md shadow-lg shadow-blue-500/5 text-xs text-slate-200">
                    <span class="w-2 h-2 rounded-full bg-blue-500 shadow-[0_0_8px_#3b82f6] animate-pulse"></span>
                    <span class="font-mono text-[11px] font-bold uppercase tracking-wider text-blue-300">
                        GLOBAL FREIGHT VISIBILITY
                    </span>
                    <span class="text-slate-500">&bull;</span>
                    <span class="text-slate-300 text-[11px]">MULTIMODAL 2026</span>
                </div>

                <!-- Headline -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.1] text-white">
                    End-to-End
                    <span class="text-amber-400 drop-shadow-[0_0_25px_rgba(245,158,11,0.45)]">Global</span>
                    Shipment Visibility.
                </h1>

                <!-- Subheadline -->
                <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl">
                    Pantau pergerakan kontainer interkontinental secara deterministik dengan validasi rute geospasial, kalkulasi tarif transparan, dan peringatan keterlambatan real-time.
                </p>

                <!-- Statistics Row & Action Button -->
                <div class="pt-4 flex flex-col sm:flex-row sm:items-center justify-between gap-6 border-t border-white/10">
                    <!-- Live Statistics -->
                    <div class="grid grid-cols-3 gap-6 sm:gap-8">
                        <div>
                            <div class="text-3xl sm:text-4xl font-black font-mono text-white">
                                {{ displayShipments }}<span class="text-blue-500">+</span>
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

                    <!-- CTA Action Button -->
                    <button
                        type="button"
                        @click="emit('scrollToTrack')"
                        class="group self-start sm:self-center pl-6 pr-2.5 py-2.5 rounded-full bg-white hover:bg-blue-50 text-slate-950 font-bold text-sm inline-flex items-center gap-3 transition-all duration-300 shadow-xl shadow-white/10 hover:-translate-y-1 cursor-pointer"
                    >
                        <span>Lacak Kargo</span>
                        <span class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center group-hover:rotate-45 transition-transform duration-300">
                            <i class="bi bi-arrow-up-right text-xs" aria-hidden="true"></i>
                        </span>
                    </button>
                </div>
            </div>

            <!-- Kolom Kanan: Container Visual Asimetris Veluno + Rotating Stamp -->
            <div class="lg:col-span-5 relative">
                <!-- Rotating Circular Stamp Overlay (Veluno Aesthetic) -->
                <div class="absolute -top-7 -right-4 sm:-right-6 z-30 pointer-events-auto">
                    <button
                        type="button"
                        @click="emit('scrollToTrack')"
                        title="Klik untuk lacak kargo instan"
                        class="group relative w-28 h-28 sm:w-32 sm:h-32 rounded-full bg-slate-900/90 backdrop-blur-xl border border-white/20 shadow-[0_10px_35px_rgba(0,0,0,0.8)] flex items-center justify-center hover:scale-105 transition-transform duration-300 cursor-pointer"
                    >
                        <svg viewBox="0 0 140 140" class="w-full h-full animate-[spin_14s_linear_infinite]">
                            <defs>
                                <path id="delixieStampPath" d="M 70, 70 m -50, 0 a 50,50 0 1,1 100,0 a 50,50 0 1,1 -100,0" />
                            </defs>
                            <text class="fill-slate-300 text-[9.5px] font-mono font-bold uppercase tracking-[0.22em]">
                                <textPath href="#delixieStampPath" startOffset="0%">
                                    DELIXIE FREIGHT TELEMETRY &bull; LIVE RADAR &bull;
                                </textPath>
                            </text>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center shadow-lg group-hover:bg-amber-500 transition-colors">
                                <i class="bi bi-arrow-up-right text-sm" aria-hidden="true"></i>
                            </span>
                        </div>
                    </button>
                </div>

                <!-- Asymmetric Vessel & Radar Container (Veluno Cutout Angles) -->
                <div class="rounded-3xl rounded-tr-[5.5rem] rounded-bl-[4.5rem] bg-slate-800/40 backdrop-blur-xl border border-white/10 p-6 sm:p-7 shadow-2xl relative overflow-hidden group hover:border-blue-500/40 hover:shadow-[0_0_30px_rgba(37,99,235,0.25)] transition-all duration-300">
                    <!-- Top Telemetry Status -->
                    <div class="flex items-center justify-between pb-4 border-b border-white/10">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-[0_0_8px_#34d399] animate-pulse"></span>
                            <span class="font-mono text-xs font-bold text-white tracking-wider">VESSEL RADAR LIVE</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-blue-500/15 text-blue-300 border border-blue-500/30 font-mono text-[10px] font-bold">
                            21.4 KTS &bull; OPTIMAL
                        </span>
                    </div>

                    <!-- Bespoke Vector SVG Cargo Vessel & Telemetry Radar Graphic -->
                    <div class="py-6 flex items-center justify-center">
                        <svg viewBox="0 0 400 220" class="w-full h-auto select-none" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="vesselHullGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#1E293B" />
                                    <stop offset="50%" stop-color="#2563EB" />
                                    <stop offset="100%" stop-color="#0284C7" />
                                </linearGradient>
                                <radialGradient id="radarSweepGrad" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#38BDF8" stop-opacity="0.3" />
                                    <stop offset="100%" stop-color="#38BDF8" stop-opacity="0" />
                                </radialGradient>
                            </defs>

                            <!-- Ocean Waves Topology Lines -->
                            <path d="M 0,185 Q 100,175 200,185 T 400,185" fill="none" stroke="rgba(56,189,248,0.25)" stroke-width="1.5" />
                            <path d="M 0,200 Q 100,195 200,200 T 400,200" fill="none" stroke="rgba(37,99,235,0.3)" stroke-width="2" />

                            <!-- Radar Grid Concentric Circles -->
                            <circle cx="200" cy="110" r="85" fill="none" stroke="rgba(255,255,255,0.06)" stroke-dasharray="3 4" />
                            <circle cx="200" cy="110" r="55" fill="none" stroke="rgba(255,255,255,0.08)" stroke-dasharray="2 3" />
                            <circle cx="200" cy="110" r="25" fill="url(#radarSweepGrad)" />

                            <!-- Cargo Container Stack (Color coded) -->
                            <!-- Row 1 (Bottom) -->
                            <rect x="135" y="115" width="22" height="13" rx="2" fill="#2563EB" stroke="#1E40AF" stroke-width="1" />
                            <rect x="160" y="115" width="22" height="13" rx="2" fill="#F59E0B" stroke="#D97706" stroke-width="1" />
                            <rect x="185" y="115" width="22" height="13" rx="2" fill="#10B981" stroke="#059669" stroke-width="1" />
                            <rect x="210" y="115" width="22" height="13" rx="2" fill="#2563EB" stroke="#1E40AF" stroke-width="1" />
                            <rect x="235" y="115" width="22" height="13" rx="2" fill="#F59E0B" stroke="#D97706" stroke-width="1" />
                            <!-- Row 2 (Middle) -->
                            <rect x="146" y="100" width="22" height="13" rx="2" fill="#10B981" stroke="#059669" stroke-width="1" />
                            <rect x="171" y="100" width="22" height="13" rx="2" fill="#2563EB" stroke="#1E40AF" stroke-width="1" />
                            <rect x="196" y="100" width="22" height="13" rx="2" fill="#F59E0B" stroke="#D97706" stroke-width="1" />
                            <rect x="221" y="100" width="22" height="13" rx="2" fill="#10B981" stroke="#059669" stroke-width="1" />
                            <!-- Row 3 (Top) -->
                            <rect x="160" y="85" width="22" height="13" rx="2" fill="#2563EB" stroke="#1E40AF" stroke-width="1" />
                            <rect x="185" y="85" width="22" height="13" rx="2" fill="#F59E0B" stroke="#D97706" stroke-width="1" />

                            <!-- Ship Bridge / Superstructure -->
                            <polygon points="262,75 290,75 285,130 262,130" fill="#334155" stroke="#475569" stroke-width="1" />
                            <rect x="268" y="82" width="16" height="5" rx="1" fill="#38BDF8" />
                            <!-- Radar mast -->
                            <line x1="276" y1="75" x2="276" y2="60" stroke="#94A3B8" stroke-width="2" />
                            <line x1="270" y1="65" x2="282" y2="65" stroke="#94A3B8" stroke-width="1.5" />
                            <circle cx="276" cy="58" r="2.5" fill="#38BDF8" />

                            <!-- Ship Hull Profile -->
                            <path d="M 85,130 L 110,170 L 305,170 L 320,130 Z" fill="url(#vesselHullGrad)" stroke="#60A5FA" stroke-width="1.5" />
                            <!-- Bow bulb effect -->
                            <circle cx="106" cy="168" r="4" fill="#38BDF8" />
                            <line x1="85" y1="130" x2="320" y2="130" stroke="#93C5FD" stroke-width="2" />

                            <!-- Active Telemetry Waypoint Nodes -->
                            <circle cx="95" cy="50" r="4" fill="#10B981" />
                            <text x="105" y="54" fill="#34D399" font-size="10" font-family="monospace" font-weight="bold">IDJKT (Port)</text>

                            <path d="M 140,50 Q 200,30 260,50" fill="none" stroke="#38BDF8" stroke-width="1.8" stroke-dasharray="4 4" />

                            <circle cx="270" cy="50" r="4" fill="#F59E0B" />
                            <text x="280" y="54" fill="#FBBF24" font-size="10" font-family="monospace" font-weight="bold">NLRTM (Hub)</text>
                        </svg>
                    </div>

                    <!-- Bottom Route Telemetry Info -->
                    <div class="pt-4 border-t border-white/10 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-[10px] uppercase font-mono text-slate-400 block">JALUR EKSPEDISI</span>
                            <strong class="font-mono text-white text-sm">IDJKT &rarr; NLRTM</strong>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] uppercase font-mono text-slate-400 block">ESTIMASI KEDATANGAN</span>
                            <span class="font-mono font-bold text-amber-400">On Schedule &bull; -2.4 Hari</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
