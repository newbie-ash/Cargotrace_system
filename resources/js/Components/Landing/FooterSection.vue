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
    <!-- SECTION 5: MINIMALIST SINGLE-ROW FOOTER -->
    <footer class="border-t border-white/10 bg-[#001F18]/90 backdrop-blur-2xl py-8 px-6 sm:px-12 text-xs relative z-10">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-6">
            <!-- Left: Logo & Copyright -->
            <div class="flex items-center gap-3">
                <DelixieLogo
                    size-class="w-6 h-6"
                    wordmark-class="font-extrabold text-sm text-[#FAEFDB]"
                    wordmark-text="Delixie Platform"
                />
                <span class="text-[#FAEFDB]/40 font-mono hidden sm:inline">&bull;</span>
                <span class="text-[#FAEFDB]/60 text-xs">
                    &copy; {{ new Date().getFullYear() }} Delixie Platform.
                </span>
            </div>

            <!-- Center: REST API v1 Info -->
            <div class="font-mono text-xs text-[#FAEFDB]/70 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                <span>REST API v1 (Sanctum Protected)</span>
            </div>

            <!-- Right: Portal Login / Dashboard Link Pill -->
            <div>
                <Link
                    :href="authUser ? '/dashboard' : '/login'"
                    class="px-5 py-2.5 rounded-full bg-gradient-to-r from-[#BE2388] to-[#D42B8A] hover:brightness-110 text-white font-bold text-xs transition shadow-md shadow-[#BE2388]/25 inline-flex items-center gap-1.5 cursor-pointer"
                >
                    <span>{{ authUser ? 'Dashboard Operasional' : 'Portal Login' }}</span>
                    <i class="bi bi-arrow-right text-xs" aria-hidden="true"></i>
                </Link>
            </div>
        </div>
    </footer>
</template>
