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
            <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 mb-6 shadow-xs flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold tracking-tight text-[#1A1A1C]">{{ carrier.name }}</h1>
                        <span class="px-2 py-0.5 rounded font-mono font-bold text-xs bg-blue-50 text-blue-700 border border-blue-200">
                            {{ carrier.code }}
                        </span>
                    </div>
                    <p class="text-xs text-[#706E6B] mt-0.5">Detail profil operator logistik.</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/carriers" class="px-3 py-1.5 border border-[#D8D2C4] hover:bg-[#F2EFE9] text-xs font-semibold rounded-xl text-[#1A1A1C] transition inline-flex items-center gap-1.5">
                        <i class="bi bi-arrow-left"></i>
                        <span>Kembali</span>
                    </Link>
                    <Link v-if="isAdmin" :href="`/carriers/${carrier.id}/edit`" class="px-3 py-1.5 bg-[#1A1A1C] hover:bg-[#2C2C2E] text-white text-xs font-semibold rounded-xl transition inline-flex items-center gap-1.5">
                        <i class="bi bi-pencil"></i>
                        <span>Edit</span>
                    </Link>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 shadow-xs space-y-4 text-xs">
                <div class="flex justify-between py-2 border-b border-[#E5E0D5]">
                    <span class="text-[#706E6B]">Moda Transportasi:</span>
                    <span class="font-bold text-[#1A1A1C]">{{ carrier.transport_mode }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-[#E5E0D5]">
                    <span class="text-[#706E6B]">Email Kontak:</span>
                    <span class="font-semibold text-[#1A1A1C]">{{ carrier.contact_email || '-' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-[#E5E0D5]">
                    <span class="text-[#706E6B]">Telepon:</span>
                    <span class="font-semibold text-[#1A1A1C]">{{ carrier.contact_phone || '-' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-[#E5E0D5]">
                    <span class="text-[#706E6B]">Status Operasional:</span>
                    <span v-if="carrier.is_active" class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-[10px]">
                        Aktif
                    </span>
                    <span v-else class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 border border-gray-200 font-bold text-[10px]">
                        Nonaktif
                    </span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-[#706E6B]">Total Shipments Terkait:</span>
                    <span class="font-bold font-mono text-[#1A1A1C]">{{ carrier.shipments_count || 0 }} pengiriman</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
