<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import DelixieLogo from '@/Components/DelixieLogo.vue';

defineProps({
    authUser: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['scrollTo']);

const isScrolled = ref(false);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 50;
};

const scrollToTop = (e) => {
    if (isScrolled.value) {
        e.preventDefault();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
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
        class="fixed inset-x-0 z-50 flex justify-center px-4 pointer-events-none transition-all duration-500 ease-out"
        :class="isScrolled ? 'top-3' : 'top-5'"
    >
        <nav
            aria-label="Navigasi Utama"
            class="pointer-events-auto w-full rounded-full flex items-center transition-all duration-500 ease-out relative overflow-hidden backdrop-blur-2xl"
            :class="[
                isScrolled
                    ? 'max-w-[56px] h-14 justify-center p-0 shadow-[0_16px_36px_rgba(0,0,0,0.45)] hover:scale-110 hover:shadow-[0_0_24px_rgba(103,199,198,0.6)] cursor-pointer'
                    : 'max-w-4xl justify-between py-2.5 px-6 gap-4 sm:gap-8 shadow-[0_12px_36px_0_rgba(19,41,61,0.12)]'
            ]"
            style="
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.92) 0%, rgba(217, 242, 238, 0.60) 50%, rgba(255, 255, 255, 0.88) 100%);
                box-shadow: inset 0 1.5px 1px 0 rgba(255, 255, 255, 0.95), inset 0 -2px 6px 0 rgba(103, 199, 198, 0.25), 0 16px 38px -8px rgba(19, 41, 61, 0.20);
                border: 1.5px solid rgba(255, 255, 255, 0.95);
            "
        >
            <!-- Liquid Glass Specular Top Highlight Beam -->
            <div
                class="absolute inset-x-4 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-white/95 to-transparent pointer-events-none"
                aria-hidden="true"
            ></div>

            <!-- Logo Delixie (When Scrolled: Centered icon only, clicks to top) -->
            <Link
                href="/"
                @click="scrollToTop"
                :title="isScrolled ? 'Kembali ke atas' : 'Delixie Platform'"
                class="flex items-center justify-center hover:opacity-90 transition group shrink-0"
                :class="isScrolled ? 'w-full h-full' : ''"
            >
                <DelixieLogo
                    :size-class="isScrolled ? 'w-8 h-8' : 'w-7 h-7'"
                    :show-wordmark="!isScrolled"
                    wordmark-class="font-extrabold tracking-tight text-[#13293D] text-base sm:text-lg transition-all duration-300"
                />
            </Link>

            <!-- Nav Links & Auth CTA (Smoothly hidden when scrolled to leave only logo) -->
            <div
                v-show="!isScrolled"
                class="flex items-center justify-between flex-1 gap-4 sm:gap-8 transition-opacity duration-300"
            >
                <!-- Nav Links -->
                <div class="hidden md:flex items-center font-bold text-[#13293D] gap-7 text-xs">
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
                        class="rounded-full bg-[#265D7A] hover:bg-[#13293D] text-white font-semibold transition-all duration-200 inline-flex items-center gap-2 px-5 py-2 text-xs shadow-md shadow-[#13293D]/15 cursor-pointer"
                    >
                        <span>Dashboard</span>
                        <i class="bi bi-arrow-up-right text-[10px]" aria-hidden="true"></i>
                    </Link>
                    <Link
                        v-else
                        href="/login"
                        class="rounded-full bg-[#265D7A] hover:bg-[#13293D] text-white font-semibold transition-all duration-200 inline-flex items-center gap-2 px-5 py-2 text-xs shadow-md shadow-[#13293D]/15 cursor-pointer"
                    >
                        <span>Masuk Portal</span>
                        <i class="bi bi-arrow-up-right text-[10px]" aria-hidden="true"></i>
                    </Link>
                </div>
            </div>
        </nav>
    </header>
</template>
