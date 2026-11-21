<script setup>
import { Link } from '@inertiajs/vue3';
import DelixieLogo from '@/Components/DelixieLogo.vue';

defineProps({
    authUser: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <footer class="bg-[#001F18]/90 backdrop-blur-2xl text-[#FAEFDB]/80 py-16 px-4 sm:px-8 border-t border-[#FAEFDB]/15 text-xs relative z-10 shadow-[0_-12px_36px_rgba(0,0,0,0.45)]">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-start md:items-center justify-between gap-8">
            <div>
                <div class="mb-2.5">
                    <DelixieLogo
                        size-class="w-7 h-7"
                        wordmark-class="font-extrabold text-base text-[#FAEFDB]"
                        wordmark-text="Delixie Platform"
                    />
                </div>
                <p class="max-w-md text-xs font-normal leading-relaxed text-[#FAEFDB]/75">
                    Global Shipment Visibility &amp; Real-Time Logistics Platform. Dibangun menggunakan Laravel 12, Vue 3, Inertia.js, Tailwind CSS v4, Leaflet.js, dan Chart.js.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-4 text-xs">
                <span class="px-4 py-2 rounded-xl bg-[#012B23] border border-[#FAEFDB]/20 font-mono font-semibold text-[#FAEFDB]">
                    REST API v1 (Sanctum Protected)
                </span>
                <Link
                    :href="authUser ? '/dashboard' : '/login'"
                    class="px-6 py-2.5 rounded-full bg-gradient-to-r from-[#BE2388] to-[#D42B8A] hover:brightness-110 text-white font-bold transition shadow-md shadow-[#BE2388]/25 cursor-pointer"
                >
                    {{ authUser ? 'Dashboard Operasional' : 'Portal Login' }} &rarr;
                </Link>
            </div>
        </div>
        <div class="max-w-7xl mx-auto mt-10 pt-6 border-t border-[#FAEFDB]/15 flex flex-col sm:flex-row justify-between items-center gap-2 text-[11px] font-medium text-[#FAEFDB]/60">
            <span>&copy; {{ new Date().getFullYear() }} Delixie Platform &bull; Web Programming 2 Final Project.</span>
            <span class="text-[#FAEFDB]/80 font-mono">Deep Teal &bull; Bone &bull; Magenta Palette</span>
        </div>
    </footer>
</template>
