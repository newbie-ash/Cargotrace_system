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
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 mb-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)] flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-xl font-extrabold tracking-tight text-[#13293D]">{{ carrier.name }}</h1>
                        <span class="px-2.5 py-0.5 rounded font-mono font-bold text-xs bg-[#13293D] text-[#A7E4DB]">
                            {{ carrier.code }}
                        </span>
                    </div>
                    <p class="text-xs font-normal text-[#265D7A] mt-0.5">Detail profil operator logistik.</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/carriers" class="px-3.5 py-1.5 border border-[#265D7A]/20 bg-white/80 hover:bg-white text-xs font-semibold rounded-full text-[#13293D] transition inline-flex items-center gap-1.5">
                        <i class="bi bi-arrow-left"></i>
                        <span>Kembali</span>
                    </Link>
                    <Link v-if="isAdmin" :href="`/carriers/${carrier.id}/edit`" class="px-4 py-1.5 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-full transition inline-flex items-center gap-1.5 shadow-md shadow-[#13293D]/15">
                        <i class="bi bi-pencil"></i>
                        <span>Edit</span>
                    </Link>
                </div>
            </div>

            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)] space-y-4 text-xs">
                <div class="flex justify-between py-2 border-b border-[#265D7A]/10">
                    <span class="text-[#265D7A]">Moda Transportasi:</span>
                    <span class="font-mono font-bold text-[#13293D]">{{ carrier.transport_mode }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-[#265D7A]/10">
                    <span class="text-[#265D7A]">Email Kontak:</span>
                    <span class="font-semibold text-[#13293D]">{{ carrier.contact_email || '-' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-[#265D7A]/10">
                    <span class="text-[#265D7A]">Telepon:</span>
                    <span class="font-mono font-semibold text-[#13293D]">{{ carrier.contact_phone || '-' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-[#265D7A]/10">
                    <span class="text-[#265D7A]">Status Operasional:</span>
                    <span v-if="carrier.is_active" class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-900 border border-emerald-600/30 font-bold text-[10px]">
                        Aktif
                    </span>
                    <span v-else class="px-2.5 py-0.5 rounded-full bg-[#EAF6F4] text-[#265D7A] border border-[#265D7A]/20 font-bold text-[10px]">
                        Nonaktif
                    </span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-[#265D7A]">Total Shipments Terkait:</span>
                    <span class="font-bold font-mono text-[#265D7A]">{{ carrier.shipments_count || 0 }} pengiriman</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
