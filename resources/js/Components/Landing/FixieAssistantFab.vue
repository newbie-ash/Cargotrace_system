<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    sampleShipments: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['triggerTrack', 'scrollToTab']);

const isOpen = ref(false);
const showTooltip = ref(true);
const quickSearchCode = ref('');

const toggleAssistant = () => {
    isOpen.value = !isOpen.value;
    if (isOpen.value) {
        showTooltip.value = false;
    }
};

const closeAssistant = () => {
    isOpen.value = false;
};

const handleQuickTrack = (code) => {
    emit('triggerTrack', code);
    closeAssistant();
};

const handleTabAction = (tabName) => {
    emit('scrollToTab', tabName);
    closeAssistant();
};

const handleKeydown = (e) => {
    if (e.key === 'Escape' && isOpen.value) {
        closeAssistant();
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <aside aria-label="AI Assistant Fixie" class="fixed bottom-6 right-6 z-50 pointer-events-auto flex flex-col items-end">
        <!-- Floating Dialog / Mascot Copilot Panel -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-4 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 translate-y-4 scale-95"
        >
            <div
                v-if="isOpen"
                role="dialog"
                aria-modal="true"
                aria-label="Panel Asisten Fixie"
                class="mb-3 w-[92vw] sm:w-96 rounded-3xl bg-slate-900/95 backdrop-blur-2xl border border-blue-500/30 p-5 shadow-[0_20px_50px_rgba(0,0,0,0.7)] text-white text-xs relative overflow-hidden"
            >
                <!-- Subtle Background Grid & Glow -->
                <div class="absolute -top-12 -right-12 w-36 h-36 bg-blue-600/20 rounded-full blur-2xl pointer-events-none"></div>
                <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-amber-500/15 rounded-full blur-2xl pointer-events-none"></div>

                <!-- Header with Mascot Avatar & Close -->
                <div class="flex items-center justify-between pb-3 border-b border-white/10 relative z-10">
                    <div class="flex items-center gap-3">
                        <!-- Fixie Mascot SVG Avatar -->
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 p-1 flex items-center justify-center shadow-lg shadow-blue-500/30 shrink-0">
                            <svg viewBox="0 0 48 48" class="w-10 h-10 select-none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="24" cy="24" r="18" fill="#0F172A" stroke="#38BDF8" stroke-width="2" />
                                <!-- Antenna -->
                                <line x1="24" y1="6" x2="24" y2="1" stroke="#38BDF8" stroke-width="2" stroke-linecap="round" />
                                <circle cx="24" cy="1" r="2.5" fill="#F59E0B" />
                                <!-- Visor Eyes -->
                                <rect x="14" y="18" width="20" height="9" rx="4.5" fill="#0284C7" />
                                <circle cx="19" cy="22.5" r="2" fill="#E0F2FE" />
                                <circle cx="29" cy="22.5" r="2" fill="#E0F2FE" />
                                <!-- Cheerful Mouth -->
                                <path d="M 20 32 Q 24 35 28 32" fill="none" stroke="#38BDF8" stroke-width="1.8" stroke-linecap="round" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-sm text-white">Fixie</span>
                                <span class="px-1.5 py-0.5 rounded-full bg-blue-500/20 text-blue-300 border border-blue-500/40 text-[9px] font-mono font-bold uppercase">
                                    AI Copilot
                                </span>
                            </div>
                            <span class="text-[10px] text-emerald-400 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                Standby &bull; Delixie Intelligence
                            </span>
                        </div>
                    </div>
                    <button
                        @click="closeAssistant"
                        type="button"
                        aria-label="Tutup Panel Fixie"
                        class="w-7 h-7 rounded-full bg-white/5 hover:bg-white/15 border border-white/10 flex items-center justify-center text-slate-300 hover:text-white transition cursor-pointer"
                    >
                        <i class="bi bi-x-lg text-xs" aria-hidden="true"></i>
                    </button>
                </div>

                <!-- Speech Bubble Dialogue -->
                <div class="mt-3 p-3.5 rounded-2xl bg-slate-950/70 border border-white/10 leading-relaxed text-slate-200">
                    <p>
                        Halo! Saya <strong>Fixie</strong>, asisten navigasi kargo Delixie. Mau lacak kontainer antar benua, kalkulasi tarif rute, atau pantau cuaca pelabuhan hari ini?
                    </p>
                </div>

                <!-- Quick Track Input -->
                <div class="mt-3 space-y-1.5">
                    <label for="fixie-track-input" class="text-[10px] uppercase font-mono tracking-wider text-slate-400 font-bold block">
                        Ketik Resi Instan
                    </label>
                    <div class="flex gap-2">
                        <input
                            id="fixie-track-input"
                            v-model="quickSearchCode"
                            @keyup.enter="handleQuickTrack(quickSearchCode)"
                            type="text"
                            placeholder="Contoh: CT-2026-0001"
                            class="flex-1 h-9 px-3 bg-slate-950 border border-white/15 rounded-xl text-xs font-mono text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                        />
                        <button
                            type="button"
                            @click="handleQuickTrack(quickSearchCode)"
                            class="px-3.5 h-9 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl transition cursor-pointer text-xs shrink-0"
                        >
                            Lacak
                        </button>
                    </div>
                </div>

                <!-- Quick Action Chips -->
                <div class="mt-3 pt-3 border-t border-white/10 space-y-2">
                    <div class="text-[10px] uppercase font-mono tracking-wider text-slate-400 font-bold">
                        Pilihan Cepat
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <button
                            v-for="sample in sampleShipments"
                            :key="sample"
                            type="button"
                            @click="handleQuickTrack(sample)"
                            class="px-2.5 py-1 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-300 border border-blue-500/30 text-[11px] font-mono transition cursor-pointer"
                        >
                            <i class="bi bi-box-seam mr-1" aria-hidden="true"></i>{{ sample }}
                        </button>

                        <button
                            type="button"
                            @click="handleTabAction('tariff')"
                            class="px-2.5 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[11px] font-semibold transition cursor-pointer"
                        >
                            <i class="bi bi-calculator mr-1" aria-hidden="true"></i>Kalkulator Tarif
                        </button>

                        <button
                            type="button"
                            @click="handleTabAction('hubs')"
                            class="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[11px] font-semibold transition cursor-pointer"
                        >
                            <i class="bi bi-geo-alt mr-1" aria-hidden="true"></i>Jaringan Pelabuhan
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Tooltip & Main FAB Row -->
        <div class="flex items-center gap-3">
            <!-- Glassmorphism Tooltip -->
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 translate-x-2"
                enter-to-class="opacity-100 translate-x-0"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0 translate-x-2"
            >
                <div
                    v-if="showTooltip && !isOpen"
                    class="hidden sm:flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-slate-900/90 backdrop-blur-xl border border-blue-500/30 text-white text-xs shadow-xl shadow-black/40"
                >
                    <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                    <span>Fixie standby. Sini kubantu lacak resimu.</span>
                    <button
                        @click="showTooltip = false"
                        type="button"
                        aria-label="Tutup Tooltip"
                        class="text-slate-400 hover:text-white transition ml-1 cursor-pointer"
                    >
                        <i class="bi bi-x" aria-hidden="true"></i>
                    </button>
                </div>
            </Transition>

            <!-- Pulsing Royal Blue FAB Button -->
            <button
                @click="toggleAssistant"
                type="button"
                aria-label="Buka Asisten AI Fixie"
                class="relative w-14 h-14 rounded-full bg-blue-600 hover:bg-blue-500 text-white flex items-center justify-center shadow-[0_0_25px_rgba(37,99,235,0.6)] transition-all duration-300 hover:scale-105 cursor-pointer group"
                :class="{ 'ring-4 ring-blue-400/40': isOpen }"
            >
                <!-- Pulse Glow Ring -->
                <span class="absolute inset-0 rounded-full bg-blue-500 animate-ping opacity-25"></span>

                <!-- Animated Icon / Mascot Face -->
                <i v-if="isOpen" class="bi bi-chevron-down text-xl" aria-hidden="true"></i>
                <svg v-else viewBox="0 0 24 24" class="w-7 h-7 fill-white group-hover:scale-110 transition-transform" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2M7.5 13A1.5 1.5 0 0 0 6 14.5 1.5 1.5 0 0 0 7.5 16 1.5 1.5 0 0 0 9 14.5 1.5 1.5 0 0 0 7.5 13m9 0a1.5 1.5 0 0 0-1.5 1.5 1.5 1.5 0 0 0 1.5 1.5 1.5 1.5 0 0 0 1.5-1.5 1.5 1.5 0 0 0-1.5-1.5M12 17c-1.5 0-2.8.7-3.5 1.8h7c-.7-1.1-2-1.8-3.5-1.8Z"/>
                </svg>
            </button>
        </div>
    </aside>
</template>
