<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    carriers: {
        type: Array,
        required: true,
    },
    locations: {
        type: Array,
        required: true,
    },
    customers: {
        type: Array,
        required: true,
    },
});

const defaultDeparture = new Date().toISOString().slice(0, 16);
const defaultArrival = new Date(Date.now() + 14 * 24 * 60 * 60 * 1000).toISOString().slice(0, 16);

const form = useForm({
    customer_id: '',
    carrier_id: '',
    origin_location_id: '',
    destination_location_id: '',
    cargo_type: 'Electronics & Tech',
    expected_departure: defaultDeparture,
    expected_arrival: defaultArrival,
    reference_number: '',
    notes: '',
    items: [
        {
            sku: 'SKU-MICRO-01',
            description: 'Automotive Microcontroller Units',
            quantity: 100,
            weight_kg: 15.5,
            volume_cbm: 0.12,
            declared_value: 15000,
        },
    ],
});

const addItem = () => {
    form.items.push({
        sku: `SKU-${form.items.length + 1}00`,
        description: '',
        quantity: 1,
        weight_kg: 0,
        volume_cbm: 0,
        declared_value: 0,
    });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const totalWeight = computed(() => {
    return form.items.reduce((sum, item) => sum + (Number(item.weight_kg) || 0) * (Number(item.quantity) || 0), 0);
});

const totalVolume = computed(() => {
    return form.items.reduce((sum, item) => sum + (Number(item.volume_cbm) || 0) * (Number(item.quantity) || 0), 0);
});

const totalQuantity = computed(() => {
    return form.items.reduce((sum, item) => sum + (Number(item.quantity) || 0), 0);
});

const totalDeclaredValue = computed(() => {
    return form.items.reduce((sum, item) => sum + (Number(item.declared_value) || 0) * (Number(item.quantity) || 0), 0);
});

const submit = () => {
    form.post('/shipments');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Buat Pengiriman Baru" />

        <div class="max-w-5xl mx-auto">
            <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 mb-6 shadow-xs flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[#1A1A1C]">Registrasi Order Pengiriman Kargo</h1>
                    <p class="text-xs text-[#706E6B] mt-0.5">Input rute asal, tujuan, operator logistik, dan rincian manifest barang.</p>
                </div>
                <Link href="/shipments" class="px-3 py-1.5 border border-[#D8D2C4] hover:bg-[#F2EFE9] text-xs font-semibold rounded-xl text-[#1A1A1C] transition inline-flex items-center gap-1.5">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left: Logistics Routing -->
                    <div class="lg:col-span-2 bg-white/80 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 shadow-xs space-y-4">
                        <h2 class="text-sm font-bold text-[#1A1A1C] pb-2 border-b border-[#E5E0D5]">1. Parameter Rute & Mitra Logistik</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Customer Akun <span class="text-rose-600">*</span></label>
                                <select v-model="form.customer_id" required class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20">
                                    <option value="">Pilih Customer...</option>
                                    <option v-for="c in customers" :key="c.id" :value="c.id">
                                        {{ c.name }} ({{ c.company_name || c.email }})
                                    </option>
                                </select>
                                <div v-if="form.errors.customer_id" class="text-xs text-rose-600 mt-1">{{ form.errors.customer_id }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Carrier Pelayaran <span class="text-rose-600">*</span></label>
                                <select v-model="form.carrier_id" required class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20">
                                    <option value="">Pilih Carrier...</option>
                                    <option v-for="car in carriers" :key="car.id" :value="car.id">
                                        {{ car.name }} ({{ car.code }} &bull; {{ car.transport_mode }})
                                    </option>
                                </select>
                                <div v-if="form.errors.carrier_id" class="text-xs text-rose-600 mt-1">{{ form.errors.carrier_id }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Pelabuhan Asal (Origin) <span class="text-rose-600">*</span></label>
                                <select v-model="form.origin_location_id" required class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20">
                                    <option value="">Pilih Lokasi Asal...</option>
                                    <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                        {{ loc.code }} &bull; {{ loc.name }} ({{ loc.city }})
                                    </option>
                                </select>
                                <div v-if="form.errors.origin_location_id" class="text-xs text-rose-600 mt-1">{{ form.errors.origin_location_id }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Pelabuhan Tujuan (Dest) <span class="text-rose-600">*</span></label>
                                <select v-model="form.destination_location_id" required class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20">
                                    <option value="">Pilih Lokasi Tujuan...</option>
                                    <option v-for="loc in locations" :key="loc.id" :value="loc.id">
                                        {{ loc.code }} &bull; {{ loc.name }} ({{ loc.city }})
                                    </option>
                                </select>
                                <div v-if="form.errors.destination_location_id" class="text-xs text-rose-600 mt-1">{{ form.errors.destination_location_id }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Klasifikasi Kargo <span class="text-rose-600">*</span></label>
                                <select v-model="form.cargo_type" required class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20">
                                    <option value="Container FCL">Container FCL (Full Load)</option>
                                    <option value="Container LCL">Container LCL (Less Container)</option>
                                    <option value="Electronics & Tech">Electronics & Tech</option>
                                    <option value="Perishable / Reefer">Perishable / Reefer</option>
                                    <option value="Hazardous Material">Hazardous Material</option>
                                    <option value="Dry Bulk">Dry Bulk</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">No. Referensi PO Customer</label>
                                <input v-model="form.reference_number" type="text" placeholder="PO-2026-9001" class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Keberangkatan (ETD) <span class="text-rose-600">*</span></label>
                                <input v-model="form.expected_departure" type="datetime-local" required class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20" />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Kedatangan (ETA) <span class="text-rose-600">*</span></label>
                                <input v-model="form.expected_arrival" type="datetime-local" required class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Catatan Operasional / Penanganan Khusus</label>
                            <textarea v-model="form.notes" rows="2" placeholder="Instruksi stowage, penanganan suhu, atau nomor container..." class="w-full p-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20"></textarea>
                        </div>
                    </div>

                    <!-- Right: Live Manifest Metrics -->
                    <div class="bg-white/80 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 shadow-xs flex flex-col justify-between">
                        <div>
                            <h2 class="text-sm font-bold text-[#1A1A1C] pb-2 border-b border-[#E5E0D5] mb-4">Ringkasan Muatan</h2>

                            <div class="grid grid-cols-2 gap-3 text-center mb-4">
                                <div class="p-3 rounded-xl bg-[#F6F3EC] border border-[#E5E0D5]">
                                    <div class="text-[10px] font-bold text-[#706E6B] uppercase">Total Berat</div>
                                    <div class="text-lg font-mono font-extrabold text-[#1A1A1C]">{{ totalWeight.toFixed(2) }} <span class="text-xs font-normal">kg</span></div>
                                </div>
                                <div class="p-3 rounded-xl bg-[#F6F3EC] border border-[#E5E0D5]">
                                    <div class="text-[10px] font-bold text-[#706E6B] uppercase">Total Volume</div>
                                    <div class="text-lg font-mono font-extrabold text-[#1A1A1C]">{{ totalVolume.toFixed(2) }} <span class="text-xs font-normal">CBM</span></div>
                                </div>
                                <div class="p-3 rounded-xl bg-[#F6F3EC] border border-[#E5E0D5]">
                                    <div class="text-[10px] font-bold text-[#706E6B] uppercase">Total Item</div>
                                    <div class="text-lg font-mono font-extrabold text-[#1A1A1C]">{{ totalQuantity }} <span class="text-xs font-normal">unit</span></div>
                                </div>
                                <div class="p-3 rounded-xl bg-[#F6F3EC] border border-[#E5E0D5]">
                                    <div class="text-[10px] font-bold text-[#706E6B] uppercase">Declared Value</div>
                                    <div class="text-lg font-mono font-extrabold text-[#1A1A1C]">${{ totalDeclaredValue.toLocaleString() }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-blue-900 text-[11px] leading-relaxed">
                            <i class="bi bi-info-circle me-1"></i>
                            Nomor shipment unik (<code>CT-YYYY-XXXXXX</code>) dan checkpoint pelacakan awal di lokasi asal dibuat otomatis saat shipment didaftarkan.
                        </div>
                    </div>
                </div>

                <!-- Dynamic Line Items Manifest Builder -->
                <div class="bg-white/80 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 shadow-xs">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#E5E0D5]">
                        <div>
                            <h2 class="text-sm font-bold text-[#1A1A1C]">2. Manifest Barang Kargo (Line Items)</h2>
                            <p class="text-xs text-[#706E6B]">Rincian item muatan yang dikemas dalam pengiriman ini.</p>
                        </div>
                        <button type="button" @click="addItem" class="px-3 py-1.5 bg-[#F2EFE9] hover:bg-[#E5E0D5] border border-[#D8D2C4] rounded-xl text-xs font-semibold text-[#1A1A1C] transition inline-flex items-center gap-1.5">
                            <i class="bi bi-plus-circle"></i>
                            <span>Tambah Item</span>
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-[#F2EFE9] border-b border-[#E5E0D5] text-[#706E6B] font-bold uppercase tracking-wider text-[11px]">
                                    <th class="py-2.5 px-3">SKU</th>
                                    <th class="py-2.5 px-3">Deskripsi Barang <span class="text-rose-600">*</span></th>
                                    <th class="py-2.5 px-3 w-20">Qty <span class="text-rose-600">*</span></th>
                                    <th class="py-2.5 px-3 w-28">Berat (kg)</th>
                                    <th class="py-2.5 px-3 w-28">Volume (CBM)</th>
                                    <th class="py-2.5 px-3 w-28">Nilai ($)</th>
                                    <th class="py-2.5 px-3 w-12 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#E5E0D5]">
                                <tr v-for="(item, idx) in form.items" :key="idx">
                                    <td class="py-2 px-2">
                                        <input v-model="item.sku" type="text" placeholder="SKU-001" class="w-full h-9 px-2 bg-white border border-[#D8D2C4] rounded-lg font-mono text-xs text-[#1A1A1C]" />
                                    </td>
                                    <td class="py-2 px-2">
                                        <input v-model="item.description" type="text" placeholder="Nama komoditas" required class="w-full h-9 px-2 bg-white border border-[#D8D2C4] rounded-lg text-xs text-[#1A1A1C]" />
                                    </td>
                                    <td class="py-2 px-2">
                                        <input v-model.number="item.quantity" type="number" min="1" required class="w-full h-9 px-2 bg-white border border-[#D8D2C4] rounded-lg text-xs text-center font-mono text-[#1A1A1C]" />
                                    </td>
                                    <td class="py-2 px-2">
                                        <input v-model.number="item.weight_kg" type="number" step="0.01" min="0" placeholder="0.00" class="w-full h-9 px-2 bg-white border border-[#D8D2C4] rounded-lg text-xs text-right font-mono text-[#1A1A1C]" />
                                    </td>
                                    <td class="py-2 px-2">
                                        <input v-model.number="item.volume_cbm" type="number" step="0.01" min="0" placeholder="0.00" class="w-full h-9 px-2 bg-white border border-[#D8D2C4] rounded-lg text-xs text-right font-mono text-[#1A1A1C]" />
                                    </td>
                                    <td class="py-2 px-2">
                                        <input v-model.number="item.declared_value" type="number" step="0.01" min="0" placeholder="0.00" class="w-full h-9 px-2 bg-white border border-[#D8D2C4] rounded-lg text-xs text-right font-mono text-[#1A1A1C]" />
                                    </td>
                                    <td class="py-2 px-2 text-center">
                                        <button type="button" @click="removeItem(idx)" :disabled="form.items.length <= 1" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition disabled:opacity-30">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Submit Bar -->
                <div class="flex justify-end gap-3 pt-2">
                    <Link href="/shipments" class="px-5 py-2.5 border border-[#D8D2C4] hover:bg-[#F2EFE9] text-xs font-semibold rounded-xl text-[#1A1A1C] transition">
                        Batal
                    </Link>
                    <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-[#1A1A1C] hover:bg-[#2C2C2E] text-white text-xs font-semibold rounded-xl transition shadow-xs disabled:opacity-75 flex items-center gap-2">
                        <span v-if="form.processing" class="inline-block w-3.5 h-3.5 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                        <i v-else class="bi bi-check2-circle"></i>
                        <span>Registrasikan Shipment</span>
                    </button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
