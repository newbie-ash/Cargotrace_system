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
            <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 mb-6 shadow-xs flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold tracking-tight text-[#1A1A1C]">{{ user.name }}</h1>
                        <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-purple-50 text-purple-700 border border-purple-200 uppercase">
                            {{ user.role }}
                        </span>
                    </div>
                    <p class="text-xs text-[#706E6B] mt-0.5">{{ user.email }}</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/users" class="px-3 py-1.5 border border-[#D8D2C4] hover:bg-[#F2EFE9] text-xs font-semibold rounded-xl text-[#1A1A1C] transition inline-flex items-center gap-1.5">
                        <i class="bi bi-arrow-left"></i>
                        <span>Kembali</span>
                    </Link>
                    <Link :href="`/users/${user.id}/edit`" class="px-3 py-1.5 bg-[#1A1A1C] hover:bg-[#2C2C2E] text-white text-xs font-semibold rounded-xl transition inline-flex items-center gap-1.5">
                        <i class="bi bi-pencil"></i>
                        <span>Edit</span>
                    </Link>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 shadow-xs space-y-4 text-xs">
                <div class="flex justify-between py-2 border-b border-[#E5E0D5]">
                    <span class="text-[#706E6B]">Email:</span>
                    <span class="font-mono text-[#1A1A1C]">{{ user.email }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-[#E5E0D5]">
                    <span class="text-[#706E6B]">Peran Sistem (RBAC):</span>
                    <span class="font-bold uppercase text-[#1A1A1C]">{{ user.role }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-[#E5E0D5]">
                    <span class="text-[#706E6B]">Perusahaan:</span>
                    <span class="font-semibold text-[#1A1A1C]">{{ user.company_name || '-' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-[#E5E0D5]">
                    <span class="text-[#706E6B]">Telepon:</span>
                    <span class="font-mono text-[#1A1A1C]">{{ user.phone || '-' }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-[#706E6B]">Total Shipments Terkait:</span>
                    <span class="font-mono font-bold text-[#1A1A1C]">{{ user.shipments_count || 0 }} kargo</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
