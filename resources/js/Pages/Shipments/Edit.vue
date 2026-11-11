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
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 mb-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)] flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold font-mono tracking-tight text-[#13293D]">Edit Metadata: {{ shipment.shipment_number }}</h1>
                    <p class="text-xs font-normal text-[#265D7A] mt-0.5">Perbarui carrier, jadwal kedatangan yang diharapkan, dan catatan operasional.</p>
                </div>
                <Link :href="`/shipments/${shipment.id}`" class="px-3.5 py-1.5 border border-[#265D7A]/20 bg-white/80 hover:bg-white text-xs font-semibold rounded-full text-[#13293D] transition inline-flex items-center gap-1.5">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Form -->
                <div class="lg:col-span-2 bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)]">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#13293D] mb-1">Carrier Pelayaran <span class="text-rose-600">*</span></label>
                                <select v-model="form.carrier_id" required class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]">
                                    <option v-for="c in carriers" :key="c.id" :value="c.id">
                                        {{ c.name }} ({{ c.code }})
                                    </option>
                                </select>
                                <div v-if="form.errors.carrier_id" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.carrier_id }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-[#13293D] mb-1">No. Referensi PO Customer</label>
                                <input v-model="form.reference_number" type="text" class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                                <div v-if="form.errors.reference_number" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.reference_number }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Estimasi Kedatangan (ETA) <span class="text-rose-600">*</span></label>
                            <input v-model="form.expected_arrival" type="datetime-local" required class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                            <div v-if="form.errors.expected_arrival" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.expected_arrival }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Catatan Khusus / Instruksi Operasional</label>
                            <textarea v-model="form.notes" rows="4" class="w-full p-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]"></textarea>
                        </div>

                        <div class="pt-4 border-t border-[#265D7A]/15 flex justify-end gap-2">
                            <Link :href="`/shipments/${shipment.id}`" class="px-4 py-2 border border-[#265D7A]/20 bg-white/80 hover:bg-white text-xs font-semibold rounded-full text-[#13293D] transition">
                                Batal
                            </Link>
                            <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-full transition shadow-md shadow-[#13293D]/15 disabled:opacity-60 cursor-pointer">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Immutable Parameters -->
                <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)] space-y-3 text-xs">
                    <h2 class="text-sm font-bold text-[#13293D] pb-2 border-b border-[#265D7A]/15">Atribut Terproteksi (Immutable)</h2>

                    <div class="flex justify-between py-1.5 border-b border-[#265D7A]/10">
                        <span class="text-[#265D7A]">Nomor Shipment:</span>
                        <span class="font-mono font-bold text-[#13293D]">{{ shipment.shipment_number }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-[#265D7A]/10">
                        <span class="text-[#265D7A]">Pelabuhan Asal:</span>
                        <span class="font-mono font-bold text-[#13293D]">{{ shipment.origin_location?.code }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-[#265D7A]/10">
                        <span class="text-[#265D7A]">Pelabuhan Tujuan:</span>
                        <span class="font-mono font-bold text-[#13293D]">{{ shipment.destination_location?.code }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-[#265D7A]/10">
                        <span class="text-[#265D7A]">Customer Akun:</span>
                        <span class="text-[#13293D] font-bold">{{ shipment.customer?.name }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-[#265D7A]">Status Terkini:</span>
                        <span class="font-mono font-bold text-[#13293D]">{{ shipment.current_status }}</span>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-amber-500/15 border border-amber-600/30 text-amber-950 font-medium text-[11px] leading-relaxed mt-4">
                        <i class="bi bi-shield-lock me-1 text-amber-800"></i>
                        Rute asal, tujuan, dan customer terkunci permanen untuk menjaga integritas data logistik.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
