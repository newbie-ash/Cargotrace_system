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

            <!-- Kolom Kanan: Interactive 3D Globe (Three.js / Globe.GL) & Telemetry Overlay -->
            <div class="lg:col-span-5 relative flex items-center justify-center">
                <div class="relative w-full max-w-md">
                    <div class="relative flex items-center justify-center">
                        <HeroGlobe3D :locations="locations" />
                    </div>

                    <!-- Floating Frosted Glass Telemetry Overlay Card -->
                    <div class="relative z-20 mt-4 sm:mt-0 sm:absolute -bottom-4 right-0 sm:right-2 backdrop-blur-2xl bg-white/85 border border-white/95 rounded-2xl p-4 sm:p-5 shadow-[0_18px_40px_rgba(19,41,61,0.14)] max-w-xs mx-auto transition-transform duration-300 hover:-translate-y-1">
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
