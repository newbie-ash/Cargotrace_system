<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-[#031716] via-[#032324] to-[#032F30] text-[#F8FAFC] flex flex-col items-center justify-center p-4 relative overflow-hidden selection:bg-[#0C969C] selection:text-[#031716]">
        <!-- Subtle Ambient Ocean Spotlights -->
        <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-[#0C969C]/12 blur-[140px] pointer-events-none"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 rounded-full bg-[#0A7075]/14 blur-[140px] pointer-events-none"></div>

        <div class="w-full max-w-md relative z-10">
            <!-- Brand Header -->
            <div class="text-center mb-8">
                <Link href="/" class="inline-flex items-center gap-2.5 font-extrabold text-2xl tracking-tight text-white hover:text-[#6BA3BE] transition">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#0C969C] shadow-[0_0_10px_#0C969C]"></span>
                    <span>Delixie</span>
                </Link>
                <p class="text-xs font-light text-[#9ABBC8] mt-1.5">Global Shipment Visibility &amp; Tracking Platform</p>
            </div>

            <!-- Card Box with Frosted Glassmorphism -->
            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 sm:p-8 shadow-[0_16px_40px_0_rgba(0,0,0,0.45)]">
                <slot />
            </div>

            <div class="text-center mt-6 text-xs font-light text-[#6BA3BE]">
                &copy; {{ new Date().getFullYear() }} Delixie &bull; Global Freight Operations
            </div>
        </div>
    </div>
</template>
