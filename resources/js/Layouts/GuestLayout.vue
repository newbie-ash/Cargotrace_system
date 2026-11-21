<script setup>
import DynamicIslandToast from '@/Components/DynamicIslandToast.vue';
</script>

<template>
    <div class="min-h-screen bg-[#012B23] text-[#FAEFDB] flex flex-col justify-center py-6 sm:py-10 px-4 sm:px-6 lg:px-8 relative overflow-hidden selection:bg-[#BE2388] selection:text-white">
        <!-- Background Aerial Container Ship (Sailing Left, 4K Upscaled & Sharpened) -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden select-none" aria-hidden="true">
            <img
                src="/images/login-ship-bg.jpg"
                alt="Aerial Container Vessel Maritime Logistics"
                class="w-full h-full object-cover object-center opacity-85"
            />
            <!-- Atmospheric Multi-Stop Deep Teal Overlays for Crisp Contrast -->
            <div class="absolute inset-0 bg-gradient-to-r from-[#012B23]/90 via-[#012B23]/50 to-[#012B23]/80"></div>
            <div class="absolute inset-0 bg-gradient-to-b from-[#012B23]/75 via-transparent to-[#012B23]/85"></div>

            <!-- Soft Ambient Backlight Glows -->
            <div class="absolute -top-32 -left-28 w-[640px] h-[640px] rounded-full bg-[#BE2388]/15 blur-[120px]"></div>
            <div class="absolute bottom-10 right-10 w-[520px] h-[520px] rounded-full bg-[#0A3D32]/50 blur-[130px]"></div>
        </div>

        <DynamicIslandToast />

        <slot />
    </div>
</template>
