<script setup>
import DynamicIslandToast from '@/Components/DynamicIslandToast.vue';
import CargoTransportBg from '@/Components/CargoTransportBg.vue';
</script>

<template>
    <div class="min-h-screen bg-[#012B23] text-[#FAEFDB] flex flex-col justify-center py-6 sm:py-10 px-4 sm:px-6 lg:px-8 relative overflow-hidden selection:bg-[#BE2388] selection:text-white">
        <!-- New Crisp Vector Transport Cargo Background (Facing Left, Mathematically Sharp) -->
        <CargoTransportBg />

        <DynamicIslandToast />

        <slot />
    </div>
</template>
