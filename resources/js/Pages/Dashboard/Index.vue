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
        case 'CREATED': return 'bg-white/10 text-[#D6E6EA] border-white/15';
        case 'BOOKED':
        case 'PICKED_UP': return 'bg-[#0C969C]/15 text-[#7DE2E6] border-[#0C969C]/35';
        case 'IN_TRANSIT': return 'bg-amber-500/15 text-amber-300 border-amber-400/30';
        case 'DELIVERED': return 'bg-emerald-500/15 text-emerald-300 border-emerald-400/30';
        case 'CANCELLED': return 'bg-rose-500/15 text-rose-300 border-rose-400/30';
        default: return 'bg-white/10 text-[#D6E6EA] border-white/15';
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Operational Dashboard" />

        <!-- Header Banner -->
        <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 mb-6 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2.5 mb-1">
                        <h1 class="text-2xl font-extrabold tracking-tight text-white">Operational Dashboard</h1>
                        <span class="px-2.5 py-0.5 text-[10px] font-mono font-bold rounded-full bg-[#0C969C]/15 text-[#7DE2E6] border border-[#0C969C]/35 uppercase tracking-wider">
                            {{ user.role }}
                        </span>
                    </div>
                    <p class="text-xs text-[#9ABBC8] font-light">
                        Platform visibilitas terpadu & pelacakan rantai pasok kargo global interkontinental.
                    </p>
                </div>

                <div v-if="isAdmin || isOperator" class="flex gap-2">
                    <Link href="/shipments/create" class="px-5 py-2 bg-[#274D60] hover:bg-[#0A7075] text-white text-xs font-semibold rounded-full transition inline-flex items-center gap-2 shadow-lg shadow-black/25">
                        <i class="bi bi-plus-lg"></i>
                        <span>Buat Shipment</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- 8 Dynamic Metric KPI Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <!-- 1. Total Shipments -->
            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-2xl p-5 shadow-lg hover:border-[#0C969C]/40 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#9ABBC8]">Total Shipments</span>
                    <div class="w-8 h-8 rounded-xl bg-[#0A7075]/25 border border-[#0C969C]/30 text-[#0C969C] flex items-center justify-center">
                        <i class="bi bi-box-seam"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-white font-mono">{{ stats.total_shipments }}</div>
                <div class="text-[11px] text-[#9ABBC8] mt-1">Kargo terdaftar sistem</div>
            </div>

            <!-- 2. In Transit -->
            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-2xl p-5 shadow-lg hover:border-[#0C969C]/40 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#9ABBC8]">In Transit</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center">
                        <i class="bi bi-water"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-amber-400 font-mono">{{ stats.in_transit }}</div>
                <div class="text-[11px] text-[#9ABBC8] mt-1">Sedang dalam pelayaran</div>
            </div>

            <!-- 3. Delivered -->
            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-2xl p-5 shadow-lg hover:border-[#0C969C]/40 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#9ABBC8]">Delivered</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-emerald-400 font-mono">{{ stats.delivered }}</div>
                <div class="text-[11px] text-[#9ABBC8] mt-1">Tuntas tiba di destinasi</div>
            </div>

            <!-- 4. Delayed Shipments -->
            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-2xl p-5 shadow-lg hover:border-[#0C969C]/40 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#9ABBC8]">Delayed</span>
                    <div class="w-8 h-8 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-400 flex items-center justify-center">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-rose-400 font-mono">{{ stats.delayed }}</div>
                <div class="text-[11px] text-[#9ABBC8] mt-1">
                    Delay Rate: <strong class="text-rose-300 font-mono">{{ stats.delay_rate }}%</strong>
                </div>
            </div>

            <!-- 5. Open Exceptions -->
            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-2xl p-5 shadow-lg hover:border-[#0C969C]/40 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#9ABBC8]">Open Exceptions</span>
                    <div class="w-8 h-8 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-400 flex items-center justify-center">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-rose-400 font-mono">{{ stats.open_exceptions }}</div>
                <div class="text-[11px] text-[#9ABBC8] mt-1">
                    Incident Rate: <strong class="text-rose-300 font-mono">{{ stats.exception_rate }}%</strong>
                </div>
            </div>

            <!-- 6. Active Carriers -->
            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-2xl p-5 shadow-lg hover:border-[#0C969C]/40 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#9ABBC8]">Active Carriers</span>
                    <div class="w-8 h-8 rounded-xl bg-[#032F30] border border-white/10 text-[#6BA3BE] flex items-center justify-center">
                        <i class="bi bi-truck"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-white font-mono">{{ stats.active_carriers }}</div>
                <div class="text-[11px] text-[#9ABBC8] mt-1">Armada pelayaran aktif</div>
            </div>

            <!-- 7. Global Hubs -->
            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-2xl p-5 shadow-lg hover:border-[#0C969C]/40 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#9ABBC8]">Global Hubs</span>
                    <div class="w-8 h-8 rounded-xl bg-[#0A7075]/30 border border-white/10 text-[#7DE2E6] flex items-center justify-center">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-white font-mono">{{ stats.total_locations }}</div>
                <div class="text-[11px] text-[#9ABBC8] mt-1">Pelabuhan & checkpoint transit</div>
            </div>

            <!-- 8. Users Count -->
            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-2xl p-5 shadow-lg hover:border-[#0C969C]/40 transition">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#9ABBC8]">Sistem Akun</span>
                    <div class="w-8 h-8 rounded-xl bg-[#032324] border border-[#0C969C]/30 text-[#0C969C] flex items-center justify-center">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
                <div class="text-2xl font-extrabold text-white font-mono">{{ stats.total_users }}</div>
                <div class="text-[11px] text-[#9ABBC8] mt-1">Admin, operator & customer</div>
            </div>
        </div>

        <!-- Interactive Visual Charts (Chart.js) -->
        <DashboardCharts
            :status-distribution="stats.status_distribution"
            :carrier-distribution="stats.carrier_distribution"
        />

        <!-- Recent Shipments Overview Table -->
        <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl mb-6">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-[#0A7075]/25 border border-[#0C969C]/30 text-[#0C969C] flex items-center justify-center text-xs">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <h2 class="text-xs font-bold text-white uppercase tracking-wider">Pengiriman Terkini (Recent Shipments)</h2>
                        <p class="text-[11px] font-light text-[#9ABBC8]">Daftar aktivitas kargo terbaru dalam sistem</p>
                    </div>
                </div>
                <Link href="/shipments" class="text-xs font-bold text-[#6BA3BE] hover:text-[#0C969C] inline-flex items-center gap-1 transition">
                    <span>Lihat Semua</span>
                    <i class="bi bi-arrow-right"></i>
                </Link>
            </div>

            <div v-if="stats.recent_shipments?.length" class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-white/10 text-[#9ABBC8] uppercase font-mono text-[10px]">
                            <th class="py-2.5 px-3">No. Shipment</th>
                            <th class="py-2.5 px-3">Carrier</th>
                            <th class="py-2.5 px-3">Rute</th>
                            <th class="py-2.5 px-3">Status</th>
                            <th class="py-2.5 px-3">Kondisi ETA</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        <tr v-for="s in stats.recent_shipments" :key="s.id" class="hover:bg-white/[0.04] transition">
                            <td class="py-3 px-3 font-mono font-bold text-[#6BA3BE]">
                                <Link :href="`/shipments/${s.id}`" class="hover:underline">
                                    {{ s.shipment_number }}
                                </Link>
                            </td>
                            <td class="py-3 px-3 text-[#D6E6EA] font-medium">
                                {{ s.carrier?.name || '-' }}
                            </td>
                            <td class="py-3 px-3 font-mono text-[11px] text-[#9ABBC8]">
                                {{ s.origin_location?.code }} &rarr; {{ s.destination_location?.code }}
                            </td>
                            <td class="py-3 px-3">
                                <span :class="['px-2.5 py-0.5 rounded-md border text-[10px] font-mono font-bold', statusBadgeClass(s.current_status)]">
                                    {{ s.current_status }}
                                </span>
                            </td>
                            <td class="py-3 px-3">
                                <span v-if="s.is_delayed" class="px-2 py-0.5 rounded-md bg-rose-500/15 text-rose-300 border border-rose-500/30 text-[10px] font-bold inline-flex items-center gap-1">
                                    <i class="bi bi-clock-history"></i>
                                    <span>DELAY (+{{ s.delay_duration_hours }}j)</span>
                                </span>
                                <span v-else class="px-2 py-0.5 rounded-md bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 text-[10px] font-bold inline-flex items-center gap-1">
                                    <i class="bi bi-check-circle"></i>
                                    <span>ON-TIME</span>
                                </span>
                            </td>
                            <td class="py-3 px-3 text-right">
                                <Link :href="`/shipments/${s.id}`" class="px-3 py-1 text-xs border border-white/15 bg-white/5 hover:bg-white/15 text-white rounded-lg transition inline-flex items-center gap-1 font-medium">
                                    <span>Detail</span>
                                    <i class="bi bi-chevron-right text-[10px]"></i>
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-else class="py-8 text-center text-xs text-[#9ABBC8]">
                Belum ada data pengiriman terdaftar.
            </div>
        </div>

        <!-- Recent Audit Trail Feed (For Admin) -->
        <div v-if="isAdmin && stats.recent_audits?.length" class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl">
            <h2 class="text-xs font-bold text-white uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="bi bi-shield-check text-emerald-400"></i>
                <span>Aktivitas Audit Trail Terkini</span>
            </h2>

            <div class="divide-y divide-white/10 text-xs">
                <div v-for="log in stats.recent_audits" :key="log.id" class="py-2.5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-[#9ABBC8] text-[11px]">{{ new Date(log.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</span>
                        <div>
                            <span class="font-bold text-white">{{ log.user?.name || 'System' }}</span>
                            <span class="text-white/20 mx-1.5">&bull;</span>
                            <span class="font-mono text-[11px] px-2 py-0.5 rounded bg-[#031716]/80 border border-white/10 text-[#7DE2E6]">{{ log.action }}</span>
                        </div>
                    </div>
                    <span class="text-[#9ABBC8] font-mono text-[11px]">{{ log.auditable_type?.split('\\').pop() }} #{{ log.auditable_id }}</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>