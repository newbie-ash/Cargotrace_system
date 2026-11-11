<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    authUser: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['scrollTo']);
</script>

<template>
    <header class="fixed top-5 inset-x-0 z-50 flex justify-center px-4 pointer-events-none">
        <nav
            aria-label="Navigasi Utama"
            class="pointer-events-auto backdrop-blur-xl bg-[#032324]/80 border border-white/10 rounded-full px-6 py-2.5 shadow-[0_12px_32px_0_rgba(0,0,0,0.45)] flex items-center justify-between gap-4 sm:gap-8 max-w-4xl w-full text-white transition-all duration-300"
        >
            <!-- Logo Delixie -->
            <Link href="/" class="flex items-center gap-2.5 font-bold text-base sm:text-lg tracking-tight hover:text-[#6BA3BE] transition group">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#0C969C] shadow-[0_0_10px_#0C969C]"></span>
                </span>
                <span class="text-white font-extrabold tracking-tight">Delixie</span>
            </Link>

            <!-- Nav Links -->
            <div class="hidden md:flex items-center gap-7 text-xs font-medium text-[#9ABBC8]">
                <button
                    type="button"
                    @click="emit('scrollTo', 'track')"
                    class="hover:text-white transition cursor-pointer"
                >
                    Cek Resi
                </button>
                <button
                    type="button"
                    @click="emit('scrollTo', 'tariff')"
                    class="hover:text-white transition cursor-pointer"
                >
                    Cek Tarif
                </button>
                <button
                    type="button"
                    @click="emit('scrollTo', 'hubs')"
                    class="hover:text-white transition cursor-pointer"
                >
                    Jaringan Hub
                </button>
                <a
                    href="#bento"
                    class="hover:text-white transition cursor-pointer"
                >
                    Arsitektur
                </a>
            </div>

            <!-- Auth CTA: Minimalist Solid Pill -->
            <div class="flex items-center gap-2">
                <Link
                    v-if="authUser"
                    href="/dashboard"
                    class="px-5 py-2 rounded-full bg-[#274D60] hover:bg-[#0A7075] text-white text-xs font-semibold transition-all duration-200 inline-flex items-center gap-2 shadow-md shadow-black/20 cursor-pointer"
                >
                    <span>Dashboard</span>
                    <i class="bi bi-arrow-up-right text-[11px]" aria-hidden="true"></i>
                </Link>
                <Link
                    v-else
                    href="/login"
                    class="px-5 py-2 rounded-full bg-[#274D60] hover:bg-[#0A7075] text-white text-xs font-semibold transition-all duration-200 inline-flex items-center gap-2 shadow-md shadow-black/20 cursor-pointer"
                >
                    <span>Masuk Portal</span>
                    <i class="bi bi-arrow-up-right text-[11px]" aria-hidden="true"></i>
                </Link>
            </div>
        </nav>
    </header>
</template>
