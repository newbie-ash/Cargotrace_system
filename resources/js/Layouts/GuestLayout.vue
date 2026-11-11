<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-cyber-grid bg-[#0B1120] text-slate-100 flex flex-col items-center justify-center p-4 relative overflow-hidden selection:bg-blue-600 selection:text-white">
        <!-- Ambient Glowing Spotlights -->
        <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-blue-600/15 blur-[120px] pointer-events-none"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 rounded-full bg-amber-500/10 blur-[130px] pointer-events-none"></div>

        <div class="w-full max-w-md relative z-10">
            <!-- Brand Header -->
            <div class="text-center mb-8">
                <Link href="/" class="inline-flex items-center gap-2.5 font-black text-2xl tracking-tight text-white hover:text-blue-400 transition">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-600 shadow-[0_0_12px_#2563eb] animate-pulse"></span>
                    <span>Delixie</span>
                </Link>
                <p class="text-xs text-slate-400 font-medium mt-1">Global Shipment Visibility &amp; Tracking Platform</p>
            </div>

            <!-- Card Box with Glassmorphism -->
            <div class="bg-slate-800/40 backdrop-blur-xl border border-white/10 rounded-3xl p-6 sm:p-8 shadow-[0_8px_32px_0_rgba(0,0,0,0.45)]">
                <slot />
            </div>

            <div class="text-center mt-6 text-xs text-slate-500">
                &copy; {{ new Date().getFullYear() }} Delixie &bull; Global Freight Operations
            </div>
        </div>
    </div>
</template>
