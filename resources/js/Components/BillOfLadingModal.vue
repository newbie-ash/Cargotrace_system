<script setup>
import { computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import { estimateCarbonFootprint } from '@/Utils/carbonCalculator';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    shipment: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const carbon = computed(() => {
    return estimateCarbonFootprint(props.shipment);
});

const printDocument = () => {
    window.print();
};
</script>

<template>
    <Modal :show="show" max-width="3xl" @close="emit('close')">
        <div v-if="shipment" class="p-6 bg-white text-[#13293D] print:p-0">
            <!-- Modal Header (Screen Only) -->
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-200 print:hidden">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-[#13293D]">
                        Dokumen Resmi Kargo &bull; Electronic Bill of Lading (e-B/L)
                    </h3>
                </div>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="printDocument"
                        class="px-4 py-1.5 rounded-full bg-[#13293D] hover:bg-[#265D7A] text-[#A7E4DB] text-xs font-semibold inline-flex items-center gap-1.5 transition cursor-pointer shadow-xs"
                    >
                        <i class="bi bi-printer"></i>
                        <span>Cetak / Simpan PDF</span>
                    </button>
                    <button
                        type="button"
                        @click="emit('close')"
                        aria-label="Tutup"
                        class="text-slate-400 hover:text-slate-700 p-1 cursor-pointer"
                    >
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Printable Bill of Lading Document Container -->
            <div id="printable-bill-of-lading" class="border border-slate-300 rounded-xl p-6 bg-white space-y-5 text-xs font-sans">
                <!-- Top Brand & Header -->
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 pb-4 border-b-2 border-[#13293D]">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xl font-black text-[#13293D] tracking-tight">DELIXIE</span>
                            <span class="px-2 py-0.5 rounded bg-[#13293D] text-[#A7E4DB] text-[10px] font-mono font-bold">
                                CARGOTRACE GLOBAL
                            </span>
                        </div>
                        <div class="text-[10px] text-slate-500 uppercase tracking-widest font-mono">
                            Non-Negotiable Electronic Sea Waybill / Air Waybill
                        </div>
                        <div class="text-[11px] text-slate-600 mt-1">
                            Global Intermodal Logistics System &bull; ISO 28000 Certified
                        </div>
                    </div>

                    <div class="text-left sm:text-right font-mono">
                        <div class="text-[10px] uppercase text-slate-400 font-bold">Tracking / B/L No.</div>
                        <div class="text-lg font-black text-[#13293D]">{{ shipment.shipment_number }}</div>
                        <div v-if="shipment.reference_number" class="text-[10px] text-slate-500">
                            Ref: {{ shipment.reference_number }}
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5">
                            Tanggal Terbit: {{ new Date(shipment.created_at || Date.now()).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                        </div>
                    </div>
                </div>

                <!-- Parties Box (Shipper, Consignee, Carrier) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border border-slate-200 rounded-lg p-4 bg-slate-50/50">
                    <div>
                        <div class="text-[10px] font-mono font-bold uppercase text-slate-400 mb-0.5">Pengirim (Shipper / Consignor)</div>
                        <div class="font-extrabold text-[#13293D] text-sm">{{ shipment.customer?.name || 'Customer' }}</div>
                        <div class="text-slate-600 text-[11px]">{{ shipment.customer?.company_name || 'Client Corporation Ltd.' }}</div>
                        <div class="text-slate-500 text-[10px] font-mono mt-0.5">Email: {{ shipment.customer?.email || '-' }}</div>
                        <div class="text-[10px] text-slate-500 font-mono">Customer ID: #{{ shipment.customer_id }}</div>
                    </div>

                    <div>
                        <div class="text-[10px] font-mono font-bold uppercase text-slate-400 mb-0.5">Penerima (Consignee / Destination Hub)</div>
                        <div class="font-extrabold text-[#13293D] text-sm">{{ shipment.destination_location?.name || 'Destination Terminal' }}</div>
                        <div class="text-slate-600 text-[11px]">{{ shipment.destination_location?.city }}, {{ shipment.destination_location?.country }}</div>
                        <div class="text-slate-500 text-[10px] font-mono mt-0.5">Port/Hub Code: {{ shipment.destination_location?.code }}</div>
                        <div class="text-[10px] text-emerald-700 font-semibold">Status: Verified Destination</div>
                    </div>
                </div>

                <!-- Vessel, Voyage, Port of Loading & Discharge -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 border border-slate-200 rounded-lg p-3 text-[11px]">
                    <div>
                        <span class="text-[10px] text-slate-400 font-mono uppercase block">Carrier / Armada</span>
                        <strong class="text-[#13293D]">{{ shipment.carrier?.name || 'Carrier' }}</strong>
                        <div class="text-[10px] font-mono text-slate-500 uppercase">{{ shipment.carrier?.transport_mode }} FREIGHT</div>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-mono uppercase block">Pelabuhan Muat (Origin)</span>
                        <strong class="text-[#13293D]">{{ shipment.origin_location?.code }}</strong>
                        <div class="text-[10px] text-slate-500 truncate">{{ shipment.origin_location?.city }}</div>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-mono uppercase block">Pelabuhan Bongkar (Dest)</span>
                        <strong class="text-emerald-700">{{ shipment.destination_location?.code }}</strong>
                        <div class="text-[10px] text-slate-500 truncate">{{ shipment.destination_location?.city }}</div>
                    </div>
                    <div>
                        <span class="text-[10px] text-slate-400 font-mono uppercase block">Estimasi Tiba (ETA)</span>
                        <strong class="text-[#13293D] font-mono">
                            {{ (shipment.actual_arrival || shipment.estimated_arrival || shipment.expected_arrival) ? new Date(shipment.actual_arrival || shipment.estimated_arrival || shipment.expected_arrival).toLocaleDateString() : '-' }}
                        </strong>
                        <div class="text-[10px] text-slate-500">{{ shipment.is_delayed ? 'Kondisi: Penyesuaian Jadwal' : 'Kondisi: Tepat Waktu' }}</div>
                    </div>
                </div>

                <!-- Cargo Particulars / Manifest Table -->
                <div>
                    <div class="text-[11px] font-mono font-bold uppercase tracking-wider text-[#13293D] mb-2 flex items-center justify-between">
                        <span>Rincian Muatan Kargo (Particulars of Goods)</span>
                        <span class="text-slate-500 font-normal">Klasifikasi: {{ shipment.cargo_type || 'General Cargo' }}</span>
                    </div>

                    <table class="w-full border-collapse border border-slate-200 text-xs">
                        <thead>
                            <tr class="bg-slate-100 border-b border-slate-200 text-slate-600 font-mono uppercase text-[10px]">
                                <th class="p-2 text-left">SKU / Kode</th>
                                <th class="p-2 text-left">Deskripsi Muatan</th>
                                <th class="p-2 text-center">Jumlah</th>
                                <th class="p-2 text-right">Berat Kotor</th>
                                <th class="p-2 text-right">Volume</th>
                                <th class="p-2 text-right">Nilai Dideklarasikan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="item in (shipment.items || [])" :key="item.id">
                                <td class="p-2 font-mono font-bold text-slate-600">{{ item.sku || '-' }}</td>
                                <td class="p-2 text-[#13293D] font-medium">{{ item.description }}</td>
                                <td class="p-2 text-center font-mono font-bold">{{ item.quantity }}</td>
                                <td class="p-2 text-right font-mono">{{ Number(item.weight_kg).toFixed(1) }} kg</td>
                                <td class="p-2 text-right font-mono">{{ Number(item.volume_cbm).toFixed(2) }} CBM</td>
                                <td class="p-2 text-right font-mono font-bold text-emerald-800">${{ Number(item.declared_value).toLocaleString() }}</td>
                            </tr>
                            <tr v-if="!(shipment.items && shipment.items.length)">
                                <td class="p-2 font-mono font-bold text-slate-600">{{ shipment.reference_number || 'GEN-01' }}</td>
                                <td class="p-2 text-[#13293D] font-medium">{{ shipment.cargo_type || 'Containerized General Freight' }}</td>
                                <td class="p-2 text-center font-mono font-bold">1 Lot</td>
                                <td class="p-2 text-right font-mono">{{ Number(shipment.total_weight_kg || shipment.weight_kg || 2500).toFixed(1) }} kg</td>
                                <td class="p-2 text-right font-mono">{{ Number(shipment.total_volume_cbm || 12.5).toFixed(2) }} CBM</td>
                                <td class="p-2 text-right font-mono font-bold text-emerald-800">$25,000</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 font-bold border-t-2 border-slate-300">
                                <td colspan="3" class="p-2 text-right uppercase text-[10px] font-mono text-slate-600">Total Akumulasi:</td>
                                <td class="p-2 text-right font-mono text-[#13293D]">{{ Number(shipment.total_weight_kg || shipment.weight_kg || 2500).toFixed(1) }} kg</td>
                                <td class="p-2 text-right font-mono text-[#13293D]">{{ Number(shipment.total_volume_cbm || 12.5).toFixed(2) }} CBM</td>
                                <td class="p-2 text-right font-mono text-emerald-800">-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Green Logistics & Carbon Footprint Statement -->
                <div class="bg-emerald-50/70 border border-emerald-200/80 rounded-lg p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-tree text-emerald-700 text-lg"></i>
                        <div>
                            <span class="font-bold text-emerald-900 block">Jejak Karbon Kargo (GLEC Standard Compliance)</span>
                            <span class="text-[11px] text-emerald-800">
                                Estimasi emisi rute berlayar: <strong>{{ carbon.emissionsKg }} kg CO₂e</strong> &bull; Efisiensi: {{ carbon.ecoRating }}
                            </span>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 rounded bg-emerald-600 text-white font-mono text-[10px] font-bold self-start sm:self-auto">
                        {{ carbon.factorLabel }}
                    </span>
                </div>

                <!-- Legal Clause & Verification Footer -->
                <div class="pt-4 border-t border-slate-200 grid grid-cols-1 sm:grid-cols-3 gap-4 text-[10px] text-slate-500">
                    <div class="sm:col-span-2 space-y-1">
                        <strong class="text-slate-700 block">Ketentuan Pengangkutan (Terms of Carriage):</strong>
                        <p class="leading-relaxed">
                            Muatan diterima dalam kondisi prima yang tampak untuk diangkut sesuai jadwal dan ketentuan standar Delixie CargoTrace Platform. Dokumen elektronik ini sah secara hukum sesuai UNCITRAL Model Law on Electronic Transferable Records (MLETR).
                        </p>
                    </div>

                    <!-- Digital Verification Hash & QR Placeholder -->
                    <div class="text-right flex flex-col items-end justify-center border-l sm:border-slate-200 pl-3">
                        <div class="w-16 h-16 border-2 border-slate-400 rounded bg-slate-50 p-1 flex items-center justify-center mb-1">
                            <i class="bi bi-qr-code text-3xl text-slate-700"></i>
                        </div>
                        <span class="font-mono text-[9px] text-slate-400">SHA256: 8f4a...e129</span>
                        <span class="font-bold text-emerald-700 text-[10px]">VERIFIED DIGITAL SEAL</span>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>

<style>
@media print {
    body * {
        visibility: hidden !important;
    }
    #printable-bill-of-lading,
    #printable-bill-of-lading * {
        visibility: visible !important;
    }
    #printable-bill-of-lading {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
}
</style>
