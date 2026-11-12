<script setup>
import { computed } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Konfirmasi Penghapusan',
    },
    message: {
        type: String,
        default: 'Tindakan ini bersifat permanen dan tidak dapat dibatalkan. Lanjutkan?',
    },
    itemName: {
        type: String,
        default: '',
    },
    confirmText: {
        type: String,
        default: 'Ya, Hapus Data',
    },
    cancelText: {
        type: String,
        default: 'Batal',
    },
    type: {
        type: String,
        default: 'danger', // danger | warning | info
    },
    loading: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['confirm', 'close']);

const config = computed(() => {
    switch (props.type) {
        case 'warning':
            return {
                icon: 'bi-exclamation-triangle-fill',
                iconColor: 'text-amber-600',
                haloBg: 'bg-amber-500/20',
                btnBg: 'bg-amber-600 hover:bg-amber-700 text-white shadow-amber-600/20',
                badgeBg: 'bg-amber-500/15 text-amber-900 border-amber-500/30',
            };
        case 'info':
            return {
                icon: 'bi-info-circle-fill',
                iconColor: 'text-[#265D7A]',
                haloBg: 'bg-[#67C7C6]/25',
                btnBg: 'bg-[#265D7A] hover:bg-[#13293D] text-white shadow-[#13293D]/20',
                badgeBg: 'bg-[#EAF6F4] text-[#13293D] border-[#265D7A]/25',
            };
        case 'danger':
        default:
            return {
                icon: 'bi-trash3-fill',
                iconColor: 'text-rose-600',
                haloBg: 'bg-rose-500/20',
                btnBg: 'bg-rose-600 hover:bg-rose-700 text-white shadow-rose-600/25',
                badgeBg: 'bg-rose-500/15 text-rose-900 border-rose-500/30',
            };
    }
});
</script>

<template>
    <Modal :show="show" max-width="md" @close="emit('close')">
        <div class="text-center flex flex-col items-center">
            <!-- Pulsing Halo Icon Bubble -->
            <div class="relative flex items-center justify-center mb-4">
                <span :class="['absolute w-14 h-14 rounded-full animate-ping opacity-60 pointer-events-none', config.haloBg]"></span>
                <div class="relative w-12 h-12 rounded-2xl bg-white/95 border border-white flex items-center justify-center shadow-sm">
                    <i :class="['bi text-xl', config.icon, config.iconColor]" aria-hidden="true"></i>
                </div>
            </div>

            <!-- Title & Message -->
            <h3 class="text-base sm:text-lg font-bold text-[#13293D] tracking-tight">
                {{ title }}
            </h3>

            <p class="text-xs text-[#265D7A] mt-1.5 leading-relaxed max-w-sm">
                {{ message }}
            </p>

            <!-- Highlighted Item Name (If any) -->
            <div v-if="itemName" class="mt-3 px-3 py-1.5 rounded-xl border font-mono text-xs font-bold truncate max-w-full" :class="config.badgeBg">
                {{ itemName }}
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex flex-wrap items-center justify-center gap-2.5 w-full">
                <button
                    type="button"
                    @click="emit('close')"
                    :disabled="loading"
                    class="px-5 py-2 rounded-full border border-[#265D7A]/20 bg-white/85 hover:bg-white text-xs font-semibold text-[#13293D] transition cursor-pointer disabled:opacity-60"
                >
                    {{ cancelText }}
                </button>

                <button
                    type="button"
                    @click="emit('confirm')"
                    :disabled="loading"
                    :class="['px-6 py-2 rounded-full text-xs font-semibold transition inline-flex items-center gap-2 shadow-md cursor-pointer disabled:opacity-60', config.btnBg]"
                >
                    <i v-if="loading" class="bi bi-arrow-repeat animate-spin text-xs"></i>
                    <span>{{ confirmText }}</span>
                </button>
            </div>
        </div>
    </Modal>
</template>
