<script setup>
defineProps({
    icon: {
        type: String,
        default: 'bi-inbox',
    },
    title: {
        type: String,
        default: 'Tidak Ada Data Ditemukan',
    },
    description: {
        type: String,
        default: 'Coba sesuaikan kata kunci pencarian atau bersihkan filter yang aktif.',
    },
});
</script>

<template>
    <div class="py-12 px-4 flex flex-col items-center justify-center text-center">
        <!-- Ambient Glowing Radar Halo Container -->
        <div class="relative flex items-center justify-center mb-4">
            <span class="absolute w-14 h-14 rounded-full bg-[#67C7C6]/20 animate-ping opacity-75"></span>
            <div class="relative w-12 h-12 rounded-2xl bg-gradient-to-br from-[#EAF6F4] to-[#D9F2EE] border border-[#67C7C6]/40 flex items-center justify-center text-[#265D7A] shadow-sm">
                <i :class="['bi text-2xl text-[#265D7A]', icon]" aria-hidden="true"></i>
            </div>
        </div>

        <h3 class="text-sm font-bold text-[#13293D] tracking-tight">
            {{ title }}
        </h3>
        <p class="text-xs text-[#265D7A] mt-1 max-w-sm leading-relaxed">
            {{ description }}
        </p>

        <!-- Action Buttons Slot -->
        <div v-if="$slots.action" class="mt-4 flex flex-wrap items-center justify-center gap-2">
            <slot name="action" />
        </div>
    </div>
</template>
