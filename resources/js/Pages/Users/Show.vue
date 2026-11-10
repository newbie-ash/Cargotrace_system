<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`User ${user.name}`" />

        <div class="max-w-2xl mx-auto">
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-6 mb-6 shadow-xl flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-xl font-extrabold tracking-tight text-white">{{ user.name }}</h1>
                        <span
                            :class="[
                                'px-2.5 py-0.5 rounded-md font-mono font-bold text-xs border uppercase',
                                user.role === 'admin'
                                    ? 'bg-purple-500/15 text-purple-300 border-purple-500/30'
                                    : user.role === 'operator'
                                      ? 'bg-cyan-500/15 text-cyan-300 border-cyan-500/30'
                                      : 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
                            ]"
                        >
                            {{ user.role }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-400 mt-0.5 font-mono">{{ user.email }}</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/users" class="px-3.5 py-1.5 border border-white/15 bg-white/5 hover:bg-white/10 text-xs font-semibold rounded-full text-slate-200 transition inline-flex items-center gap-1.5">
                        <i class="bi bi-arrow-left"></i>
                        <span>Kembali</span>
                    </Link>
                    <Link :href="`/users/${user.id}/edit`" class="px-4 py-1.5 bg-white hover:bg-cyan-50 text-slate-950 text-xs font-bold rounded-full transition inline-flex items-center gap-1.5 shadow-lg shadow-white/10">
                        <i class="bi bi-pencil"></i>
                        <span>Edit</span>
                    </Link>
                </div>
            </div>

            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl space-y-4 text-xs">
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-slate-400">Email:</span>
                    <span class="font-mono font-semibold text-white">{{ user.email }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-slate-400">Peran Sistem (RBAC):</span>
                    <span class="font-mono font-bold uppercase text-white">{{ user.role }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-slate-400">Perusahaan:</span>
                    <span class="font-semibold text-white">{{ user.company_name || '-' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-slate-400">Telepon:</span>
                    <span class="font-mono font-semibold text-white">{{ user.phone || '-' }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-slate-400">Total Shipments Terkait:</span>
                    <span class="font-mono font-bold text-cyan-400">{{ user.shipments_count || 0 }} kargo</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
