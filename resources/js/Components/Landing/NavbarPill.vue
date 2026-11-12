<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    authUser: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['scrollTo']);

const isScrolled = ref(false);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 30;
};

onMounted(() => {
    handleScroll();
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <header
        class="fixed inset-x-0 z-50 flex justify-center px-4 pointer-events-none transition-all duration-300 ease-out"
        :class="isScrolled ? 'top-3' : 'top-5'"
    >
        <nav
            aria-label="Navigasi Utama"
            class="pointer-events-auto rounded-full flex items-center justify-between w-full text-[#13293D] transition-all duration-300 ease-out relative overflow-hidden backdrop-blur-2xl"
            :class="[
                isScrolled
                    ? 'max-w-2xl py-1.5 px-4 sm:px-5 gap-3 sm:gap-6 shadow-[0_16px_40px_-6px_rgba(19,41,61,0.15)]'
                    : 'max-w-4xl py-2.5 px-6 gap-4 sm:gap-8 shadow-[0_12px_36px_0_rgba(19,41,61,0.08)]'
            ]"
            style="
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.88) 0%, rgba(217, 242, 238, 0.45) 50%, rgba(255, 255, 255, 0.78) 100%);
                box-shadow: inset 0 1.5px 1px 0 rgba(255, 255, 255, 0.95), inset 0 -2px 6px 0 rgba(103, 199, 198, 0.22), 0 16px 38px -8px rgba(19, 41, 61, 0.12);
                border: 1px solid rgba(255, 255, 255, 0.95);
            "
        >
            <!-- Liquid Glass Specular Top Highlight Beam -->
            <div
                class="absolute inset-x-8 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-white/95 to-transparent pointer-events-none"
                aria-hidden="true"
            ></div>

            <!-- Logo Delixie -->
            <Link
                href="/"
                class="flex items-center gap-2 font-bold tracking-tight hover:text-[#265D7A] transition group shrink-0"
                :class="isScrolled ? 'text-sm sm:text-base' : 'text-base sm:text-lg'"
            >
                <span class="relative flex" :class="isScrolled ? 'h-2 w-2' : 'h-2.5 w-2.5'">
                    <span class="relative inline-flex rounded-full bg-[#67C7C6] shadow-[0_0_10px_#67C7C6]" :class="isScrolled ? 'h-2 w-2' : 'h-2.5 w-2.5'"></span>
                </span>
                <span class="text-[#13293D] font-extrabold tracking-tight">Delixie</span>
            </Link>

            <!-- Nav Links -->
            <div
                class="hidden md:flex items-center font-bold text-[#13293D] transition-all duration-300"
                :class="isScrolled ? 'gap-5 text-[11px]' : 'gap-7 text-xs'"
            >
                <button
                    type="button"
                    @click="emit('scrollTo', 'track')"
                    class="hover:text-[#265D7A] transition cursor-pointer"
                >
                    Cek Resi
                </button>
                <button
                    type="button"
                    @click="emit('scrollTo', 'tariff')"
                    class="hover:text-[#265D7A] transition cursor-pointer"
                >
                    Cek Tarif
                </button>
                <button
                    type="button"
                    @click="emit('scrollTo', 'hubs')"
                    class="hover:text-[#265D7A] transition cursor-pointer"
                >
                    Jaringan Hub
                </button>
                <a
                    href="#bento"
                    class="hover:text-[#265D7A] transition cursor-pointer"
                >
                    Arsitektur
                </a>
            </div>

            <!-- Auth CTA: Solid Ocean Pill -->
            <div class="flex items-center gap-2 shrink-0">
                <Link
                    v-if="authUser"
                    href="/dashboard"
                    class="rounded-full bg-[#265D7A] hover:bg-[#13293D] text-white font-semibold transition-all duration-200 inline-flex items-center gap-2 shadow-md shadow-[#13293D]/15 cursor-pointer"
                    :class="isScrolled ? 'px-4 py-1.5 text-[11px]' : 'px-5 py-2 text-xs'"
                >
                    <span>Dashboard</span>
                    <i class="bi bi-arrow-up-right text-[10px]" aria-hidden="true"></i>
                </Link>
                <Link
                    v-else
                    href="/login"
                    class="rounded-full bg-[#265D7A] hover:bg-[#13293D] text-white font-semibold transition-all duration-200 inline-flex items-center gap-2 shadow-md shadow-[#13293D]/15 cursor-pointer"
                    :class="isScrolled ? 'px-4 py-1.5 text-[11px]' : 'px-5 py-2 text-xs'"
                >
                    <span>Masuk Portal</span>
                    <i class="bi bi-arrow-up-right text-[10px]" aria-hidden="true"></i>
                </Link>
            </div>
        </nav>
    </header>
</template>
