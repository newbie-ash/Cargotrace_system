<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import DelixieLogo from '@/Components/DelixieLogo.vue';

const props = defineProps({
    status: {
        type: Number,
        default: 404,
    },
    message: {
        type: String,
        default: null,
    },
});

const errorDetails = computed(() => {
    switch (props.status) {
        case 403:
            return {
                badge: 'AKSES DIBATASI (FORBIDDEN)',
                title: 'Otoritas Akses Tidak Mencukupi',
                desc: props.message || 'Peran akun Anda tidak memiliki izin untuk melihat atau memodifikasi sumber daya logistik ini. Silakan hubungi Administrator jika ini merupakan kekeliruan.',
                icon: 'bi-shield-lock-fill',
                iconColor: 'text-amber-600',
            };
        case 404:
            return {
                badge: 'RUTE TIDAK DITEMUKAN (NOT FOUND)',
                title: 'Halaman atau Resi Pengiriman Tidak Ditemukan',
                desc: props.message || 'Koordinat URL atau manifest kargo yang Anda tuju tidak terdaftar dalam basis data pelacakan Delixie. Periksa kembali tautan Anda.',
                icon: 'bi-geo-alt-fill',
                iconColor: 'text-[#265D7A]',
            };
        case 419:
            return {
                badge: 'SESI KEDALUWARSA (PAGE EXPIRED)',
                title: 'Sesi Keamanan Telah Kedaluwarsa',
                desc: props.message || 'Token keamanan CSRF Anda telah usang karena inaktivitas. Silakan muat ulang halaman untuk memperbarui sesi pengiriman.',
                icon: 'bi-hourglass-bottom',
                iconColor: 'text-[#67C7C6]',
            };
        case 503:
            return {
                badge: 'PEMELIHARAAN SISTEM (SERVICE UNAVAILABLE)',
                title: 'Sistem Sedang Dalam Pemeliharaan Berkala',
                desc: props.message || 'Layanan visibilitas kargo sedang dioptimasi untuk peningkatan keandalan. Mohon coba beberapa saat lagi.',
                icon: 'bi-cone-striped',
                iconColor: 'text-amber-500',
            };
        case 500:
        default:
            return {
                badge: 'KENDALA SERVER (INTERNAL ERROR)',
                title: 'Terjadi Kendala Teknis Pada Server',
                desc: props.message || 'Sistem pelacakan menemui hambatan saat memproses permintaan Anda. Tim operasional telemetri telah menerima laporan insiden ini.',
                icon: 'bi-exclamation-octagon-fill',
                iconColor: 'text-rose-600',
            };
    }
});

const reloadPage = () => {
    window.location.reload();
};
</script>

