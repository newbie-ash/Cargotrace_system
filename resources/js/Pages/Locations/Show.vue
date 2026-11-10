<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    location: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Lokasi ${location.name}`" />

        <div class="max-w-2xl mx-auto">
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-6 mb-6 shadow-xl flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-xl font-extrabold tracking-tight text-white">{{ location.name }}</h1>
                        <span class="px-2.5 py-0.5 rounded font-mono font-bold text-xs bg-cyan-500/15 text-cyan-300 border border-cyan-500/30">
                            {{ location.code }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">{{ location.city }}, {{ location.country }}</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/locations" class="px-3.5 py-1.5 border border-white/15 bg-white/5 hover:bg-white/10 text-xs font-semibold rounded-full text-slate-200 transition inline-flex items-center gap-1.5">
                        <i class="bi bi-arrow-left"></i>
                        <span>Kembali</span>
                    </Link>
                    <Link v-if="isAdmin" :href="`/locations/${location.id}/edit`" class="px-4 py-1.5 bg-white hover:bg-cyan-50 text-slate-950 text-xs font-bold rounded-full transition inline-flex items-center gap-1.5 shadow-lg shadow-white/10">
                        <i class="bi bi-pencil"></i>
                        <span>Edit</span>
                    </Link>
                </div>
            </div>

            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl space-y-4 text-xs">
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-slate-400">Tipe Fasilitas:</span>
                    <span class="font-mono font-bold text-white">{{ location.type }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-slate-400">Wilayah Geografis:</span>
                    <span class="font-semibold text-white">{{ location.city }}, {{ location.country }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-slate-400">Koordinat GPS:</span>
                    <span class="font-mono font-semibold text-white">{{ location.latitude }}, {{ location.longitude }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-slate-400">Shipments Asal (Origin):</span>
                    <span class="font-mono font-bold text-cyan-400">{{ location.origin_shipments_count || 0 }} kargo</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-slate-400">Shipments Tujuan (Destination):</span>
                    <span class="font-mono font-bold text-cyan-400">{{ location.destination_shipments_count || 0 }} kargo</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
