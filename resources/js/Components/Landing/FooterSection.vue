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
    <footer class="bg-[#0B1926]/85 backdrop-blur-2xl text-slate-300 py-16 px-4 sm:px-8 border-t border-white/10 text-xs relative z-10 shadow-[0_-12px_36px_rgba(0,0,0,0.28)]">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-start md:items-center justify-between gap-8">
            <div>
                <div class="mb-2.5">
                    <DelixieLogo
                        size-class="w-7 h-7"
                        wordmark-class="font-extrabold text-base text-white"
                        wordmark-text="Delixie Platform"
                    />
                </div>
                <p class="max-w-md text-xs font-normal leading-relaxed text-slate-300">
                    Global Shipment Visibility &amp; Real-Time Logistics Platform. Dibangun menggunakan Laravel 12, Vue 3, Inertia.js, Tailwind CSS v4, Leaflet.js, dan Chart.js.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-4 text-xs">
                <span class="px-4 py-2 rounded-xl bg-white/10 border border-white/15 font-mono font-semibold text-[#A7E4DB]">
                    REST API v1 (Sanctum Protected)
                </span>
                <Link
                    :href="authUser ? '/dashboard' : '/login'"
                    class="px-6 py-2.5 rounded-full bg-gradient-to-r from-[#67C7C6] to-[#A7E4DB] hover:brightness-110 text-[#102232] font-bold transition shadow-md shadow-[#67C7C6]/20"
                >
                    {{ authUser ? 'Dashboard Operasional' : 'Portal Login' }} &rarr;
                </Link>
            </div>
        </div>
        <div class="max-w-7xl mx-auto mt-10 pt-6 border-t border-white/10 flex flex-col sm:flex-row justify-between items-center gap-2 text-[11px] font-medium text-slate-400">
            <span>&copy; {{ new Date().getFullYear() }} Delixie Platform &bull; Web Programming 2 Final Project.</span>
            <span class="text-[#A7E4DB]">Visibilitas Rantai Pasok Terintegrasi.</span>
        </div>
    </footer>
</template>
