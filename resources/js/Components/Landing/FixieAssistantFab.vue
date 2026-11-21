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
                class="mb-3 w-[92vw] sm:w-96 rounded-3xl bg-white/90 backdrop-blur-2xl border border-white p-5 shadow-[0_24px_60px_rgba(19,41,61,0.18)] text-[#13293D] text-xs relative overflow-hidden"
            >
                <!-- Header with Mascot Avatar & Close -->
                <div class="flex items-center justify-between pb-3 border-b border-[#265D7A]/15 relative z-10">
                    <div class="flex items-center gap-3">
                        <!-- Fixie Mascot SVG Avatar -->
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#13293D] to-[#265D7A] p-1 flex items-center justify-center shadow-lg shadow-[#13293D]/20 shrink-0">
                            <svg viewBox="0 0 48 48" class="w-10 h-10 select-none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="24" cy="24" r="18" fill="#13293D" stroke="#67C7C6" stroke-width="2" />
                                <line x1="24" y1="6" x2="24" y2="1" stroke="#A7E4DB" stroke-width="2" stroke-linecap="round" />
                                <circle cx="24" cy="1" r="2.5" fill="#67C7C6" />
                                <rect x="14" y="18" width="20" height="9" rx="4.5" fill="#265D7A" />
                                <circle cx="19" cy="22.5" r="2" fill="#FFFFFF" />
                                <circle cx="29" cy="22.5" r="2" fill="#FFFFFF" />
                                <path d="M 20 32 Q 24 35 28 32" fill="none" stroke="#A7E4DB" stroke-width="1.8" stroke-linecap="round" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-sm text-[#13293D]">Fixie</span>
                                <span class="px-2 py-0.5 rounded-full bg-[#A7E4DB]/45 text-[#13293D] border border-[#265D7A]/20 text-[9px] font-mono font-bold uppercase">
                                    Asisten Resi
                                </span>
                            </div>
                            <span class="text-[10px] font-semibold text-emerald-700 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                Standby &bull; Delixie
                            </span>
                        </div>
                    </div>
                    <button
                        @click="closeAssistant"
                        type="button"
                        aria-label="Tutup Panel Fixie"
                        class="w-7 h-7 rounded-full bg-[#EAF6F4] hover:bg-[#67C7C6]/30 border border-[#265D7A]/15 flex items-center justify-center text-[#265D7A] hover:text-[#13293D] transition cursor-pointer"
                    >
                        <i class="bi bi-x-lg text-xs" aria-hidden="true"></i>
                    </button>
                </div>

                <!-- Speech Bubble Dialogue -->
                <div class="mt-3 p-3.5 rounded-2xl bg-[#EAF6F4]/90 border border-[#265D7A]/15 leading-relaxed text-[#265D7A] font-normal">
                    <p>
                        Halo! Saya <strong class="font-bold text-[#13293D]">Fixie</strong>, asisten navigasi kargo Delixie. Mau lacak kontainer antar benua, kalkulasi tarif rute, atau cek jaringan hub?
                    </p>
                </div>

                <!-- Quick Track Input (Dark Navy Track Input Contrast per Reference) -->
                <div class="mt-3 space-y-1.5">
                    <label for="fixie-track-input" class="text-[10px] uppercase font-mono tracking-wider text-[#265D7A] font-bold block">
                        Ketik Resi Instan
                    </label>
                    <div class="flex gap-2">
                        <input
                            id="fixie-track-input"
                            v-model="quickSearchCode"
                            @keyup.enter="handleQuickTrack(quickSearchCode)"
                            type="text"
                            placeholder="Contoh: CT-2026-0001"
                            class="flex-1 h-9 px-3 bg-[#13293D] border border-[#265D7A] rounded-xl text-xs font-mono text-white placeholder-[#A7E4DB]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/50"
                        />
                        <button
                            type="button"
                            @click="handleQuickTrack(quickSearchCode)"
                            class="px-4 h-9 bg-[#67C7C6] hover:bg-[#A7E4DB] text-[#13293D] font-bold rounded-full transition cursor-pointer text-xs shrink-0 shadow-sm"
                        >
                            Lacak
                        </button>
                    </div>
                </div>

                <!-- Quick Action Chips -->
                <div class="mt-3 pt-3 border-t border-[#265D7A]/15 space-y-2">
                    <div class="text-[10px] uppercase font-mono tracking-wider text-[#265D7A] font-bold">
                        Pilihan Cepat
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <button
                            v-for="sample in sampleShipments"
                            :key="sample"
                            type="button"
                            @click="handleQuickTrack(sample)"
                            class="px-2.5 py-1 rounded-lg bg-[#EAF6F4] hover:bg-[#67C7C6]/35 text-[#13293D] border border-[#265D7A]/20 text-[11px] font-mono font-bold transition cursor-pointer"
                        >
                            <i class="bi bi-box-seam mr-1 text-[#265D7A]" aria-hidden="true"></i>{{ sample }}
                        </button>

                        <button
                            type="button"
                            @click="handleTabAction('tariff')"
                            class="px-2.5 py-1 rounded-lg bg-white hover:bg-[#EAF6F4] text-[#265D7A] hover:text-[#13293D] border border-[#265D7A]/20 text-[11px] font-semibold transition cursor-pointer"
                        >
                            <i class="bi bi-calculator mr-1" aria-hidden="true"></i>Kalkulator Tarif
                        </button>

                        <button
                            type="button"
                            @click="handleTabAction('hubs')"
                            class="px-2.5 py-1 rounded-lg bg-white hover:bg-[#EAF6F4] text-[#265D7A] hover:text-[#13293D] border border-[#265D7A]/20 text-[11px] font-semibold transition cursor-pointer"
                        >
                            <i class="bi bi-geo-alt mr-1" aria-hidden="true"></i>Jaringan Pelabuhan
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Tooltip & Main FAB Row -->
        <div class="flex items-center gap-3">
            <!-- Frosted Glass Tooltip -->
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
                    class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-full bg-white/85 backdrop-blur-2xl border border-white text-[#13293D] font-medium text-xs shadow-lg shadow-[#13293D]/10"
                >
                    <span class="w-2 h-2 rounded-full bg-[#67C7C6]"></span>
                    <span>Fixie standby. Sini kubantu lacak resimu.</span>
                    <button
                        @click="showTooltip = false"
                        type="button"
                        aria-label="Tutup Tooltip"
                        class="text-[#265D7A] hover:text-[#13293D] transition ml-1 cursor-pointer"
                    >
                        <i class="bi bi-x" aria-hidden="true"></i>
                    </button>
                </div>
            </Transition>

            <!-- Deep Ocean Steel FAB Button -->
            <button
                @click="toggleAssistant"
                type="button"
                aria-label="Buka Asisten AI Fixie"
                class="relative w-14 h-14 rounded-full bg-[#265D7A] hover:bg-[#13293D] border border-white/40 text-white flex items-center justify-center shadow-[0_12px_30px_rgba(19,41,61,0.3)] transition-all duration-300 hover:scale-105 cursor-pointer group"
                :class="{ 'ring-2 ring-[#67C7C6]': isOpen }"
            >
                <i v-if="isOpen" class="bi bi-chevron-down text-xl" aria-hidden="true"></i>
                <svg v-else viewBox="0 0 24 24" class="w-7 h-7 fill-white group-hover:scale-105 transition-transform" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.39-1 1.73V7h1a7 7 0 0 1 7 7h1a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v1a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-1H2a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h1a7 7 0 0 1 7-7h1V5.73c-.6-.34-1-.99-1-1.73a2 2 0 0 1 2-2M7.5 13A1.5 1.5 0 0 0 6 14.5 1.5 1.5 0 0 0 7.5 16 1.5 1.5 0 0 0 9 14.5 1.5 1.5 0 0 0 7.5 13m9 0a1.5 1.5 0 0 0-1.5 1.5 1.5 1.5 0 0 0 1.5 1.5 1.5 1.5 0 0 0 1.5-1.5 1.5 1.5 0 0 0-1.5-1.5M12 17c-1.5 0-2.8.7-3.5 1.8h7c-.7-1.1-2-1.8-3.5-1.8Z"/>
                </svg>
            </button>
        </div>
    </aside>
</template>
