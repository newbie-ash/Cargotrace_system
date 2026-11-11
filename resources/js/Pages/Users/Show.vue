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
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 mb-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)] flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-xl font-extrabold tracking-tight text-[#13293D]">{{ user.name }}</h1>
                        <span
                            :class="[
                                'px-2.5 py-0.5 rounded-md font-mono font-bold text-xs border uppercase',
                                user.role === 'admin'
                                    ? 'bg-[#13293D] text-[#A7E4DB] border-[#13293D]'
                                    : user.role === 'operator'
                                      ? 'bg-[#67C7C6]/25 text-[#13293D] border-[#265D7A]/25'
                                      : 'bg-emerald-500/15 text-emerald-900 border-emerald-600/30',
                            ]"
                        >
                            {{ user.role }}
                        </span>
                    </div>
                    <p class="text-xs font-normal text-[#265D7A] mt-0.5 font-mono">{{ user.email }}</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/users" class="px-3.5 py-1.5 border border-[#265D7A]/20 bg-white/80 hover:bg-white text-xs font-semibold rounded-full text-[#13293D] transition inline-flex items-center gap-1.5">
                        <i class="bi bi-arrow-left"></i>
                        <span>Kembali</span>
                    </Link>
                    <Link :href="`/users/${user.id}/edit`" class="px-4 py-1.5 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-full transition inline-flex items-center gap-1.5 shadow-md shadow-[#13293D]/15">
                        <i class="bi bi-pencil"></i>
                        <span>Edit</span>
                    </Link>
                </div>
            </div>

            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)] space-y-4 text-xs">
                <div class="flex justify-between py-2 border-b border-[#265D7A]/10">
                    <span class="text-[#265D7A]">Email:</span>
                    <span class="font-mono font-semibold text-[#13293D]">{{ user.email }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-[#265D7A]/10">
                    <span class="text-[#265D7A]">Peran Sistem (RBAC):</span>
                    <span class="font-mono font-bold uppercase text-[#13293D]">{{ user.role }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-[#265D7A]/10">
                    <span class="text-[#265D7A]">Perusahaan:</span>
                    <span class="font-semibold text-[#13293D]">{{ user.company_name || '-' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-[#265D7A]/10">
                    <span class="text-[#265D7A]">Telepon:</span>
                    <span class="font-mono font-semibold text-[#13293D]">{{ user.phone || '-' }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-[#265D7A]">Total Shipments Terkait:</span>
                    <span class="font-mono font-bold text-[#265D7A]">{{ user.shipments_count || 0 }} kargo</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>