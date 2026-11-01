<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DashboardCharts from '@/Components/DashboardCharts.vue';

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

const statusBadgeClass = (st) => {
    switch (st) {
        case 'CREATED': return 'bg-gray-100 text-gray-700 border-gray-300';
        case 'BOOKED':
        case 'PICKED_UP': return 'bg-blue-50 text-blue-700 border-blue-200';
        case 'IN_TRANSIT': return 'bg-amber-50 text-amber-800 border-amber-300';
        case 'DELIVERED': return 'bg-emerald-50 text-emerald-700 border-emerald-300';
        case 'CANCELLED': return 'bg-rose-50 text-rose-700 border-rose-300';
        default: return 'bg-gray-50 text-gray-700 border-gray-200';
    }
};
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
                        Platform visibilitas terpadu & pelacakan rantai pasok kargo global interkontinental.
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

        <!-- 8 Dynamic Metric KPI Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <!-- 1. Total Shipments -->
            <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-5 shadow-xs hover:border-[#1A1A1C]/30 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#706E6B]">Total Shipments</span>
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#1A1A1C] font-mono">{{ stats.total_shipments }}</div>
                <div class="text-[11px] text-[#706E6B] mt-1">Kargo terdaftar sistem</div>
            </div>

            <!-- 2. In Transit -->
            <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-5 shadow-xs hover:border-[#1A1A1C]/30 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#706E6B]">In Transit</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class="bi bi-water"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-amber-600 font-mono">{{ stats.in_transit }}</div>
                <div class="text-[11px] text-[#706E6B] mt-1">Sedang dalam pelayaran</div>
            </div>

            <!-- 3. Delivered -->
            <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-5 shadow-xs hover:border-[#1A1A1C]/30 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#706E6B]">Delivered</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-emerald-600 font-mono">{{ stats.delivered }}</div>
                <div class="text-[11px] text-[#706E6B] mt-1">Tuntas tiba di destinasi</div>
            </div>

            <!-- 4. Delayed Shipments -->
            <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-5 shadow-xs hover:border-[#1A1A1C]/30 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#706E6B]">Delayed</span>
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-rose-600 font-mono">{{ stats.delayed }}</div>
                <div class="text-[11px] text-[#706E6B] mt-1">
                    Delay Rate: <strong class="text-rose-700 font-mono">{{ stats.delay_rate }}%</strong>
                </div>
            </div>

            <!-- 5. Open Exceptions -->
            <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-5 shadow-xs hover:border-[#1A1A1C]/30 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#706E6B]">Open Exceptions</span>
                    <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-red-600 font-mono">{{ stats.open_exceptions }}</div>
                <div class="text-[11px] text-[#706E6B] mt-1">
                    Incident Rate: <strong class="text-red-700 font-mono">{{ stats.exception_rate }}%</strong>
                </div>
            </div>

            <!-- 6. Active Carriers -->
            <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-5 shadow-xs hover:border-[#1A1A1C]/30 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#706E6B]">Active Carriers</span>
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="bi bi-truck"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#1A1A1C] font-mono">{{ stats.active_carriers }}</div>
                <div class="text-[11px] text-[#706E6B] mt-1">Armada pelayaran aktif</div>
            </div>

            <!-- 7. Global Hubs -->
            <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-5 shadow-xs hover:border-[#1A1A1C]/30 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#706E6B]">Global Hubs</span>
                    <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#1A1A1C] font-mono">{{ stats.total_locations }}</div>
                <div class="text-[11px] text-[#706E6B] mt-1">Pelabuhan & checkpoint transit</div>
            </div>

            <!-- 8. Users Count -->
            <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-5 shadow-xs hover:border-[#1A1A1C]/30 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-[#706E6B]">Sistem Akun</span>
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#1A1A1C] font-mono">{{ stats.total_users }}</div>
                <div class="text-[11px] text-[#706E6B] mt-1">Admin, operator & customer</div>
            </div>
        </div>

        <!-- Interactive Visual Charts (Chart.js) -->
        <DashboardCharts
            :status-distribution="stats.status_distribution"
            :carrier-distribution="stats.carrier_distribution"
        />

        <!-- Recent Shipments Overview Table -->
        <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 shadow-xs mb-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#E5E0D5]">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-[#1A1A1C] uppercase tracking-wider">Pengiriman Terkini (Recent Shipments)</h2>
                        <p class="text-[11px] text-[#706E6B]">Daftar aktivitas kargo terbaru dalam sistem</p>
                    </div>
                </div>
                <Link href="/shipments" class="text-xs font-semibold text-[#1A1A1C] hover:underline inline-flex items-center gap-1">
                    <span>Lihat Semua</span>
                    <i class="bi bi-arrow-right"></i>
                </Link>
            </div>

            <div v-if="stats.recent_shipments?.length" class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[#E5E0D5] text-[#706E6B] uppercase font-mono text-[10px]">
                            <th class="py-2.5 px-3">No. Shipment</th>
                            <th class="py-2.5 px-3">Carrier</th>
                            <th class="py-2.5 px-3">Rute</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3">Kondisi ETA</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E0D5]">
                        <tr v-for="s in stats.recent_shipments" :key="s.id" class="hover:bg-[#F6F3EC]/50 transition">
                            <td class="py-3 px-3 font-mono font-bold text-[#1A1A1C]">
                                <Link :href="`/shipments/${s.id}`" class="hover:underline">
                                    {{ s.shipment_number }}
                                </Link>
                            </td>
                            <td class="py-3 px-3 text-[#1A1A1C]">
                                {{ s.carrier?.name || '-' }}
                            </td>
                            <td class="py-3 px-3 font-mono text-[11px] text-[#706E6B]">
                                {{ s.origin_location?.code }} &rarr; {{ s.destination_location?.code }}
                            </td>
                            <td class="py-3 px-3">
                                <span :class="['px-2 py-0.5 rounded-md border text-[11px] font-mono font-semibold', statusBadgeClass(s.current_status)]">
                                    {{ s.current_status }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span v-if="s.is_delayed" class="px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-semibold inline-flex items-center gap-1">
                                    <i class="bi bi-clock-history"></i>
                                    <span>DELAY (+{{ s.delay_duration_hours }}j)</span>
                                </span>
                                <span v-else class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-semibold inline-flex items-center gap-1">
                                    <i class="bi bi-check-circle"></i>
                                    <span>ON-TIME</span>
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <Link :href="`/shipments/${s.id}`" class="px-2.5 py-1 text-xs border border-[#D8D2C4] hover:bg-[#F2EFE9] rounded-lg transition inline-flex items-center gap-1">
                                    <span>Detail</span>
                                    <i class="bi bi-chevron-right text-[10px]"></i>
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="py-8 text-center text-xs text-[#706E6B]">
                Belum ada data pengiriman terdaftar.
            </div>
        </div>

        <!-- Recent Audit Trail Feed (For Admin) -->
        <div v-if="isAdmin && stats.recent_audits?.length" class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 shadow-xs">
            <h2 class="text-xs font-bold text-[#1A1A1C] uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="bi bi-shield-check text-emerald-600"></i>
                <span>Aktivitas Audit Trail Terkini</span>
            </h2>

            <div class="divide-y divide-[#E5E0D5] text-xs">
                <div v-for="log in stats.recent_audits" :key="log.id" class="py-2.5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-[#706E6B] text-[11px]">{{ new Date(log.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                        <div>
                            <span class="font-bold text-[#1A1A1C]">{{ log.user?.name || 'System' }}</span>
                            <span class="text-[#706E6B] mx-1">&bull;</span>
                            <span class="font-mono text-[11px] px-1.5 py-0.5 rounded bg-[#F6F3EC] border border-[#E5E0D5] text-[#1A1A1C]">{{ log.action }}</span>
                        </div>
                    </div>
                    <span class="text-[#706E6B] font-mono text-[11px]">{{ log.auditable_type?.split('\\').pop() }} #{{ log.auditable_id }}</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
