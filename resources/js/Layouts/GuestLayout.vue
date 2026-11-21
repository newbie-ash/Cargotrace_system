<script setup>
import DynamicIslandToast from '@/Components/DynamicIslandToast.vue';
</script>

<template>
    <div class="min-h-screen bg-[#012B23] text-[#FAEFDB] flex flex-col justify-center py-6 sm:py-10 px-4 sm:px-6 lg:px-8 relative overflow-hidden selection:bg-[#BE2388] selection:text-white">
        <!-- Ambient Deep Teal & Magenta Glow Backdrop -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none" aria-hidden="true">
            <div class="absolute -top-32 -left-28 w-[680px] h-[680px] rounded-full bg-gradient-to-br from-[#02332A] via-[#0A3D32]/50 to-[#BE2388]/15 blur-[110px]"></div>
            <div class="absolute -bottom-36 -right-28 w-[720px] h-[720px] rounded-full bg-[#001F18]/85 blur-[120px]"></div>
            <div class="absolute top-1/3 right-1/4 w-[400px] h-[400px] rounded-full bg-[#BE2388]/10 blur-[130px]"></div>
        </div>

        <DynamicIslandToast />

        <slot />
    </div>
</template>
