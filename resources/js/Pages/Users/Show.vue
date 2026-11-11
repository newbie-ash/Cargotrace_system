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
            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 mb-6 shadow-xl flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-xl font-extrabold tracking-tight text-white">{{ user.name }}</h1>
                        <span
                            :class="[
                                'px-2.5 py-0.5 rounded-md font-mono font-bold text-xs border uppercase',
                                user.role === 'admin'
                                    ? 'bg-purple-500/15 text-purple-300 border-purple-400/30'
                                    : user.role === 'operator'
                                      ? 'bg-[#0C969C]/15 text-[#7DE2E6] border-[#0C969C]/35'
                                      : 'bg-emerald-500/15 text-emerald-300 border-emerald-400/30',
                            ]"
                        >
                            {{ user.role }}
                        </span>
                    </div>
                    <p class="text-xs font-light text-[#9ABBC8] mt-0.5 font-mono">{{ user.email }}</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/users" class="px-3.5 py-1.5 border border-white/15 bg-white/5 hover:bg-white/10 text-xs font-semibold rounded-full text-[#D6E6EA] hover:text-white transition inline-flex items-center gap-1.5">
                        <i class="bi bi-arrow-left"></i>
                        <span>Kembali</span>
                    </Link>
                    <Link :href="`/users/${user.id}/edit`" class="px-4 py-1.5 bg-[#274D60] hover:bg-[#0A7075] text-white text-xs font-semibold rounded-full transition inline-flex items-center gap-1.5 shadow-lg shadow-black/25">
                        <i class="bi bi-pencil"></i>
                        <span>Edit</span>
                    </Link>
                </div>
            </div>

            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl space-y-4 text-xs">
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-[#9ABBC8]">Email:</span>
                    <span class="font-mono font-semibold text-white">{{ user.email }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-[#9ABBC8]">Peran Sistem (RBAC):</span>
                    <span class="font-mono font-bold uppercase text-white">{{ user.role }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-[#9ABBC8]">Perusahaan:</span>
                    <span class="font-semibold text-white">{{ user.company_name || '-' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-[#9ABBC8]">Telepon:</span>
                    <span class="font-mono font-semibold text-white">{{ user.phone || '-' }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-[#9ABBC8]">Total Shipments Terkait:</span>
                    <span class="font-mono font-bold text-[#7DE2E6]">{{ user.shipments_count || 0 }} kargo</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>