<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import BillOfLadingModal from '@/Components/BillOfLadingModal.vue';
import { estimateCarbonFootprint } from '@/Utils/carbonCalculator';

const props = defineProps({
    documents: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
const showBolModal = ref(false);
const selectedShipment = ref(null);

const filter = () => {
    router.get('/documents', {
        search: search.value || undefined,
        status: status.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilter = () => {
    search.value = '';
    status.value = '';
    router.get('/documents', {}, {
        preserveState: true,
        replace: true,
    });
};

const openDocument = (shipment) => {
    selectedShipment.value = shipment;
    showBolModal.value = true;
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Arsip Dokumen & e-Bill of Lading" />

        <!-- Top Header Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 mb-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight text-[#13293D]">
                        Arsip Dokumen &amp; e-Bill of Lading
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-[#13293D] text-[#A7E4DB] text-xs font-mono font-bold">
                        {{ documents.total || 0 }} Dokumen
                    </span>
                </div>
                <p class="text-xs text-[#265D7A]">
                    Pusat arsip sertifikat legal pengapalan, packing list, dan Electronic Sea/Air Waybill berstempel digital resmi.
                </p>
            </div>

            <!-- Search Input -->
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:w-72">
                    <i class="bi bi-search absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    <input
                        v-model="search"
                        @keyup.enter="filter"
                        type="text"
                        placeholder="Cari No. B/L, Ref, atau Muatan..."
                        class="w-full h-9 pl-8 pr-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-[#13293D] placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-[#67C7C6] focus:border-[#265D7A]"
                    />
                </div>
                <button
                    type="button"
                    @click="filter"
                    class="h-9 px-3 bg-[#265D7A] hover:bg-[#13293D] text-white rounded-xl text-xs font-semibold transition cursor-pointer"
                >
                    Cari
                </button>
            </div>
        </div>

        <!-- Documents Catalog (Crisp White Floating Cards) -->
        <div v-if="documents.data?.length" class="space-y-4">
            <div
                v-for="s in documents.data"
                :key="s.id"
                class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs hover:border-[#67C7C6] transition flex flex-col lg:flex-row lg:items-center justify-between gap-4"
            >
                <!-- Left: B/L Meta & Shipper/Consignee -->
                <div class="space-y-2 min-w-0 max-w-xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-700 text-[10px] font-mono font-bold uppercase">
                            OFFICIAL e-B/L
                        </span>
                        <span class="px-2 py-0.5 rounded-full bg-[#13293D] text-[#A7E4DB] text-[10px] font-mono font-bold uppercase">
                            {{ s.carrier?.transport_mode }} FREIGHT
                        </span>
                        <span class="inline-flex items-center gap-1 text-[10px] text-emerald-700 font-semibold font-mono">
                            <i class="bi bi-shield-check"></i>
                            <span>Verified Digital Seal</span>
                        </span>
                    </div>

                    <div>
                        <div class="text-lg font-black font-mono text-[#13293D]">
                            {{ s.shipment_number }}
                        </div>
                        <div class="text-[11px] text-[#265D7A] font-mono">
                            Ref PO: {{ s.reference_number || '-' }} &bull; Diterbitkan: {{ new Date(s.created_at).toLocaleDateString([], { day: 'numeric', month: 'long', year: 'numeric' }) }}
                        </div>
                    </div>

                    <!-- Shipper -> Consignee Route Strip -->
                    <div class="flex items-center gap-2 text-xs font-mono pt-1">
                        <div class="flex items-center gap-1 font-bold text-[#13293D]">
                            <i class="bi bi-geo-alt-fill text-emerald-600 text-xs"></i>
                            <span>{{ s.origin_location?.code }} ({{ s.origin_location?.city }})</span>
                        </div>
                        <i class="bi bi-arrow-right text-[#265D7A]"></i>
                        <div class="flex items-center gap-1 font-bold text-emerald-800">
                            <i class="bi bi-flag-fill text-rose-600 text-xs"></i>
                            <span>{{ s.destination_location?.code }} ({{ s.destination_location?.city }})</span>
                        </div>
                    </div>
                </div>

                <!-- Center: Cargo Summary Spec -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs font-mono p-3 bg-slate-50 rounded-xl border border-slate-100 shrink-0">
                    <div>
                        <span class="text-[9px] uppercase text-slate-400 block font-bold">Armada</span>
                        <strong class="text-[#13293D] truncate block">{{ s.carrier?.name || '-' }}</strong>
                    </div>
                    <div>
                        <span class="text-[9px] uppercase text-slate-400 block font-bold">Berat &amp; Vol</span>
                        <strong class="text-[#13293D]">{{ Number(s.total_weight_kg).toFixed(0) }} kg &bull; {{ Number(s.total_volume_cbm).toFixed(1) }} CBM</strong>
                    </div>
                    <div>
                        <span class="text-[9px] uppercase text-emerald-800 block font-bold">Emisi Karbon</span>
                        <strong class="text-emerald-900">{{ estimateCarbonFootprint(s).emissionsKg }} kg CO₂</strong>
                    </div>
                </div>

                <!-- Right: Action Buttons -->
                <div class="flex items-center gap-2 self-start lg:self-auto shrink-0">
                    <button
                        type="button"
                        @click="openDocument(s)"
                        class="px-4 py-2 rounded-xl bg-[#13293D] hover:bg-[#265D7A] text-[#A7E4DB] text-xs font-semibold inline-flex items-center gap-2 transition cursor-pointer shadow-xs"
                    >
                        <i class="bi bi-printer"></i>
                        <span>Cetak / e-B/L</span>
                    </button>

                    <Link
                        :href="`/shipments/${s.id}`"
                        class="px-3.5 py-2 rounded-xl border border-slate-200 hover:border-[#67C7C6] bg-slate-50 text-xs font-semibold text-[#13293D] inline-flex items-center gap-1 transition"
                    >
                        <span>Lacak Kargo</span>
                        <i class="bi bi-arrow-right text-[10px]"></i>
                    </Link>
                </div>
            </div>

            <!-- Pagination Bar -->
            <div v-if="documents.links?.length > 3" class="mt-4 bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-[#265D7A]">
                <div>
                    Menampilkan <strong class="text-[#13293D]">{{ documents.from || 0 }}</strong> - <strong class="text-[#13293D]">{{ documents.to || 0 }}</strong> dari <strong class="text-[#13293D]">{{ documents.total }}</strong> dokumen
                </div>
                <div class="flex gap-1 flex-wrap">
                    <template v-for="(link, i) in documents.links" :key="i">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            :class="['px-3 py-1 rounded-lg border font-mono text-xs transition', link.active ? 'bg-[#13293D] text-[#A7E4DB] font-bold border-[#13293D] shadow-2xs' : 'border-slate-200 bg-white hover:bg-slate-50 text-[#13293D]']"
                            v-html="link.label"
                            preserve-state
                        />
                        <span v-else class="px-3 py-1 rounded-lg border border-transparent text-slate-400 font-mono text-xs" v-html="link.label" />
                    </template>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white border border-slate-200/80 rounded-2xl p-8 shadow-xs">
            <EmptyState
                icon="bi-file-earmark-text"
                title="Tidak Ada Dokumen Ditemukan"
                description="Belum ada arsip Bill of Lading atau dokumen manifest yang sesuai dengan pencarian Anda."
            >
                <template #action>
                    <button
                        type="button"
                        @click="resetFilter"
                        class="px-4 py-1.5 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-full transition cursor-pointer shadow-xs"
                    >
                        Reset Pencarian
                    </button>
                </template>
            </EmptyState>
        </div>

        <!-- Electronic Bill of Lading Modal -->
        <BillOfLadingModal
            :show="showBolModal"
            :shipment="selectedShipment"
            @close="showBolModal = false"
        />
    </AuthenticatedLayout>
</template>
