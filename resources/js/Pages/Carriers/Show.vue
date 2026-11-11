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
            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 mb-6 shadow-xl flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-xl font-extrabold tracking-tight text-white">{{ carrier.name }}</h1>
                        <span class="px-2.5 py-0.5 rounded font-mono font-bold text-xs bg-[#0C969C]/15 text-[#7DE2E6] border border-[#0C969C]/35">
                            {{ carrier.code }}
                        </span>
                    </div>
                    <p class="text-xs font-light text-[#9ABBC8] mt-0.5">Detail profil operator logistik.</p>
                </div>
                <div class="flex gap-2">
                    <Link href="/carriers" class="px-3.5 py-1.5 border border-white/15 bg-white/5 hover:bg-white/10 text-xs font-semibold rounded-full text-[#D6E6EA] hover:text-white transition inline-flex items-center gap-1.5">
                        <i class="bi bi-arrow-left"></i>
                        <span>Kembali</span>
                    </Link>
                    <Link v-if="isAdmin" :href="`/carriers/${carrier.id}/edit`" class="px-4 py-1.5 bg-[#274D60] hover:bg-[#0A7075] text-white text-xs font-semibold rounded-full transition inline-flex items-center gap-1.5 shadow-lg shadow-black/25">
                        <i class="bi bi-pencil"></i>
                        <span>Edit</span>
                    </Link>
                </div>
            </div>

            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl space-y-4 text-xs">
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-[#9ABBC8]">Moda Transportasi:</span>
                    <span class="font-mono font-bold text-white">{{ carrier.transport_mode }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-[#9ABBC8]">Email Kontak:</span>
                    <span class="font-semibold text-white">{{ carrier.contact_email || '-' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-[#9ABBC8]">Telepon:</span>
                    <span class="font-mono font-semibold text-white">{{ carrier.contact_phone || '-' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-white/10">
                    <span class="text-[#9ABBC8]">Status Operasional:</span>
                    <span v-if="carrier.is_active" class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 font-bold text-[10px]">
                        Aktif
                    </span>
                    <span v-else class="px-2.5 py-0.5 rounded-full bg-white/10 text-[#9ABBC8] border border-white/15 font-bold text-[10px]">
                        Nonaktif
                    </span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-[#9ABBC8]">Total Shipments Terkait:</span>
                    <span class="font-bold font-mono text-[#7DE2E6]">{{ carrier.shipments_count || 0 }} pengiriman</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>