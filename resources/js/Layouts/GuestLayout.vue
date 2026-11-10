<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-cyber-grid bg-slate-950 text-slate-100 flex flex-col items-center justify-center p-4 relative overflow-hidden selection:bg-cyan-400 selection:text-slate-950">
        <!-- Ambient Glowing Spotlights -->
        <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-cyan-500/15 blur-[120px] pointer-events-none"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 rounded-full bg-amber-500/10 blur-[130px] pointer-events-none"></div>

        <div class="w-full max-w-md relative z-10">
            <!-- Brand Header -->
            <div class="text-center mb-8">
                <Link href="/" class="inline-flex items-center gap-2.5 font-extrabold text-2xl tracking-tight text-white hover:text-cyan-400 transition">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 shadow-[0_0_12px_#22d3ee] animate-pulse"></span>
                    <span>CargoTrace</span>
                </Link>
                <p class="text-xs text-slate-400 font-medium mt-1">Global Shipment Visibility & Tracking Platform</p>
            </div>

            <!-- Card Box with Glassmorphism -->
            <div class="bg-slate-900/65 backdrop-blur-2xl border border-white/15 rounded-3xl p-6 sm:p-8 shadow-[0_8px_32px_0_rgba(0,0,0,0.45)]">
                <slot />
            </div>

            <div class="text-center mt-6 text-xs text-slate-500">
                &copy; {{ new Date().getFullYear() }} CargoTrace &bull; Global Freight Operations
            </div>
        </div>
    </div>
</template>
