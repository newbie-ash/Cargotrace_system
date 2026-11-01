<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    shipment: {
        type: Object,
        required: true,
    },
    carriers: {
        type: Array,
        required: true,
    },
});

const defaultArrival = props.shipment.expected_arrival
    ? new Date(props.shipment.expected_arrival).toISOString().slice(0, 16)
    : '';

const form = useForm({
    carrier_id: props.shipment.carrier_id,
    expected_arrival: defaultArrival,
    reference_number: props.shipment.reference_number || '',
    notes: props.shipment.notes || '',
});

const submit = () => {
    form.put(`/shipments/${props.shipment.id}`);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Edit Metadata ${shipment.shipment_number}`" />

        <div class="max-w-4xl mx-auto">
            <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 mb-6 shadow-xs flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold font-mono tracking-tight text-[#1A1A1C]">Edit Metadata: {{ shipment.shipment_number }}</h1>
                    <p class="text-xs text-[#706E6B] mt-0.5">Perbarui carrier, jadwal kedatangan yang diharapkan, dan catatan operasional.</p>
                </div>
                <Link :href="`/shipments/${shipment.id}`" class="px-3 py-1.5 border border-[#D8D2C4] hover:bg-[#F2EFE9] text-xs font-semibold rounded-xl text-[#1A1A1C] transition inline-flex items-center gap-1.5">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Form -->
                <div class="lg:col-span-2 bg-white/80 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 shadow-xs">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Carrier Pelayaran <span class="text-rose-600">*</span></label>
                                <select v-model="form.carrier_id" required class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20">
                                    <option v-for="c in carriers" :key="c.id" :value="c.id">
                                        {{ c.name }} ({{ c.code }})
                                    </option>
                                </select>
                                <div v-if="form.errors.carrier_id" class="text-xs text-rose-600 mt-1">{{ form.errors.carrier_id }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">No. Referensi PO Customer</label>
                                <input v-model="form.reference_number" type="text" class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20" />
                                <div v-if="form.errors.reference_number" class="text-xs text-rose-600 mt-1">{{ form.errors.reference_number }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Estimasi Kedatangan (ETA) <span class="text-rose-600">*</span></label>
                            <input v-model="form.expected_arrival" type="datetime-local" required class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20" />
                            <div v-if="form.errors.expected_arrival" class="text-xs text-rose-600 mt-1">{{ form.errors.expected_arrival }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Catatan Khusus / Instruksi Operasional</label>
                            <textarea v-model="form.notes" rows="4" class="w-full p-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20"></textarea>
                        </div>

                        <div class="pt-4 border-t border-[#E5E0D5] flex justify-end gap-2">
                            <Link :href="`/shipments/${shipment.id}`" class="px-4 py-2 border border-[#D8D2C4] hover:bg-[#F2EFE9] text-xs font-semibold rounded-xl text-[#1A1A1C] transition">
                                Batal
                            </Link>
                            <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-[#1A1A1C] hover:bg-[#2C2C2E] text-white text-xs font-semibold rounded-xl transition shadow-xs disabled:opacity-75">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Immutable Parameters -->
                <div class="bg-white/80 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 shadow-xs space-y-3 text-xs">
                    <h2 class="text-sm font-bold text-[#1A1A1C] pb-2 border-b border-[#E5E0D5]">Atribut Terproteksi (Immutable)</h2>

                    <div class="flex justify-between py-1 border-b border-[#E5E0D5]">
                        <span class="text-[#706E6B]">Nomor Shipment:</span>
                        <span class="font-mono font-bold text-[#1A1A1C]">{{ shipment.shipment_number }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-[#E5E0D5]">
                        <span class="text-[#706E6B]">Pelabuhan Asal:</span>
                        <span class="font-mono text-[#1A1A1C]">{{ shipment.origin_location?.code }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-[#E5E0D5]">
                        <span class="text-[#706E6B]">Pelabuhan Tujuan:</span>
                        <span class="font-mono text-[#1A1A1C]">{{ shipment.destination_location?.code }}</span>
                    </div>
                    <div class="flex justify-between py-1 border-b border-[#E5E0D5]">
                        <span class="text-[#706E6B]">Customer Akun:</span>
                        <span class="text-[#1A1A1C]">{{ shipment.customer?.name }}</span>
                    </div>
                    <div class="flex justify-between py-1">
                        <span class="text-[#706E6B]">Status Terkini:</span>
                        <span class="font-mono font-bold text-[#1A1A1C]">{{ shipment.current_status }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-[11px] leading-relaxed mt-4">
                        <i class="bi bi-shield-lock me-1"></i>
                        Rute asal, tujuan, dan customer terkunci permanen untuk menjaga integritas data logistik.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