<template>
    <Head :title="`${status} - ${errorDetails.title} | Delixie`" />

    <div class="min-h-screen bg-transparent text-[#13293D] flex flex-col justify-between p-6 relative overflow-hidden font-sans selection:bg-teal-bright selection:text-[#13293D]">
        <!-- Ambient Radial Gradient Background Accents -->
        <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
            <div class="absolute top-10 right-10 w-96 h-96 rounded-full bg-gradient-to-br from-[#13293D] via-[#265D7A] to-[#67C7C6] opacity-30 blur-[90px]"></div>
            <div class="absolute bottom-10 left-10 w-96 h-96 rounded-full bg-[#A7E4DB]/25 blur-[100px]"></div>
        </div>

        <!-- Top Header Navigation -->
        <header class="relative z-10 max-w-5xl mx-auto w-full flex items-center justify-between pt-4">
            <Link href="/" class="hover:opacity-90 transition group">
                <DelixieLogo size-class="w-8 h-8" wordmark-class="font-extrabold text-xl tracking-tight text-[#13293D]" />
            </Link>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/80 border border-white/90 text-[11px] font-mono font-bold text-[#265D7A] shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    TELEMETRY STATUS: MONITORING
                </span>
            </div>
        </header>

        <!-- Main Center Error Liquid Glass Card -->
        <main class="relative z-10 max-w-xl mx-auto w-full my-auto py-8">
            <div
                class="rounded-3xl p-8 sm:p-10 backdrop-blur-2xl relative overflow-hidden shadow-[0_24px_60px_rgba(19,41,61,0.12)] text-center flex flex-col items-center"
                style="
                    background: linear-gradient(135deg, rgba(255, 255, 255, 0.88) 0%, rgba(217, 242, 238, 0.45) 50%, rgba(255, 255, 255, 0.78) 100%);
                    box-shadow: inset 0 1.5px 1px 0 rgba(255, 255, 255, 0.95), inset 0 -2px 6px 0 rgba(103, 199, 198, 0.20), 0 24px 60px -12px rgba(19, 41, 61, 0.14);
                    border: 1px solid rgba(255, 255, 255, 0.95);
                "
            >
                <!-- Liquid Glass Specular Top Highlight Beam -->
                <div class="absolute inset-x-12 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-white/95 to-transparent pointer-events-none" aria-hidden="true"></div>

                <!-- Giant Monogram Status Indicator with Subtle Radar Ring -->
                <div class="relative flex items-center justify-center mb-6">
                    <span class="text-7xl sm:text-8xl font-black font-mono tracking-tighter text-[#13293D] select-none opacity-90 drop-shadow-sm">
                        {{ status }}
                    </span>
                    <div class="absolute -top-2 -right-4 w-9 h-9 rounded-2xl bg-[#EAF6F4] border border-[#265D7A]/20 flex items-center justify-center shadow-xs">
                        <i :class="['bi text-lg', errorDetails.icon, errorDetails.iconColor]" aria-hidden="true"></i>
                    </div>
                </div>

                <!-- Status Badge -->
                <span class="px-3.5 py-1 rounded-full bg-[#13293D] text-[#A7E4DB] font-mono text-[10px] font-bold tracking-wider uppercase mb-3">
                    {{ errorDetails.badge }}
                </span>

                <!-- Title & Description -->
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#13293D] tracking-tight leading-tight mb-2">
                    {{ errorDetails.title }}
                </h1>
                <p class="text-xs sm:text-sm text-[#265D7A] max-w-md leading-relaxed mb-8">
                    {{ errorDetails.desc }}
                </p>

                <!-- Action Button Group -->
                <div class="flex flex-wrap items-center justify-center gap-3 w-full">
                    <button
                        v-if="status === 419"
                        type="button"
                        @click="reloadPage"
                        class="px-6 py-2.5 rounded-full bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold transition-all duration-200 inline-flex items-center gap-2 shadow-md shadow-[#13293D]/15 cursor-pointer"
                    >
                        <i class="bi bi-arrow-clockwise"></i>
                        <span>Muat Ulang Halaman</span>
                    </button>

                    <Link
                        v-else
                        href="/"
                        class="px-6 py-2.5 rounded-full bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold transition-all duration-200 inline-flex items-center gap-2 shadow-md shadow-[#13293D]/15 cursor-pointer"
                    >
                        <i class="bi bi-house-door"></i>
                        <span>Kembali ke Beranda</span>
                    </Link>

                    <Link
                        href="/dashboard"
                        class="px-6 py-2.5 rounded-full bg-white/80 hover:bg-white text-[#13293D] border border-[#265D7A]/20 text-xs font-semibold transition shadow-xs inline-flex items-center gap-2 cursor-pointer"
                    >
                        <i class="bi bi-grid-1x2"></i>
                        <span>Buka Dashboard</span>
                    </Link>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 max-w-5xl mx-auto w-full text-center pb-4 text-[11px] text-[#265D7A] font-medium">
            &copy; {{ new Date().getFullYear() }} Delixie Platform &bull; Global Cargo Visibility &amp; Operational Resilience.
        </footer>
    </div>
</template>
