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

onMounted(() => {
    animateCounter(displayShipments, props.stats.total_shipments || 8, 900);
    animateCounter(displayCarriers, props.stats.active_carriers || 7, 900);
    animateCounter(displayLocations, props.stats.total_locations || 8, 900);
});
</script>

<template>
    <section
        aria-label="Hero Section Delixie"
        class="relative min-h-[90vh] overflow-hidden bg-gradient-to-br from-[#031716] via-[#032324] to-[#032F30] text-white pt-32 pb-24 px-6 sm:px-12 lg:px-16 flex flex-col justify-center"
    >
        <!-- Subtle Ambient Ocean Glow (Airy Negative Space) -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <div class="absolute top-1/4 -right-32 w-[600px] h-[600px] rounded-full bg-[#0C969C]/10 blur-[160px]"></div>
            <div class="absolute -bottom-20 -left-20 w-[500px] h-[500px] rounded-full bg-[#0A7075]/12 blur-[150px]"></div>
        </div>

        <!-- Main Hero Grid: Split Asymmetrical Editorial Layout -->
        <div class="relative z-10 max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
            <!-- Kolom Kiri: Headline, Narrative & Metrics -->
            <div class="lg:col-span-7 flex flex-col justify-between space-y-8">
                <!-- Eyebrow Category -->
                <div class="flex items-center gap-3">
                    <span class="w-8 h-px bg-[#0C969C]" aria-hidden="true"></span>
                    <span class="text-xs font-mono font-semibold tracking-widest text-[#6BA3BE] uppercase">
                        VISIBILITAS LOGISTIK GLOBAL
                    </span>
                </div>

                <!-- Massive Editorial Headline -->
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.08] text-white">
                    End-to-End
                    <span class="text-[#0C969C] font-light">Global</span>
                    Shipment Visibility.
                </h1>

                <!-- Concise Lightweight Narrative -->
                <p class="text-base sm:text-lg text-[#9ABBC8] font-light leading-relaxed max-w-xl">
                    Pantau pergerakan kontainer interkontinental secara terverifikasi dengan validasi rute geospasial maritim, kalkulasi tarif transparan, dan peringatan keterlambatan real-time.
                </p>

                <!-- Solid Pill Primary CTA -->
                <div class="pt-2">
                    <button
                        type="button"
                        @click="emit('scrollToTrack')"
                        class="px-8 py-3.5 rounded-full bg-[#274D60] hover:bg-[#0A7075] text-white font-semibold text-sm transition-all duration-300 shadow-xl shadow-black/25 hover:-translate-y-0.5 inline-flex items-center gap-3 cursor-pointer group"
                    >
                        <span>Lacak Kargo Sekarang</span>
                        <i class="bi bi-arrow-right text-xs transition-transform duration-200 group-hover:translate-x-1" aria-hidden="true"></i>
                    </button>
                </div>

                <!-- Bottom Left: 3 Minimalist Statistics Separated by Vertical Dividers -->
                <div class="pt-8 mt-4 border-t border-white/10 grid grid-cols-3 gap-6 max-w-lg">
                    <div class="pr-4 border-r border-white/15">
                        <div class="text-3xl sm:text-4xl font-extrabold font-mono text-white tracking-tight">
                            {{ displayShipments }}<span class="text-[#0C969C]">+</span>
                        </div>
                        <div class="text-[11px] font-medium text-[#9ABBC8] mt-1">
                            Pengiriman Aktif
                        </div>
                    </div>
                    <div class="pr-4 border-r border-white/15">
                        <div class="text-3xl sm:text-4xl font-extrabold font-mono text-white tracking-tight">
                            {{ displayCarriers }}
                        </div>
                        <div class="text-[11px] font-medium text-[#9ABBC8] mt-1">
                            Mitra Ekspedisi
                        </div>
                    </div>
                    <div>
                        <div class="text-3xl sm:text-4xl font-extrabold font-mono text-white tracking-tight">
                            {{ stats.on_time_rate || 98 }}<span class="text-[#0C969C]">%</span>
                        </div>
                        <div class="text-[11px] font-medium text-[#9ABBC8] mt-1">
                            Ketepatan SLA
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Seamlessly Integrated Logistics Visual with Frosted Telemetry Overlay -->
            <div class="lg:col-span-5 relative flex items-center justify-center">
                <div class="relative w-full max-w-lg">
                    <!-- Integrated Vector Ocean & Cargo Vessel Graphic with Soft Background Fade -->
                    <div class="relative rounded-3xl overflow-hidden [mask-image:radial-gradient(ellipse_at_center,black_65%,transparent_100%)]">
                        <svg viewBox="0 0 500 360" class="w-full h-auto select-none" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="oceanGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#032F30" stop-opacity="0.9" />
                                    <stop offset="100%" stop-color="#031716" stop-opacity="0.3" />
                                </linearGradient>
                                <linearGradient id="hullGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#052526" />
                                    <stop offset="50%" stop-color="#0A7075" />
                                    <stop offset="100%" stop-color="#0C969C" />
                                </linearGradient>
                            </defs>

                            <!-- Subtle Oceanic Depth Contours -->
                            <path d="M 0,260 Q 125,235 250,260 T 500,260" fill="none" stroke="rgba(107,163,190,0.18)" stroke-width="1.5" />
                            <path d="M 0,285 Q 125,265 250,285 T 500,285" fill="none" stroke="rgba(12,150,156,0.22)" stroke-width="2" />
                            <path d="M 0,310 Q 125,295 250,310 T 500,310" fill="none" stroke="rgba(10,112,117,0.28)" stroke-width="2.5" />

                            <!-- Intercontinental Corridors -->
                            <path d="M 90,80 Q 250,30 410,110" fill="none" stroke="#0C969C" stroke-width="2" stroke-dasharray="6 6" opacity="0.6" />
                            <circle cx="90" cy="80" r="5" fill="#10B981" />
                            <text x="102" y="84" fill="#6BA3BE" font-size="11" font-family="monospace">IDJKT</text>

                            <circle cx="410" cy="110" r="5" fill="#F59E0B" />
                            <text x="422" y="114" fill="#F59E0B" font-size="11" font-family="monospace">NLRTM</text>

                            <!-- Modern Minimalist Container Vessel -->
                            <!-- Stack Row 1 -->
                            <rect x="180" y="180" width="28" height="16" rx="2" fill="#0A7075" stroke="#032F30" stroke-width="1.5" />
                            <rect x="212" y="180" width="28" height="16" rx="2" fill="#274D60" stroke="#032F30" stroke-width="1.5" />
                            <rect x="244" y="180" width="28" height="16" rx="2" fill="#0C969C" stroke="#032F30" stroke-width="1.5" />
                            <rect x="276" y="180" width="28" height="16" rx="2" fill="#6BA3BE" stroke="#032F30" stroke-width="1.5" />
                            <rect x="308" y="180" width="28" height="16" rx="2" fill="#0A7075" stroke="#032F30" stroke-width="1.5" />

                            <!-- Stack Row 2 -->
                            <rect x="196" y="160" width="28" height="16" rx="2" fill="#0C969C" stroke="#032F30" stroke-width="1.5" />
                            <rect x="228" y="160" width="28" height="16" rx="2" fill="#6BA3BE" stroke="#032F30" stroke-width="1.5" />
                            <rect x="260" y="160" width="28" height="16" rx="2" fill="#274D60" stroke="#032F30" stroke-width="1.5" />
                            <rect x="292" y="160" width="28" height="16" rx="2" fill="#0C969C" stroke="#032F30" stroke-width="1.5" />

                            <!-- Superstructure Bridge -->
                            <polygon points="345,135 380,135 375,200 345,200" fill="#032F30" stroke="#0A7075" stroke-width="1.5" />
                            <rect x="350" y="145" width="22" height="6" rx="1" fill="#6BA3BE" opacity="0.8" />
                            <line x1="362" y1="135" x2="362" y2="115" stroke="#6BA3BE" stroke-width="2" />
                            <circle cx="362" cy="112" r="3" fill="#0C969C" />

                            <!-- Vessel Hull -->
                            <path d="M 120,200 L 155,250 L 400,250 L 420,200 Z" fill="url(#hullGrad)" stroke="#6BA3BE" stroke-width="1.5" />
                            <line x1="120" y1="200" x2="420" y2="200" stroke="#6BA3BE" stroke-width="2" />
                        </svg>
                    </div>

                    <!-- Floating Frosted Glass Telemetry Overlay Card (Solar Power Reference Style) -->
                    <div class="absolute -bottom-6 right-2 sm:right-6 backdrop-blur-xl bg-[#0A7075]/20 border border-white/10 rounded-2xl p-4 sm:p-5 shadow-[0_16px_36px_rgba(0,0,0,0.5)] max-w-xs transition-transform duration-300 hover:-translate-y-1">
                        <div class="flex items-center justify-between gap-3 pb-2.5 border-b border-white/10">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#0C969C] shadow-[0_0_8px_#0C969C]"></span>
                                <span class="text-xs font-mono font-bold text-white uppercase tracking-wider">Navigasi Aktif</span>
                            </div>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-[#032F30] text-[#6BA3BE] border border-white/10">
                                21.4 KTS
                            </span>
                        </div>
                        <div class="mt-2.5 flex items-center justify-between text-xs">
                            <div>
                                <span class="text-[10px] text-[#9ABBC8] font-mono block">KORIDOR MARITIM</span>
                                <strong class="font-mono text-white text-xs">IDJKT &rarr; NLRTM</strong>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-[#9ABBC8] font-mono block">STATUS</span>
                                <span class="text-xs font-mono font-bold text-emerald-400">On Schedule</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
