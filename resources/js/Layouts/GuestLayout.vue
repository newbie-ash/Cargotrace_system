<script setup>
import { Link } from '@inertiajs/vue3';
import DelixieLogo from '@/Components/DelixieLogo.vue';
import DynamicIslandToast from '@/Components/DynamicIslandToast.vue';
</script>

<template>
    <div class="min-h-screen bg-transparent text-[#13293D] flex flex-col items-center justify-center p-4 relative overflow-hidden selection:bg-teal-bright selection:text-[#13293D]">
        <DynamicIslandToast />
        <div class="w-full max-w-md relative z-10">
            <!-- Brand Header -->
            <div class="text-center mb-8">
                <Link href="/" class="inline-flex items-center hover:opacity-90 transition group">
                    <DelixieLogo
                        size-class="w-9 h-9"
                        wordmark-class="font-extrabold text-2xl tracking-tight text-[#13293D]"
                    />
                </Link>
                <p class="text-xs font-medium text-[#265D7A] mt-1.5">Global Shipment Visibility &amp; Tracking Platform</p>
            </div>

            <!-- Card Box with Frosted Glassmorphism -->
            <div class="bg-white/75 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 sm:p-8 shadow-[0_24px_60px_rgba(19,41,61,0.12)]">
                <slot />
            </div>

            <div class="text-center mt-6 text-xs font-medium text-[#265D7A]">
                &copy; {{ new Date().getFullYear() }} Delixie &bull; Global Freight Operations
            </div>
        </div>
    </div>
</template>
