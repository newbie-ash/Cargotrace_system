<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    shipments: {
        type: Object,
        required: true,
    },
    carriers: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const isAdmin = computed(() => user.value.role === 'admin');
const isOperator = computed(() => user.value.role === 'operator');
const isCustomer = computed(() => user.value.role === 'customer');

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const carrierId = ref(props.filters.carrier_id || '');
const delay = ref(props.filters.delay || '');

const filter = () => {
    router.get('/shipments', {
        search: search.value || undefined,
        status: status.value || undefined,
        carrier_id: carrierId.value || undefined,
        delay: delay.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const deleteShipment = (shipment) => {
    if (confirm(`Apakah Anda yakin ingin menghapus shipment ${shipment.shipment_number}?`)) {
        router.delete(`/shipments/${shipment.id}`);
    }
};

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
        <Head title="Shipments & Tracking" />

        <!-- Header -->
        <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 mb-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[#1A1A1C]">Daftar Pengiriman Global (Shipments)</h1>
                <p class="text-xs text-[#706E6B] mt-0.5">Pantau pergerakan kontainer kargo laut & udara secara real-time.</p>
            </div>
            <Link v-if="isAdmin || isOperator" href="/shipments/create" class="px-4 py-2 bg-[#1A1A1C] hover:bg-[#2C2C2E] text-white text-xs font-semibold rounded-xl transition inline-flex items-center gap-2 shadow-xs">
                <i class="bi bi-plus-lg"></i>
                <span>Buat Pengiriman Baru</span>
            </Link>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-4 mb-6 shadow-xs grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="relative lg:col-span-2">
                <i class="bi bi-search absolute left-3.5 top-3 text-[#706E6B]"></i>
                <input 
                    v-model="search" 
                    @keyup.enter="filter"
                    type="text" 
                    placeholder="Cari No. Shipment atau Ref PO..." 
                    class="w-full h-10 pl-9 pr-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20"
                />
            </div>

            <select v-model="status" @change="filter" class="h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20">
                <option value="">Semua Status</option>
                <option value="CREATED">CREATED</option>
                <option value="BOOKED">BOOKED</option>
                <option value="PICKED_UP">PICKED_UP</option>
                <option value="IN_TRANSIT">IN_TRANSIT</option>
                <option value="DELIVERED">DELIVERED</option>
                <option value="CANCELLED">CANCELLED</option>
            </select>

            <select v-model="carrierId" @change="filter" class="h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20">
                <option value="">Semua Carrier</option>
                <option v-for="c in carriers" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>

            <div class="flex gap-2">
                <select v-model="delay" @change="filter" class="flex-1 h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20">
                    <option value="">Semua Jadwal</option>
                    <option value="on_time">On-Time</option>
                    <option value="delayed">Delayed (Terlambat)</option>
                </select>
                <button @click="filter" class="h-10 px-4 bg-[#F2EFE9] hover:bg-[#E5E0D5] border border-[#D8D2C4] rounded-xl text-xs font-semibold text-[#1A1A1C] transition">
                    Filter
                </button>
            </div>
        </div>

        <!-- Shipments Table -->
        <div class="bg-white/80 backdrop-blur-md border border-[#E5E0D5] rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#F2EFE9] border-b border-[#E5E0D5] text-[#706E6B] font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">No. Shipment</th>
                            <th class="py-3 px-4">Rute (Origin &rarr; Dest)</th>
                            <th class="py-3 px-4">Carrier & Kargo</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Kondisi Jadwal</th>
                            <th class="py-3 px-4">Estimasi Tiba</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E0D5]">
                        <tr v-for="shipment in shipments.data" :key="shipment.id" class="hover:bg-white/90 transition">
                            <td class="py-3 px-4">
                                <Link :href="`/shipments/${shipment.id}`" class="font-mono font-bold text-[#1A1A1C] hover:underline">
                                    {{ shipment.shipment_number }}
                                </Link>
                                <div v-if="shipment.reference_number" class="text-[10px] text-[#706E6B] font-mono">Ref: {{ shipment.reference_number }}</div>
                                <div v-if="!isCustomer && shipment.customer" class="text-[10px] text-[#706E6B]">Cust: {{ shipment.customer.name }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-mono font-semibold text-[#1A1A1C] flex items-center gap-1.5">
                                    <span>{{ shipment.origin_location?.code }}</span>
                                    <span class="text-[#706E6B]">&rarr;</span>
                                    <span>{{ shipment.destination_location?.code }}</span>
                                </div>
                                <div class="text-[10px] text-[#706E6B] truncate max-w-[140px]">
                                    {{ shipment.origin_location?.city }} &rarr; {{ shipment.destination_location?.city }}
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-[#1A1A1C]">{{ shipment.carrier?.name || '-' }}</div>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 text-[#706E6B] font-mono">
                                    {{ shipment.cargo_type }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span :class="['px-2 py-0.5 rounded-md border font-mono font-bold text-[10px]', statusBadgeClass(shipment.current_status)]">
                                    {{ shipment.current_status }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <span v-if="shipment.is_delayed" class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200 font-semibold text-[10px] inline-flex items-center gap-1">
                                    <i class="bi bi-clock-history"></i>
                                    <span>Delayed (+{{ shipment.delay_duration_hours }}j)</span>
                                </span>
                                <span v-else class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold text-[10px] inline-flex items-center gap-1">
                                    <i class="bi bi-check-circle"></i>
                                    <span>On-Time</span>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-[#706E6B]">
                                <div class="font-semibold text-[#1A1A1C]">
                                    {{ new Date(shipment.actual_arrival || shipment.estimated_arrival || shipment.expected_arrival).toLocaleDateString() }}
                                </div>
                                <div class="text-[10px]">Exp: {{ new Date(shipment.expected_arrival).toLocaleDateString() }}</div>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <Link :href="`/shipments/${shipment.id}`" class="p-1.5 rounded-lg border border-[#D8D2C4] hover:bg-[#F2EFE9] text-[#1A1A1C]" title="Detail & Timeline">
                                        <i class="bi bi-eye"></i>
                                    </Link>
                                    <Link v-if="isAdmin || isOperator" :href="`/shipments/${shipment.id}/edit`" class="p-1.5 rounded-lg border border-[#D8D2C4] hover:bg-[#F2EFE9] text-[#1A1A1C]" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </Link>
                                    <button v-if="isAdmin" @click="deleteShipment(shipment)" class="p-1.5 rounded-lg border border-[#D8D2C4] hover:bg-rose-50 text-rose-600" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!shipments.data?.length">
                            <td colspan="7" class="py-8 text-center text-[#706E6B]">
                                Tidak ada pengiriman yang sesuai filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="shipments.links?.length > 3" class="p-4 border-t border-[#E5E0D5] flex items-center justify-between text-xs text-[#706E6B]">
                <div>Menampilkan {{ shipments.from || 0 }} - {{ shipments.to || 0 }} dari {{ shipments.total }} pengiriman</div>
                <div class="flex gap-1">
                    <Link 
                        v-for="(link, i) in shipments.links" 
                        :key="i" 
                        :href="link.url || '#'" 
                        :class="['px-3 py-1 rounded-lg border', link.active ? 'bg-[#1A1A1C] text-white border-[#1A1A1C]' : 'border-[#D8D2C4] hover:bg-[#F2EFE9] text-[#1A1A1C]', !link.url ? 'opacity-40 pointer-events-none' : '']"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
