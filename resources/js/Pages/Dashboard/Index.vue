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
        case 'CREATED': return 'bg-[#EAF6F4] text-[#13293D] border-[#265D7A]/25';
        case 'BOOKED':
        case 'PICKED_UP': return 'bg-[#67C7C6]/25 text-[#13293D] border-[#265D7A]/30';
        case 'IN_TRANSIT': return 'bg-amber-500/15 text-amber-900 border-amber-600/30';
        case 'DELIVERED': return 'bg-emerald-500/15 text-emerald-900 border-emerald-600/30';
        case 'CANCELLED': return 'bg-rose-500/15 text-rose-900 border-rose-600/30';
        default: return 'bg-[#EAF6F4] text-[#13293D] border-[#265D7A]/25';
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Operational Dashboard" />

        <!-- Header Banner -->
        <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 mb-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)]">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5 mb-1">
                        <h1 class="text-2xl font-extrabold tracking-tight text-[#13293D]">Operational Dashboard</h1>
                        <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold rounded-full bg-[#13293D] text-[#A7E4DB] uppercase tracking-wider">
                            {{ user.role }}
                        </span>
                    </div>
                    <p class="text-xs text-[#265D7A] font-normal">
                        Platform visibilitas terpadu &amp; pelacakan rantai pasok kargo global interkontinental.
                    </p>
                </div>

                <div v-if="isAdmin || isOperator" class="flex gap-2">
                    <Link href="/shipments/create" class="px-5 py-2 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-full transition inline-flex items-center gap-2 shadow-md shadow-[#13293D]/15">
                        <i class="bi bi-plus-lg"></i>
                        <span>Buat Shipment</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- 8 Dynamic Metric KPI Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <!-- 1. Total Shipments -->
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-2xl p-5 shadow-[0_12px_30px_rgba(19,41,61,0.06)] hover:border-[#67C7C6] transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#265D7A]">Total Shipments</span>
                    <div class="w-8 h-8 rounded-xl bg-[#EAF6F4] border border-[#265D7A]/20 text-[#265D7A] flex items-center justify-center">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#13293D] font-mono">{{ stats.total_shipments }}</div>
                <div class="text-[11px] text-[#265D7A] mt-1">Kargo terdaftar sistem</div>
            </div>

            <!-- 2. In Transit -->
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-2xl p-5 shadow-[0_12px_30px_rgba(19,41,61,0.06)] hover:border-[#67C7C6] transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#265D7A]">In Transit</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-500/15 border border-amber-600/25 text-amber-800 flex items-center justify-center">
                        <i class="bi bi-water"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-amber-800 font-mono">{{ stats.in_transit }}</div>
                <div class="text-[11px] text-[#265D7A] mt-1">Sedang dalam pelayaran</div>
            </div>

            <!-- 3. Delivered -->
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-2xl p-5 shadow-[0_12px_30px_rgba(19,41,61,0.06)] hover:border-[#67C7C6] transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#265D7A]">Delivered</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/15 border border-emerald-600/25 text-emerald-800 flex items-center justify-center">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-emerald-800 font-mono">{{ stats.delivered }}</div>
                <div class="text-[11px] text-[#265D7A] mt-1">Tuntas tiba di destinasi</div>
            </div>

            <!-- 4. Delayed Shipments -->
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-2xl p-5 shadow-[0_12px_30px_rgba(19,41,61,0.06)] hover:border-[#67C7C6] transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#265D7A]">Delayed</span>
                    <div class="w-8 h-8 rounded-xl bg-rose-500/15 border border-rose-600/25 text-rose-800 flex items-center justify-center">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-rose-800 font-mono">{{ stats.delayed }}</div>
                <div class="text-[11px] text-[#265D7A] mt-1">
                    Delay Rate: <strong class="text-rose-800 font-mono">{{ stats.delay_rate }}%</strong>
                </div>
            </div>

            <!-- 5. Open Exceptions -->
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-2xl p-5 shadow-[0_12px_30px_rgba(19,41,61,0.06)] hover:border-[#67C7C6] transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#265D7A]">Open Exceptions</span>
                    <div class="w-8 h-8 rounded-xl bg-rose-500/15 border border-rose-600/25 text-rose-800 flex items-center justify-center">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-rose-800 font-mono">{{ stats.open_exceptions }}</div>
                <div class="text-[11px] text-[#265D7A] mt-1">
                    Incident Rate: <strong class="text-rose-800 font-mono">{{ stats.exception_rate }}%</strong>
                </div>
            </div>

            <!-- 6. Active Carriers -->
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-2xl p-5 shadow-[0_12px_30px_rgba(19,41,61,0.06)] hover:border-[#67C7C6] transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#265D7A]">Active Carriers</span>
                    <div class="w-8 h-8 rounded-xl bg-[#EAF6F4] border border-[#265D7A]/20 text-[#265D7A] flex items-center justify-center">
                        <i class="bi bi-truck"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#13293D] font-mono">{{ stats.active_carriers }}</div>
                <div class="text-[11px] text-[#265D7A] mt-1">Armada pelayaran aktif</div>
            </div>

            <!-- 7. Global Hubs -->
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-2xl p-5 shadow-[0_12px_30px_rgba(19,41,61,0.06)] hover:border-[#67C7C6] transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#265D7A]">Global Hubs</span>
                    <div class="w-8 h-8 rounded-xl bg-[#EAF6F4] border border-[#265D7A]/20 text-[#265D7A] flex items-center justify-center">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#13293D] font-mono">{{ stats.total_locations }}</div>
                <div class="text-[11px] text-[#265D7A] mt-1">Pelabuhan &amp; checkpoint transit</div>
            </div>

            <!-- 8. Users Count -->
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-2xl p-5 shadow-[0_12px_30px_rgba(19,41,61,0.06)] hover:border-[#67C7C6] transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#265D7A]">Sistem Akun</span>
                    <div class="w-8 h-8 rounded-xl bg-[#EAF6F4] border border-[#265D7A]/20 text-[#265D7A] flex items-center justify-center">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-[#13293D] font-mono">{{ stats.total_users }}</div>
                <div class="text-[11px] text-[#265D7A] mt-1">Admin, operator &amp; customer</div>
            </div>
        </div>

        <!-- Interactive Visual Charts (Chart.js) -->
        <DashboardCharts
            :status-distribution="stats.status_distribution"
            :carrier-distribution="stats.carrier_distribution"
        />

        <!-- Recent Shipments Overview Table -->
        <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)] mb-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#265D7A]/15">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-[#EAF6F4] border border-[#265D7A]/20 text-[#265D7A] flex items-center justify-center text-xs">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-[#13293D] uppercase tracking-wider">Pengiriman Terkini (Recent Shipments)</h2>
                        <p class="text-[11px] font-normal text-[#265D7A]">Daftar aktivitas kargo terbaru dalam sistem</p>
                    </div>
                </div>
                <Link href="/shipments" class="text-xs font-bold text-[#265D7A] hover:text-[#13293D] inline-flex items-center gap-1 transition">
                    <span>Lihat Semua</span>
                    <i class="bi bi-arrow-right"></i>
                </Link>
            </div>

            <div v-if="stats.recent_shipments?.length" class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-[#265D7A]/15 text-[#265D7A] uppercase font-mono text-[10px]">
                            <th class="py-2.5 px-3">No. Shipment</th>
                            <th class="py-2.5 px-3">Carrier</th>
                            <th class="py-2.5 px-3">Rute</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3">Kondisi ETA</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#265D7A]/10">
                        <tr v-for="s in stats.recent_shipments" :key="s.id" class="hover:bg-white/60 transition">
                            <td class="py-3 px-3 font-mono font-bold text-[#265D7A]">
                                <Link :href="`/shipments/${s.id}`" class="hover:underline text-[#13293D]">
                                    {{ s.shipment_number }}
                                </Link>
                            </td>
                            <td class="py-3 px-3 text-[#13293D] font-semibold">
                                {{ s.carrier?.name || '-' }}
                            </td>
                            <td class="py-3 px-3 font-mono text-[11px] text-[#265D7A] font-semibold">
                                {{ s.origin_location?.code }} &rarr; {{ s.destination_location?.code }}
                            </td>
                            <td class="py-3 px-3">
                                <span :class="['px-2.5 py-0.5 rounded-md border text-[10px] font-mono font-bold', statusBadgeClass(s.current_status)]">
                                    {{ s.current_status }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span v-if="s.is_delayed" class="px-2 py-0.5 rounded-md bg-rose-500/15 text-rose-900 border border-rose-600/30 text-[10px] font-bold inline-flex items-center gap-1">
                                    <i class="bi bi-clock-history"></i>
                                    <span>DELAY (+{{ s.delay_duration_hours }}j)</span>
                                </span>
                                <span v-else class="px-2 py-0.5 rounded-md bg-emerald-500/15 text-emerald-900 border border-emerald-600/30 text-[10px] font-bold inline-flex items-center gap-1">
                                    <i class="bi bi-check-circle"></i>
                                    <span>ON-TIME</span>
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <Link :href="`/shipments/${s.id}`" class="px-3 py-1 text-xs border border-[#265D7A]/20 bg-white/80 hover:bg-[#13293D] hover:text-[#A7E4DB] text-[#13293D] rounded-lg transition inline-flex items-center gap-1 font-semibold">
                                    <span>Detail</span>
                                    <i class="bi bi-chevron-right text-[10px]"></i>
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="py-8 text-center text-xs text-[#265D7A]">
                Belum ada data pengiriman terdaftar.
            </div>
        </div>

        <!-- Recent Audit Trail Feed (For Admin) -->
        <div v-if="isAdmin && stats.recent_audits?.length" class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)]">
            <h2 class="text-xs font-bold text-[#13293D] uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="bi bi-shield-check text-emerald-700"></i>
                <span>Aktivitas Audit Trail Terkini</span>
            </h2>

            <div class="divide-y divide-[#265D7A]/10 text-xs">
                <div v-for="log in stats.recent_audits" :key="log.id" class="py-2.5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-[#265D7A] text-[11px]">{{ new Date(log.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                        <div>
                            <span class="font-bold text-[#13293D]">{{ log.user?.name || 'System' }}</span>
                            <span class="text-[#265D7A]/40 mx-1.5">&bull;</span>
                            <span class="font-mono text-[11px] px-2 py-0.5 rounded bg-[#13293D] text-[#A7E4DB] font-bold">{{ log.action }}</span>
                        </div>
                    </div>
                    <span class="text-[#265D7A] font-mono text-[11px]">{{ log.auditable_type?.split('\\').pop() }} #{{ log.auditable_id }}</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
