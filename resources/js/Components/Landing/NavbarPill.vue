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
    <header class="fixed top-4 inset-x-0 z-50 flex justify-center px-4 pointer-events-none">
        <nav
            aria-label="Navigasi Utama"
            class="pointer-events-auto backdrop-blur-2xl bg-slate-900/80 border border-white/10 rounded-full px-5 py-2.5 shadow-[0_8px_32px_0_rgba(0,0,0,0.5)] flex items-center justify-between gap-4 sm:gap-8 max-w-4xl w-full text-white transition-all duration-300"
        >
            <!-- Logo Delixie -->
            <Link href="/" class="flex items-center gap-2.5 font-black text-base sm:text-lg tracking-tight hover:text-blue-400 transition group">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-500 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-600 shadow-[0_0_10px_#2563eb]"></span>
                </span>
                <span class="bg-gradient-to-r from-white via-slate-100 to-blue-200 bg-clip-text text-transparent">Delixie</span>
            </Link>

            <!-- Nav Links -->
            <div class="hidden md:flex items-center gap-6 text-xs font-semibold text-slate-300">
                <button
                    type="button"
                    @click="emit('scrollTo', 'track')"
                    class="hover:text-blue-400 transition cursor-pointer"
                >
                    Cek Resi
                </button>
                <button
                    type="button"
                    @click="emit('scrollTo', 'tariff')"
                    class="hover:text-blue-400 transition cursor-pointer"
                >
                    Cek Tarif
                </button>
                <button
                    type="button"
                    @click="emit('scrollTo', 'hubs')"
                    class="hover:text-blue-400 transition cursor-pointer"
                >
                    Jaringan Hub
                </button>
                <a
                    href="#bento"
                    class="hover:text-blue-400 transition cursor-pointer"
                >
                    Arsitektur
                </a>
            </div>

            <!-- Auth CTA -->
            <div class="flex items-center gap-2">
                <Link
                    v-if="authUser"
                    href="/dashboard"
                    class="px-4 py-1.5 rounded-full bg-white text-slate-950 hover:bg-blue-50 text-xs font-bold transition inline-flex items-center gap-1.5 shadow-md shadow-white/10 cursor-pointer"
                >
                    <span>Dashboard</span>
                    <i class="bi bi-arrow-up-right text-[11px]" aria-hidden="true"></i>
                </Link>
                <Link
                    v-else
                    href="/login"
                    class="px-4 py-1.5 rounded-full bg-white text-slate-950 hover:bg-blue-50 text-xs font-bold transition inline-flex items-center gap-1.5 shadow-md shadow-white/10 cursor-pointer"
                >
                    <span>Masuk Portal</span>
                    <i class="bi bi-arrow-up-right text-[11px]" aria-hidden="true"></i>
                </Link>
            </div>
        </nav>
    </header>
</template>
