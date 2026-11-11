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
    <footer class="bg-[#021110] text-[#9ABBC8] py-16 px-4 sm:px-8 border-t border-white/10 text-xs relative z-10">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-start md:items-center justify-between gap-8">
            <div>
                <div class="flex items-center gap-2.5 font-extrabold text-base text-white mb-2.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#0C969C] shadow-[0_0_8px_#0C969C]"></span>
                    <span>Delixie Platform</span>
                </div>
                <p class="max-w-md text-xs font-light leading-relaxed text-[#9ABBC8]">
                    Global Shipment Visibility & Real-Time Logistics Platform. Dibangun menggunakan Laravel 12, Vue 3, Inertia.js, Tailwind CSS v4, Leaflet.js, dan Chart.js.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-4 text-xs">
                <span class="px-4 py-2 rounded-xl bg-[#032324] border border-white/10 font-mono text-[#6BA3BE]">
                    REST API v1 (Sanctum Protected)
                </span>
                <Link
                    :href="authUser ? '/dashboard' : '/login'"
                    class="px-6 py-2.5 rounded-full bg-[#274D60] hover:bg-[#0A7075] text-white font-semibold transition shadow-md"
                >
                    {{ authUser ? 'Dashboard Operasional' : 'Portal Login' }} &rarr;
                </Link>
            </div>
        </div>

        <div class="max-w-7xl mx-auto mt-10 pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] font-light text-[#6BA3BE]">
            <span>&copy; {{ new Date().getFullYear() }} Delixie Logistics Engineering. All Rights Reserved.</span>
            <span>Peta &copy; Esri World Dark Canvas &bull; Telemetri oleh Open-Meteo &amp; Nominatim OSM</span>
        </div>
    </footer>
</template>
