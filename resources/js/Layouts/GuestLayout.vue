<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-[#F6F3EC] text-[#1A1A1C] flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-md">
            <!-- Brand Header -->
            <div class="text-center mb-8">
                <Link href="/" class="inline-flex items-center gap-2.5 font-extrabold text-2xl tracking-tight text-[#1A1A1C]">
                    <span class="w-3 h-3 rounded-full bg-[#1A1A1C]"></span>
                    <span>CargoTrace</span>
                </Link>
                <p class="text-xs text-[#706E6B] font-medium mt-1">Global Shipment Visibility & Tracking Platform</p>
            </div>

            <!-- Card Box -->
            <div class="bg-white/80 backdrop-blur-xl border border-[#E5E0D5] rounded-2xl p-6 sm:p-8 shadow-sm">
                <slot />
            </div>

            <div class="text-center mt-6 text-xs text-[#706E6B]">
                &copy; {{ new Date().getFullYear() }} CargoTrace &bull; Web Programming 2
            </div>
        </div>
    </div>
</template>
