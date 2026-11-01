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
    locations: {
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

const showAdvancedFilters = ref(false);

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const carrierId = ref(props.filters.carrier_id || '');
const originId = ref(props.filters.origin_id || '');
const destinationId = ref(props.filters.destination_id || '');
const delay = ref(props.filters.delay || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const sortBy = ref(props.filters.sort_by || 'id');
const sortDirection = ref(props.filters.sort_direction || 'desc');

const filter = () => {
    router.get('/shipments', {
        search: search.value || undefined,
        status: status.value || undefined,
        carrier_id: carrierId.value || undefined,
        origin_id: originId.value || undefined,
        destination_id: destinationId.value || undefined,
        delay: delay.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        sort_by: sortBy.value || undefined,
        sort_direction: sortDirection.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilter = () => {
    search.value = '';
    status.value = '';
    carrierId.value = '';
    originId.value = '';
    destinationId.value = '';
    delay.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    sortBy.value = 'id';
    sortDirection.value = 'desc';
    router.get('/shipments', {}, { preserveState: true, replace: true });
};

const toggleSort = (col) => {
    if (sortBy.value === col) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = col;
        sortDirection.value = 'desc';
    }
    filter();
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
                <p class="text-xs text-[#706E6B] mt-0.5">Pantau pergerakan kontainer kargo laut & udara secara real-time dengan filter multi-parameter.</p>
            </div>
            <Link v-if="isAdmin || isOperator" href="/shipments/create" class="px-4 py-2 bg-[#1A1A1C] hover:bg-[#2C2C2E] text-white text-xs font-semibold rounded-xl transition inline-flex items-center gap-2 shadow-xs">
                <i class="bi bi-plus-lg"></i>
                <span>Buat Pengiriman Baru</span>
            </Link>
        </div>

        <!-- Multi-Parameter Search & Filter Panel -->
        <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-4 mb-6 shadow-xs space-y-3">
            <!-- Tier 1: Quick Search & Primary Selectors -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <div class="relative lg:col-span-2">
                    <i class="bi bi-search absolute left-3.5 top-3 text-[#706E6B]"></i>
                    <input 
                        v-model="search" 
                        @keyup.enter="filter"
                        type="text" 
                        placeholder="Cari No. Shipment, Ref, atau Kargo..." 
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

                <select v-model="delay" @change="filter" class="h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20">
                    <option value="">Semua Jadwal ETA</option>
                    <option value="on_time">On-Time</option>
                    <option value="delayed">Delayed (Terlambat)</option>
                </select>

                <div class="flex gap-2">
                    <button @click="showAdvancedFilters = !showAdvancedFilters" class="flex-1 h-10 px-3 border border-[#D8D2C4] hover:bg-[#F2EFE9] rounded-xl text-xs font-semibold text-[#1A1A1C] transition inline-flex items-center justify-center gap-1.5">
                        <i class="bi bi-sliders"></i>
                        <span>Filter Lanjut</span>
                    </button>
                    <button @click="resetFilter" title="Reset Filter" class="h-10 px-3 border border-[#D8D2C4] hover:bg-rose-50 hover:text-rose-600 rounded-xl text-xs text-[#706E6B] transition">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>

            <!-- Tier 2: Advanced Geographic & Date Filters (Collapsible) -->
            <div v-show="showAdvancedFilters" class="pt-3 border-t border-[#E5E0D5] grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div>
                    <label class="block font-semibold mb-1 text-[#706E6B]">Pelabuhan Asal (Origin)</label>
                    <select v-model="originId" @change="filter" class="w-full h-9 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs">
                        <option value="">Semua Lokasi Asal</option>
                        <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.code }} - {{ l.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1 text-[#706E6B]">Pelabuhan Tujuan (Destination)</label>
                    <select v-model="destinationId" @change="filter" class="w-full h-9 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs">
                        <option value="">Semua Lokasi Tujuan</option>
                        <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.code }} - {{ l.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold mb-1 text-[#706E6B]">Berangkat Sejak (Date From)</label>
                    <input v-model="dateFrom" @change="filter" type="date" class="w-full h-9 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs" />
                </div>

                <div>
                    <label class="block font-semibold mb-1 text-[#706E6B]">Berangkat Hingga (Date To)</label>
                    <input v-model="dateTo" @change="filter" type="date" class="w-full h-9 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs" />
                </div>
            </div>
        </div>

        <!-- Shipments Table -->
        <div class="bg-white/80 backdrop-blur-md border border-[#E5E0D5] rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#F2EFE9] border-b border-[#E5E0D5] text-[#706E6B] font-bold uppercase tracking-wider text-[11px]">
                            <th @click="toggleSort('shipment_number')" class="py-3 px-4 cursor-pointer hover:text-black">
                                <span class="inline-flex items-center gap-1">
                                    No. Shipment
                                    <i v-if="sortBy === 'shipment_number'" :class="sortDirection === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                </span>
                            </th>
                            <th class="py-3 px-4">Carrier & Mode</th>
                            <th class="py-3 px-4">Rute (Origin &rarr; Destination)</th>
                            <th v-if="!isCustomer" class="py-3 px-4">Customer</th>
                            <th @click="toggleSort('current_status')" class="py-3 px-4 cursor-pointer hover:text-black">
                                <span class="inline-flex items-center gap-1">
                                    Status
                                    <i v-if="sortBy === 'current_status'" :class="sortDirection === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                </span>
                            </th>
                            <th @click="toggleSort('expected_departure')" class="py-3 px-4 cursor-pointer hover:text-black">
                                <span class="inline-flex items-center gap-1">
                                    Keberangkatan
                                    <i v-if="sortBy === 'expected_departure'" :class="sortDirection === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                </span>
                            </th>
                            <th @click="toggleSort('expected_arrival')" class="py-3 px-4 cursor-pointer hover:text-black">
                                <span class="inline-flex items-center gap-1">
                                    Estimasi Tiba (ETA)
                                    <i v-if="sortBy === 'expected_arrival'" :class="sortDirection === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                </span>
                            </th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E0D5]">
                        <tr v-for="s in shipments.data" :key="s.id" class="hover:bg-[#F6F3EC]/50 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-[#1A1A1C]">
                                <Link :href="`/shipments/${s.id}`" class="hover:underline text-blue-800">
                                    {{ s.shipment_number }}
                                </Link>
                                <div v-if="s.reference_number" class="text-[10px] text-[#706E6B] font-mono">Ref: {{ s.reference_number }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-[#1A1A1C]">{{ s.carrier?.name }}</div>
                                <span class="text-[10px] font-mono text-[#706E6B] uppercase">{{ s.carrier?.transport_mode }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1 font-mono text-[11px] font-semibold text-[#1A1A1C]">
                                    <span>{{ s.origin_location?.code }}</span>
                                    <i class="bi bi-arrow-right text-[#706E6B]"></i>
                                    <span>{{ s.destination_location?.code }}</span>
                                </div>
                                <div class="text-[10px] text-[#706E6B]">
                                    {{ s.origin_location?.city }} &rarr; {{ s.destination_location?.city }}
                                </div>
                            </td>
                            <td v-if="!isCustomer" class="py-3.5 px-4">
                                <div class="font-semibold text-[#1A1A1C]">{{ s.customer?.name }}</div>
                                <div class="text-[10px] text-[#706E6B]">{{ s.customer?.company_name || 'Individual' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span :class="['px-2.5 py-0.5 rounded-md border font-mono font-bold text-[11px]', statusBadgeClass(s.current_status)]">
                                    {{ s.current_status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-[#706E6B]">
                                {{ new Date(s.expected_departure).toLocaleDateString() }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-mono text-[11px] text-[#1A1A1C]">
                                    {{ new Date(s.actual_arrival || s.estimated_arrival || s.expected_arrival).toLocaleDateString() }}
                                </div>
                                <span v-if="s.is_delayed" class="px-1.5 py-0.2 rounded bg-rose-50 text-rose-700 border border-rose-200 text-[10px] font-semibold inline-flex items-center gap-0.5">
                                    <i class="bi bi-clock-history"></i>
                                    <span>DELAY (+{{ s.delay_duration_hours }}j)</span>
                                </span>
                                <span v-else class="text-[10px] text-emerald-700 font-semibold inline-flex items-center gap-0.5">
                                    <i class="bi bi-check-circle"></i>
                                    <span>ON-TIME</span>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link :href="`/shipments/${s.id}`" class="px-2.5 py-1 text-xs border border-[#D8D2C4] hover:bg-[#F2EFE9] text-[#1A1A1C] rounded-lg transition inline-flex items-center gap-1 font-medium">
                                        <i class="bi bi-eye"></i>
                                        <span>Detail</span>
                                    </Link>
                                    <Link v-if="isAdmin || isOperator" :href="`/shipments/${s.id}/edit`" class="px-2.5 py-1 text-xs border border-[#D8D2C4] hover:bg-[#F2EFE9] text-[#1A1A1C] rounded-lg transition font-medium">
                                        Edit
                                    </Link>
                                    <button v-if="isAdmin" @click="deleteShipment(s)" class="px-2 py-1 text-xs text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!shipments.data?.length">
                            <td :colspan="isCustomer ? 7 : 8" class="py-12 text-center text-[#706E6B]">
                                <i class="bi bi-inbox text-3xl text-[#8E8B85] mb-2 block"></i>
                                <span class="font-semibold text-[#1A1A1C]">Tidak ada kargo yang sesuai filter</span>
                                <p class="text-[11px] text-[#706E6B] mt-0.5">Coba ubah kata kunci pencarian atau reset parameter filter.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div v-if="shipments.links?.length > 3" class="p-4 border-t border-[#E5E0D5] flex items-center justify-between text-xs text-[#706E6B]">
                <div>
                    Menampilkan <strong class="text-[#1A1A1C]">{{ shipments.from || 0 }}</strong> - <strong class="text-[#1A1A1C]">{{ shipments.to || 0 }}</strong> dari <strong class="text-[#1A1A1C]">{{ shipments.total }}</strong> kargo
                </div>
                <div class="flex gap-1">
                    <template v-for="(link, i) in shipments.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="['px-3 py-1 rounded-lg border font-mono', link.active ? 'bg-[#1A1A1C] text-white border-[#1A1A1C]' : 'border-[#D8D2C4] hover:bg-[#F2EFE9] text-[#1A1A1C]']"
                            v-html="link.label"
                            preserve-state
                        />
                        <span v-else class="px-3 py-1 rounded-lg border border-transparent text-[#706E6B]/50 font-mono" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
