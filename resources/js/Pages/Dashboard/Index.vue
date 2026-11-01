<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const isAdmin = computed(() => user.value.role === 'admin');
const isOperator = computed(() => user.value.role === 'operator');
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Operational Dashboard" />

        <!-- Header Banner -->
        <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 mb-6 shadow-xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <h1 class="text-2xl font-bold tracking-tight text-[#1A1A1C]">Operational Dashboard</h1>
                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-md bg-[#1A1A1C] text-white uppercase tracking-wider">
                            {{ user.role }}
                        </span>
                    </div>
                    <p class="text-xs text-[#706E6B]">
                        Platform visibilitas & pelacakan rantai pasok kargo global interkontinental.
                    </p>
                </div>

                <div v-if="isAdmin || isOperator" class="flex gap-2">
                    <Link href="/shipments/create" class="px-4 py-2 bg-[#1A1A1C] hover:bg-[#2C2C2E] text-white text-xs font-semibold rounded-xl transition inline-flex items-center gap-2 shadow-xs">
                        <i class="bi bi-plus-lg"></i>
                        <span>Buat Shipment</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-5 shadow-xs hover:border-[#1A1A1C]/30 transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#706E6B]">Total Shipments</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#1A1A1C] font-mono">{{ stats.total_shipments }}</div>
                <div class="text-[11px] text-[#706E6B] mt-1">Order pengiriman terdaftar</div>
            </div>

            <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-5 shadow-xs hover:border-[#1A1A1C]/30 transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#706E6B]">Active Carriers</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i class="bi bi-truck"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#1A1A1C] font-mono">{{ stats.active_carriers }}</div>
                <div class="text-[11px] text-[#706E6B] mt-1">Armada pelayaran aktif</div>
            </div>

            <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-5 shadow-xs hover:border-[#1A1A1C]/30 transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#706E6B]">Global Hubs</span>
                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#1A1A1C] font-mono">{{ stats.total_locations }}</div>
                <div class="text-[11px] text-[#706E6B] mt-1">Pelabuhan & bandara transit</div>
            </div>

            <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-5 shadow-xs hover:border-[#1A1A1C]/30 transition">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#706E6B]">Pengguna Sistem</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#1A1A1C] font-mono">{{ stats.total_users }}</div>
                <div class="text-[11px] text-[#706E6B] mt-1">Admin, operator & customer</div>
            </div>
        </div>

        <!-- Recent Audit Trail Feed (For Admin) -->
        <div v-if="isAdmin && stats.recent_audits?.length" class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 shadow-xs">
            <h2 class="text-sm font-bold text-[#1A1A1C] mb-4 flex items-center gap-2">
                <i class="bi bi-shield-check"></i>
                <span>Aktivitas Audit Log Terkini</span>
            </h2>

            <div class="divide-y divide-[#E5E0D5] text-xs">
                <div v-for="log in stats.recent_audits" :key="log.id" class="py-2.5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-[#706E6B]">{{ new Date(log.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                        <div>
                            <span class="font-bold text-[#1A1A1C]">{{ log.user?.name || 'System' }}</span>
                            <span class="text-[#706E6B] mx-1">&bull;</span>
                            <span class="font-mono text-[11px] px-1.5 py-0.5 rounded bg-gray-100 text-[#1A1A1C]">{{ log.action }}</span>
                        </div>
                    </div>
                    <span class="text-[#706E6B] font-mono text-[11px]">{{ log.auditable_type?.split('\\').pop() }} #{{ log.auditable_id }}</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
