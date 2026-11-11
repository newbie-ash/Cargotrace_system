<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
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
        class="relative min-h-[90vh] overflow-hidden bg-transparent text-[#13293D] pt-32 pb-28 px-6 sm:px-12 lg:px-16 flex flex-col justify-center"
    >
        <!-- Ambient Organic Gradient Wave Accent (Matches Color Palette Reference) -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <div class="hidden sm:block absolute -top-28 -right-28 w-[640px] h-[640px] rounded-full bg-gradient-to-br from-[#13293D] via-[#265D7A] to-[#67C7C6] opacity-80 blur-[64px]"></div>
            <div class="hidden sm:block absolute top-20 right-16 w-[420px] h-[420px] rounded-full bg-[#A7E4DB]/35 blur-[90px]"></div>
            <div class="absolute -bottom-24 -left-24 w-[480px] h-[480px] rounded-full bg-[#67C7C6]/20 blur-[130px]"></div>
        </div>

        <!-- Main Hero Grid: Split Asymmetrical Editorial Layout -->
        <div class="relative z-10 max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
            <!-- Kolom Kiri: Headline, Narrative & Metrics -->
            <div class="lg:col-span-7 flex flex-col justify-between space-y-8">
                <!-- Eyebrow Category -->
                <div class="flex items-center gap-3">
                    <span class="w-8 h-px bg-[#265D7A]" aria-hidden="true"></span>
                    <span class="text-xs font-mono font-bold tracking-widest text-[#265D7A] uppercase">
                        VISIBILITAS LOGISTIK GLOBAL
                    </span>
                </div>

                <!-- Massive Editorial Headline -->
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.08] text-[#13293D]">
                    End-to-End
                    <span class="text-[#265D7A] font-bold">Global</span>
                    Shipment Visibility.
                </h1>

                <!-- Concise Lightweight Narrative -->
                <p class="text-base sm:text-lg text-[#265D7A] font-normal leading-relaxed max-w-xl">
                    Pantau pergerakan kontainer interkontinental secara terverifikasi dengan validasi rute geospasial maritim, kalkulasi tarif transparan, dan peringatan keterlambatan real-time.
                </p>

                <!-- Solid Pill Primary CTA -->
                <div class="pt-2 flex flex-wrap items-center gap-4">
                    <button
                        type="button"
                        @click="emit('scrollToTrack')"
                        class="px-8 py-3.5 rounded-full bg-[#265D7A] hover:bg-[#13293D] text-white font-semibold text-sm transition-all duration-300 shadow-xl shadow-[#13293D]/20 hover:-translate-y-0.5 inline-flex items-center gap-3 cursor-pointer group"
                    >
                        <span>Lacak Kargo Sekarang</span>
                        <i class="bi bi-arrow-right text-xs transition-transform duration-200 group-hover:translate-x-1" aria-hidden="true"></i>
                    </button>
                </div>

                <!-- Bottom Left: 3 Real Statistics Separated by Vertical Dividers -->
                <div class="pt-8 mt-4 border-t border-[#265D7A]/20 grid grid-cols-3 gap-6 max-w-lg">
                    <div class="pr-4 border-r border-[#265D7A]/20">
                        <div class="text-3xl sm:text-4xl font-extrabold font-mono text-[#13293D] tracking-tight">
                            {{ displayShipments }}<span class="text-[#265D7A]">+</span>
                        </div>
                        <div class="text-[11px] font-semibold text-[#265D7A] mt-1">
                            Pengiriman Aktif
                        </div>
                    </div>
                    <div class="pr-4 border-r border-[#265D7A]/20">
                        <div class="text-3xl sm:text-4xl font-extrabold font-mono text-[#13293D] tracking-tight">
                            {{ displayCarriers }}
                        </div>
                        <div class="text-[11px] font-semibold text-[#265D7A] mt-1">
                            Mitra Ekspedisi
                        </div>
                    </div>
                    <div>
                        <div class="text-3xl sm:text-4xl font-extrabold font-mono text-[#13293D] tracking-tight">
                            {{ displayLocations }}
                        </div>
                        <div class="text-[11px] font-semibold text-[#265D7A] mt-1">
                            Pelabuhan &amp; Hub
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Translucent Frosted Globe Sculpture & Telemetry Overlay -->
            <div class="lg:col-span-5 relative flex items-center justify-center">
                <div class="relative w-full max-w-md">
                    <!-- Translucent Frosted Globe Sphere (Matches Reference) -->
                    <div class="relative rounded-full p-2">
                        <svg viewBox="0 0 420 420" class="w-full h-auto drop-shadow-[0_28px_60px_rgba(19,41,61,0.22)] select-none" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <radialGradient id="globeGlass" cx="35%" cy="30%" r="70%">
                                    <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.96" />
                                    <stop offset="55%" stop-color="#D9F2EE" stop-opacity="0.88" />
                                    <stop offset="100%" stop-color="#67C7C6" stop-opacity="0.72" />
                                </radialGradient>
                                <linearGradient id="globeArc" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#13293D" />
                                    <stop offset="50%" stop-color="#265D7A" />
                                    <stop offset="100%" stop-color="#67C7C6" />
                                </linearGradient>
                            </defs>

                            <!-- Main Frosted Sphere -->
                            <circle cx="210" cy="210" r="178" fill="url(#globeGlass)" stroke="rgba(255,255,255,0.9)" stroke-width="2" />

                            <!-- Abstract Continents Silhouette (Subtle Ice-Teal) -->
                            <path d="M105,140 Q145,105 190,128 T235,165 Q215,215 170,225 T110,195 Z" fill="#67C7C6" fill-opacity="0.42" />
                            <path d="M245,105 Q295,95 330,138 T315,205 Q270,215 245,175 Z" fill="#265D7A" fill-opacity="0.28" />
                            <path d="M165,245 Q225,235 270,275 T240,335 Q185,330 158,285 Z" fill="#67C7C6" fill-opacity="0.36" />

                            <!-- Meridian Grid Lines -->
                            <ellipse cx="210" cy="210" rx="95" ry="178" fill="none" stroke="rgba(38,93,122,0.16)" stroke-width="1.2" />
                            <ellipse cx="210" cy="210" rx="178" ry="62" fill="none" stroke="rgba(38,93,122,0.16)" stroke-width="1.2" />

                            <!-- Global Corridor Arcs -->
                            <path d="M 122,195 Q 205,110 298,142" fill="none" stroke="url(#globeArc)" stroke-width="2.5" stroke-dasharray="6 5" />
                            <path d="M 122,195 Q 225,245 315,215" fill="none" stroke="#265D7A" stroke-width="2" stroke-opacity="0.55" />

                            <!-- Port Nodes -->
                            <circle cx="122" cy="195" r="7" fill="#10B981" stroke="#FFFFFF" stroke-width="2.5" />
                            <text x="108" y="220" fill="#13293D" font-size="11" font-weight="bold" font-family="monospace">IDJKT</text>

                            <circle cx="298" cy="142" r="7" fill="#F59E0B" stroke="#FFFFFF" stroke-width="2.5" />
                            <text x="275" y="128" fill="#13293D" font-size="11" font-weight="bold" font-family="monospace">NLRTM</text>

                            <circle cx="315" cy="215" r="5" fill="#265D7A" stroke="#FFFFFF" stroke-width="2" />
                            <text x="295" y="238" fill="#265D7A" font-size="10" font-weight="bold" font-family="monospace">SGSIN</text>
                        </svg>
                    </div>

                    <!-- Floating Frosted Glass Telemetry Overlay Card -->
                    <div class="sm:absolute -bottom-4 right-0 sm:right-2 backdrop-blur-2xl bg-white/85 border border-white/95 rounded-2xl p-4 sm:p-5 shadow-[0_18px_40px_rgba(19,41,61,0.14)] max-w-xs mx-auto transition-transform duration-300 hover:-translate-y-1">
                        <div class="flex items-center justify-between gap-3 pb-2.5 border-b border-[#265D7A]/15">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#67C7C6] shadow-[0_0_8px_#67C7C6]"></span>
                                <span class="text-xs font-mono font-bold text-[#13293D] uppercase tracking-wider">Navigasi Aktif</span>
                            </div>
                            <span class="text-[10px] font-mono font-bold px-2.5 py-0.5 rounded-full bg-[#13293D] text-[#A7E4DB]">
                                21.4 KTS
                            </span>
                        </div>
                        <div class="mt-2.5 flex items-center justify-between text-xs">
                            <div>
                                <span class="text-[10px] text-[#265D7A] font-mono font-semibold block">KORIDOR MARITIM</span>
                                <strong class="font-mono text-[#13293D] text-xs">IDJKT &rarr; NLRTM</strong>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-[#265D7A] font-mono font-semibold block">STATUS</span>
                                <span class="text-xs font-mono font-bold text-emerald-800">On Schedule</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
