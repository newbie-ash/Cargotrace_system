<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    carrier: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Carrier ${carrier.name}`" />

        <div class="max-w-2xl mx-auto">
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-6 mb-6 shadow-xl flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-xl font-extrabold tracking-tight text-white">{{ carrier.name }}</h1>
                        <span class="px-2.5 py-0.5 rounded font-mono font-bold text-xs bg-cyan-500/15 text-cyan-300 border border-cyan-500/30">
                            {{ carrier.code }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5">Detail profil operator logistik.</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/carriers" class="px-3.5 py-1.5 border border-white/15 bg-white/5 hover:bg-white/10 text-xs font-semibold rounded-full text-slate-200 transition inline-flex items-center gap-1.5">
                        <i class="bi bi-arrow-left"></i>
                        <span>Kembali</span>
                    </Link>
                    <Link v-if="isAdmin" :href="`/carriers/${carrier.id}/edit`" class="px-4 py-1.5 bg-white hover:bg-cyan-50 text-slate-950 text-xs font-bold rounded-full transition inline-flex items-center gap-1.5 shadow-lg shadow-white/10">
                        <i class="bi bi-pencil"></i>
                        <span>Edit</span>
                    </Link>
                </div>
            </div>

            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl space-y-4 text-xs">
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-slate-400">Moda Transportasi:</span>
                    <span class="font-mono font-bold text-white">{{ carrier.transport_mode }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-slate-400">Email Kontak:</span>
                    <span class="font-semibold text-white">{{ carrier.contact_email || '-' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-slate-400">Telepon:</span>
                    <span class="font-mono font-semibold text-white">{{ carrier.contact_phone || '-' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-slate-400">Status Operasional:</span>
                    <span v-if="carrier.is_active" class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 font-bold text-[10px]">
                        Aktif
                    </span>
                    <span v-else class="px-2.5 py-0.5 rounded-full bg-slate-500/20 text-slate-400 border border-slate-400/30 font-bold text-[10px]">
                        Nonaktif
                    </span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-slate-400">Total Shipments Terkait:</span>
                    <span class="font-bold font-mono text-cyan-400">{{ carrier.shipments_count || 0 }} pengiriman</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
