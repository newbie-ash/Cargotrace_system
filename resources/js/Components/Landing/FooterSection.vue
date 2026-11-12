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
    <footer class="bg-white/60 backdrop-blur-2xl text-[#265D7A] py-16 px-4 sm:px-8 border-t border-white/85 text-xs relative z-10 shadow-[0_-12px_36px_rgba(19,41,61,0.04)]">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-start md:items-center justify-between gap-8">
            <div>
                <div class="mb-2.5">
                    <DelixieLogo
                        size-class="w-7 h-7"
                        wordmark-class="font-extrabold text-base text-[#13293D]"
                        wordmark-text="Delixie Platform"
                    />
                </div>
                <p class="max-w-md text-xs font-normal leading-relaxed text-[#265D7A]">
                    Global Shipment Visibility &amp; Real-Time Logistics Platform. Dibangun menggunakan Laravel 12, Vue 3, Inertia.js, Tailwind CSS v4, Leaflet.js, dan Chart.js.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-4 text-xs">
                <span class="px-4 py-2 rounded-xl bg-[#EAF6F4] border border-[#265D7A]/20 font-mono font-semibold text-[#13293D]">
                    REST API v1 (Sanctum Protected)
                </span>
                <Link
                    :href="authUser ? '/dashboard' : '/login'"
                    class="px-6 py-2.5 rounded-full bg-[#265D7A] hover:bg-[#13293D] text-white font-semibold transition shadow-md shadow-[#13293D]/15"
                >
                    {{ authUser ? 'Dashboard Operasional' : 'Portal Login' }} &rarr;
                </Link>
            </div>
        </div>
        <div class="max-w-7xl mx-auto mt-10 pt-6 border-t border-[#265D7A]/15 flex flex-col sm:flex-row justify-between items-center gap-2 text-[11px] font-medium text-[#265D7A]">
            <span>&copy; {{ new Date().getFullYear() }} Delixie Platform &bull; Web Programming 2 Final Project.</span>
            <span>Visibilitas Rantai Pasok Terintegrasi.</span>
        </div>
    </footer>
</template>
