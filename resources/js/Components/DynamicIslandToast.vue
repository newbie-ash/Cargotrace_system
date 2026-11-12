<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const visible = ref(false);
const currentToast = ref({
    type: 'success', // success | error | warning | info
    message: '',
});

let timer = null;
const duration = 4500;
const progress = ref(100);
let progressInterval = null;
let remainingTime = duration;
let startTime = null;
let isPaused = false;

const toastConfig = computed(() => {
    switch (currentToast.value.type) {
        case 'error':
            return {
                label: 'SISTEM ERROR',
                icon: 'bi-exclamation-octagon-fill',
                badgeBg: 'bg-rose-500/15 text-rose-800 border-rose-500/30',
                iconColor: 'text-rose-600',
                barColor: 'bg-rose-500',
                glow: 'rgba(244, 63, 94, 0.25)',
            };
        case 'warning':
            return {
                label: 'PERINGATAN',
                icon: 'bi-exclamation-triangle-fill',
                badgeBg: 'bg-amber-500/15 text-amber-900 border-amber-500/30',
                iconColor: 'text-amber-600',
                barColor: 'bg-amber-500',
                glow: 'rgba(245, 158, 11, 0.25)',
            };
        case 'info':
            return {
                label: 'INFO TELEMETRI',
                icon: 'bi-info-circle-fill',
                badgeBg: 'bg-[#67C7C6]/20 text-[#13293D] border-[#67C7C6]/40',
                iconColor: 'text-[#265D7A]',
                barColor: 'bg-[#67C7C6]',
                glow: 'rgba(103, 199, 198, 0.35)',
            };
        case 'success':
        default:
            return {
                label: 'BERHASIL',
                icon: 'bi-check-circle-fill',
                badgeBg: 'bg-emerald-500/15 text-emerald-900 border-emerald-500/30',
                iconColor: 'text-emerald-600',
                barColor: 'bg-emerald-500',
                glow: 'rgba(16, 185, 129, 0.25)',
            };
    }
});

const startCountdown = () => {
    clearTimers();
    startTime = Date.now();
    isPaused = false;
    progress.value = (remainingTime / duration) * 100;

    progressInterval = setInterval(() => {
        if (!isPaused) {
            const elapsed = Date.now() - startTime;
            const currentRemaining = Math.max(0, remainingTime - elapsed);
            progress.value = (currentRemaining / duration) * 100;

            if (currentRemaining <= 0) {
                dismiss();
            }
        }
    }, 50);
};

const pauseCountdown = () => {
    if (isPaused) return;
    isPaused = true;
    remainingTime = Math.max(0, remainingTime - (Date.now() - startTime));
};

const resumeCountdown = () => {
    if (!isPaused) return;
    isPaused = false;
    startTime = Date.now();
};

const clearTimers = () => {
    if (timer) clearTimeout(timer);
    if (progressInterval) clearInterval(progressInterval);
    timer = null;
    progressInterval = null;
};

const showToast = (type, message) => {
    if (!message) return;
    currentToast.value = { type, message };
    remainingTime = duration;
    progress.value = 100;
    visible.value = true;
    startCountdown();
};

const dismiss = () => {
    visible.value = false;
    clearTimers();
};

// Watch for Inertia Flash Messages
watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;
        if (flash.success) {
            showToast('success', flash.success);
        } else if (flash.error) {
            showToast('error', flash.error);
        } else if (flash.warning) {
            showToast('warning', flash.warning);
        } else if (flash.info) {
            showToast('info', flash.info);
        }
    },
    { deep: true, immediate: true }
);

// Event listener for client-side programmatic triggers
const handleCustomToast = (e) => {
    if (e.detail?.message) {
        showToast(e.detail.type || 'info', e.detail.message);
    }
};

onMounted(() => {
    window.addEventListener('delixie-toast', handleCustomToast);
});

onUnmounted(() => {
    clearTimers();
    window.removeEventListener('delixie-toast', handleCustomToast);
});
</script>

<template>
    <div
        class="fixed top-4 inset-x-0 z-[60] flex justify-center px-4 pointer-events-none select-none"
        aria-live="polite"
    >
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="-translate-y-5 opacity-0 scale-95"
            enter-to-class="translate-y-0 opacity-100 scale-100"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100 scale-100"
            leave-to-class="-translate-y-3 opacity-0 scale-95"
        >
            <div
                v-if="visible"
                @mouseenter="pauseCountdown"
                @mouseleave="resumeCountdown"
                class="pointer-events-auto relative overflow-hidden rounded-full max-w-lg w-auto min-w-[280px] sm:min-w-[340px] px-4 py-2 sm:py-2.5 backdrop-blur-2xl transition-all duration-200 ease-out flex items-center justify-between gap-3 shadow-[0_16px_36px_rgba(19,41,61,0.16)] border border-white/95"
                :style="{
                    background: 'linear-gradient(135deg, rgba(255, 255, 255, 0.92) 0%, rgba(217, 242, 238, 0.55) 50%, rgba(255, 255, 255, 0.85) 100%)',
                    boxShadow: `inset 0 1.5px 1px 0 rgba(255, 255, 255, 0.95), inset 0 -2px 6px 0 ${toastConfig.glow}, 0 16px 36px -6px rgba(19, 41, 61, 0.15)`
                }"
            >
                <!-- Specular Beam -->
                <div
                    class="absolute inset-x-6 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-white/95 to-transparent pointer-events-none"
                    aria-hidden="true"
                ></div>

                <!-- Left Content: Pulse Ring + Badge + Message -->
                <div class="flex items-center gap-2.5 min-w-0 pr-1">
                    <!-- Icon Bubble with Radar Halo -->
                    <div class="relative shrink-0 flex items-center justify-center">
                        <span
                            class="absolute w-7 h-7 rounded-full animate-ping opacity-60 pointer-events-none"
                            :style="{ backgroundColor: toastConfig.glow }"
                        ></span>
                        <div class="relative w-6 h-6 rounded-full bg-white/90 border border-white flex items-center justify-center shadow-xs">
                            <i :class="['bi text-xs', toastConfig.icon, toastConfig.iconColor]" aria-hidden="true"></i>
                        </div>
                    </div>

                    <!-- Label & Text -->
                    <div class="min-w-0 flex items-center gap-2">
                        <span :class="['shrink-0 px-2 py-0.5 rounded-full border text-[9px] font-mono font-bold tracking-wider uppercase', toastConfig.badgeBg]">
                            {{ toastConfig.label }}
                        </span>
                        <p class="text-xs font-semibold text-[#13293D] truncate max-w-[200px] sm:max-w-xs">
                            {{ currentToast.message }}
                        </p>
                    </div>
                </div>

                <!-- Right Action: Close Button -->
                <button
                    type="button"
                    @click="dismiss"
                    class="shrink-0 p-1 rounded-full text-[#265D7A] hover:text-[#13293D] hover:bg-black/5 transition cursor-pointer"
                    title="Tutup Notifikasi"
                    aria-label="Tutup Notifikasi"
                >
                    <i class="bi bi-x text-base leading-none"></i>
                </button>

                <!-- Bottom Progress Bar -->
                <div class="absolute inset-x-0 bottom-0 h-[2px] bg-black/5 overflow-hidden">
                    <div
                        class="h-full transition-all duration-75 ease-linear"
                        :class="toastConfig.barColor"
                        :style="{ width: `${progress}%` }"
                    ></div>
                </div>
            </div>
        </Transition>
    </div>
</template>
