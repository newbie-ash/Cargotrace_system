<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';

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
        <Head title="Shipments & Tracking" />

        <!-- Header -->
        <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 mb-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-[#13293D]">Daftar Pengiriman Global (Shipments)</h1>
                <p class="text-xs font-normal text-[#265D7A] mt-0.5">Pantau pergerakan kontainer kargo laut &amp; udara secara real-time dengan filter multi-parameter.</p>
            </div>
            <Link v-if="isAdmin || isOperator" href="/shipments/create" class="px-5 py-2 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-full transition inline-flex items-center gap-2 shadow-md shadow-[#13293D]/15">
                <i class="bi bi-plus-lg"></i>
                <span>Buat Pengiriman Baru</span>
            </Link>
        </div>

        <!-- Multi-Parameter Search & Filter Panel -->
        <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-4 mb-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)] space-y-3">
            <!-- Tier 1 -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <div class="relative lg:col-span-2">
                    <i class="bi bi-search absolute left-3.5 top-3 text-[#265D7A]"></i>
                    <input
                        v-model="search"
                        @keyup.enter="filter"
                        type="text"
                        placeholder="Cari No. Shipment, Ref, atau Kargo..."
                        class="w-full h-10 pl-9 pr-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]"
                    />
                </div>

                <select v-model="status" @change="filter" class="h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] font-medium focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]">
                    <option value="">Semua Status</option>
                    <option value="CREATED">CREATED</option>
                    <option value="BOOKED">BOOKED</option>
                    <option value="PICKED_UP">PICKED_UP</option>
                    <option value="IN_TRANSIT">IN_TRANSIT</option>
                    <option value="DELIVERED">DELIVERED</option>
                    <option value="CANCELLED">CANCELLED</option>
                </select>

                <select v-model="carrierId" @change="filter" class="h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] font-medium focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]">
                    <option value="">Semua Carrier</option>
                    <option v-for="c in carriers" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>

                <select v-model="delay" @change="filter" class="h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] font-medium focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]">
                    <option value="">Semua Jadwal ETA</option>
                    <option value="on_time">On-Time</option>
                    <option value="delayed">Delayed (Terlambat)</option>
                </select>

                <div class="flex gap-2">
                    <button @click="showAdvancedFilters = !showAdvancedFilters" class="flex-1 h-10 px-3 border border-[#265D7A]/20 bg-white/80 hover:bg-white rounded-xl text-xs font-bold text-[#13293D] transition inline-flex items-center justify-center gap-1.5 cursor-pointer">
                        <i class="bi bi-sliders"></i>
                        <span>Filter Lanjut</span>
                    </button>
                    <button @click="resetFilter" title="Reset Filter" aria-label="Reset Filter" class="h-10 px-3 border border-[#265D7A]/20 bg-white/80 hover:bg-rose-500/15 hover:text-rose-800 hover:border-rose-500/30 rounded-xl text-xs text-[#265D7A] transition cursor-pointer">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>
            </div>

            <!-- Tier 2 -->
            <div v-show="showAdvancedFilters" class="pt-3 border-t border-[#265D7A]/15 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                <div>
                    <label class="block font-bold mb-1 text-[#13293D]">Pelabuhan Asal (Origin)</label>
                    <select v-model="originId" @change="filter" class="w-full h-9 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D]">
                        <option value="">Semua Lokasi Asal</option>
                        <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.code }} - {{ l.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold mb-1 text-[#13293D]">Pelabuhan Tujuan (Destination)</label>
                    <select v-model="destinationId" @change="filter" class="w-full h-9 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D]">
                        <option value="">Semua Lokasi Tujuan</option>
                        <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.code }} - {{ l.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block font-bold mb-1 text-[#13293D]">Berangkat Sejak (Date From)</label>
                    <input v-model="dateFrom" @change="filter" type="date" class="w-full h-9 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D]" />
                </div>

                <div>
                    <label class="block font-bold mb-1 text-[#13293D]">Berangkat Hingga (Date To)</label>
                    <input v-model="dateTo" @change="filter" type="date" class="w-full h-9 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D]" />
                </div>
            </div>
        </div>

        <!-- Shipments Table -->
        <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl overflow-hidden shadow-[0_16px_40px_rgba(19,41,61,0.08)]">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#EAF6F4] border-b border-[#265D7A]/15 text-[#265D7A] font-mono font-bold uppercase tracking-wider text-[10px]">
                            <th @click="toggleSort('shipment_number')" class="py-3.5 px-4 cursor-pointer hover:text-[#13293D] transition">
                                <span class="inline-flex items-center gap-1">
                                    No. Shipment
                                    <i v-if="sortBy === 'shipment_number'" :class="sortDirection === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                </span>
                            </th>
                            <th class="py-3.5 px-4">Carrier &amp; Mode</th>
                            <th class="py-3.5 px-4">Rute (Origin &rarr; Destination)</th>
                            <th v-if="!isCustomer" class="py-3.5 px-4">Customer</th>
                            <th @click="toggleSort('current_status')" class="py-3.5 px-4 cursor-pointer hover:text-[#13293D] transition">
                                <span class="inline-flex items-center gap-1">
                                    Status
                                    <i v-if="sortBy === 'current_status'" :class="sortDirection === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                </span>
                            </th>
                            <th @click="toggleSort('expected_departure')" class="py-3.5 px-4 cursor-pointer hover:text-[#13293D] transition">
                                <span class="inline-flex items-center gap-1">
                                    Keberangkatan
                                    <i v-if="sortBy === 'expected_departure'" :class="sortDirection === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                </span>
                            </th>
                            <th @click="toggleSort('expected_arrival')" class="py-3.5 px-4 cursor-pointer hover:text-[#13293D] transition">
                                <span class="inline-flex items-center gap-1">
                                    Estimasi Tiba (ETA)
                                    <i v-if="sortBy === 'expected_arrival'" :class="sortDirection === 'asc' ? 'bi bi-arrow-up' : 'bi bi-arrow-down'"></i>
                                </span>
                            </th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#265D7A]/10">
                        <tr v-for="s in shipments.data" :key="s.id" class="hover:bg-white/60 transition">
                            <td class="py-3.5 px-4 font-mono font-bold">
                                <Link :href="`/shipments/${s.id}`" class="hover:underline text-[#13293D]">
                                    {{ s.shipment_number }}
                                </Link>
                                <div v-if="s.reference_number" class="text-[10px] text-[#265D7A] font-mono font-normal">Ref: {{ s.reference_number }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-[#13293D]">{{ s.carrier?.name }}</div>
                                <span class="text-[10px] font-mono text-[#265D7A] uppercase">{{ s.carrier?.transport_mode }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5 font-mono text-[11px] font-bold text-[#13293D]">
                                    <span>{{ s.origin_location?.code }}</span>
                                    <i class="bi bi-arrow-right text-[#265D7A]"></i>
                                    <span>{{ s.destination_location?.code }}</span>
                                </div>
                                <div class="text-[10px] text-[#265D7A]">
                                    {{ s.origin_location?.city }} &rarr; {{ s.destination_location?.city }}
                                </div>
                            </td>
                            <td v-if="!isCustomer" class="py-3.5 px-4">
                                <div class="font-semibold text-[#13293D]">{{ s.customer?.name }}</div>
                                <div class="text-[10px] text-[#265D7A]">{{ s.customer?.company_name || 'Individual' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span :class="['px-2.5 py-0.5 rounded-md border font-mono font-bold text-[10px]', statusBadgeClass(s.current_status)]">
                                    {{ s.current_status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-[#265D7A]">
                                {{ new Date(s.expected_departure).toLocaleDateString() }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-mono text-[11px] text-[#13293D] font-semibold">
                                    {{ new Date(s.actual_arrival || s.estimated_arrival || s.expected_arrival).toLocaleDateString() }}
                                </div>
                                <span v-if="s.is_delayed" class="px-1.5 py-0.2 rounded bg-rose-500/15 text-rose-900 border border-rose-600/30 text-[10px] font-bold inline-flex items-center gap-0.5 mt-0.5">
                                    <i class="bi bi-clock-history"></i>
                                    <span>DELAY (+{{ s.delay_duration_hours }}j)</span>
                                </span>
                                <span v-else class="text-[10px] text-emerald-800 font-bold inline-flex items-center gap-0.5 mt-0.5">
                                    <i class="bi bi-check-circle"></i>
                                    <span>ON-TIME</span>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <Link :href="`/shipments/${s.id}`" class="px-2.5 py-1 text-xs border border-[#265D7A]/20 bg-white/80 hover:bg-[#13293D] hover:text-[#A7E4DB] text-[#13293D] rounded-lg transition inline-flex items-center gap-1 font-semibold">
                                        <i class="bi bi-eye"></i>
                                        <span>Detail</span>
                                    </Link>
                                    <Link v-if="isAdmin || isOperator" :href="`/shipments/${s.id}/edit`" class="px-2.5 py-1 text-xs border border-[#265D7A]/20 bg-white/80 hover:bg-[#13293D] hover:text-[#A7E4DB] text-[#13293D] rounded-lg transition font-semibold">
                                        Edit
                                    </Link>
                                    <button v-if="isAdmin" @click="deleteShipment(s)" aria-label="Hapus Shipment" class="px-2 py-1 text-xs text-rose-800 bg-rose-500/15 hover:bg-rose-500/25 border border-rose-600/25 rounded-lg transition cursor-pointer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!shipments.data?.length">
                            <td :colspan="isCustomer ? 7 : 8" class="p-0">
                                <EmptyState
                                    icon="bi-box-seam"
                                    title="Tidak Ada Kargo Ditemukan"
                                    description="Ubah kata kunci pencarian atau sesuaikan filter status, carrier, dan tanggal."
                                >
                                    <template #action>
                                        <button
                                            type="button"
                                            @click="resetFilter"
                                            class="px-4 py-1.5 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-full transition cursor-pointer shadow-sm"
                                        >
                                            Reset Filter
                                        </button>
                                    </template>
                                </EmptyState>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <div v-if="shipments.links?.length > 3" class="p-4 border-t border-[#265D7A]/15 flex items-center justify-between text-xs text-[#265D7A]">
                <div>
                    Menampilkan <strong class="text-[#13293D]">{{ shipments.from || 0 }}</strong> - <strong class="text-[#13293D]">{{ shipments.to || 0 }}</strong> dari <strong class="text-[#13293D]">{{ shipments.total }}</strong> kargo
                </div>
                <div class="flex gap-1">
                    <template v-for="(link, i) in shipments.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="['px-3 py-1 rounded-lg border font-mono text-xs transition', link.active ? 'bg-[#13293D] text-[#A7E4DB] font-bold border-[#13293D] shadow-sm' : 'border-[#265D7A]/20 bg-white/80 hover:bg-white text-[#13293D]']"
                            v-html="link.label"
                            preserve-state
                        />
                        <span v-else class="px-3 py-1 rounded-lg border border-transparent text-[#265D7A]/40 font-mono text-xs" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
