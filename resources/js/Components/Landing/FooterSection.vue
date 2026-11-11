<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    authUser: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <footer class="bg-[#080D1A] text-slate-400 py-14 px-4 sm:px-8 border-t border-white/10 text-xs relative z-10">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-2.5 font-black text-base text-white mb-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600 shadow-[0_0_8px_#2563eb]"></span>
                    <span>Delixie Platform</span>
                </div>
                <p class="max-w-md text-xs leading-relaxed text-slate-400">
                    Global Shipment Visibility & Real-Time Logistics Platform. Dibangun menggunakan Laravel 12, Vue 3, Inertia.js, Tailwind CSS v4, Leaflet.js, dan Chart.js.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-4 text-xs">
                <span class="px-3.5 py-2 rounded-xl bg-slate-900 border border-white/10 font-mono text-blue-400">
                    REST API v1 (Sanctum Protected)
                </span>
                <Link
                    :href="authUser ? '/dashboard' : '/login'"
                    class="px-5 py-2 rounded-full bg-white text-slate-950 font-bold hover:bg-blue-50 transition shadow-sm"
                >
                    {{ authUser ? 'Dashboard Operasional' : 'Portal Login' }} &rarr;
                </Link>
            </div>
        </div>

        <div class="max-w-7xl mx-auto mt-8 pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-500">
            <span>&copy; {{ new Date().getFullYear() }} Delixie Logistics Engineering. All Rights Reserved.</span>
            <span>Peta &copy; CARTO Dark Matter &bull; Telemetri oleh Open-Meteo &amp; Nominatim OSM</span>
        </div>
    </footer>
</template>
