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
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-6 mb-6 shadow-xl flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold font-mono tracking-tight text-white">Edit Metadata: {{ shipment.shipment_number }}</h1>
                    <p class="text-xs text-slate-400 mt-0.5">Perbarui carrier, jadwal kedatangan yang diharapkan, dan catatan operasional.</p>
                </div>
                <Link :href="`/shipments/${shipment.id}`" class="px-3.5 py-1.5 border border-white/15 bg-white/5 hover:bg-white/10 text-xs font-semibold rounded-full text-slate-200 transition inline-flex items-center gap-1.5">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </Link>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Form -->
                <div class="lg:col-span-2 bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">Carrier Pelayaran <span class="text-rose-400">*</span></label>
                                <select v-model="form.carrier_id" required class="w-full h-10 px-3 bg-slate-950/70 border border-white/15 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-cyan-400/40">
                                    <option v-for="c in carriers" :key="c.id" :value="c.id">
                                        {{ c.name }} ({{ c.code }})
                                    </option>
                                </select>
                                <div v-if="form.errors.carrier_id" class="text-xs text-rose-400 mt-1">{{ form.errors.carrier_id }}</div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1">No. Referensi PO Customer</label>
                                <input v-model="form.reference_number" type="text" class="w-full h-10 px-3 bg-slate-950/70 border border-white/15 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-cyan-400/40" />
                                <div v-if="form.errors.reference_number" class="text-xs text-rose-400 mt-1">{{ form.errors.reference_number }}</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Estimasi Kedatangan (ETA) <span class="text-rose-400">*</span></label>
                            <input v-model="form.expected_arrival" type="datetime-local" required class="w-full h-10 px-3 bg-slate-950/70 border border-white/15 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-cyan-400/40" />
                            <div v-if="form.errors.expected_arrival" class="text-xs text-rose-400 mt-1">{{ form.errors.expected_arrival }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Catatan Khusus / Instruksi Operasional</label>
                            <textarea v-model="form.notes" rows="4" class="w-full p-3 bg-slate-950/70 border border-white/15 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-cyan-400/40"></textarea>
                        </div>

                        <div class="pt-4 border-t border-white/10 flex justify-end gap-2">
                            <Link :href="`/shipments/${shipment.id}`" class="px-4 py-2 border border-white/15 bg-white/5 hover:bg-white/10 text-xs font-semibold rounded-full text-slate-200 transition">
                                Batal
                            </Link>
                            <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-white hover:bg-cyan-50 text-slate-950 text-xs font-bold rounded-full transition shadow-lg shadow-white/10 disabled:opacity-60 cursor-pointer">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right: Immutable Parameters -->
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl space-y-3 text-xs">
                    <h2 class="text-sm font-bold text-white pb-2 border-b border-white/10">Atribut Terproteksi (Immutable)</h2>

                    <div class="flex justify-between py-1.5 border-b border-white/10">
                        <span class="text-slate-400">Nomor Shipment:</span>
                        <span class="font-mono font-bold text-cyan-400">{{ shipment.shipment_number }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-white/10">
                        <span class="text-slate-400">Pelabuhan Asal:</span>
                        <span class="font-mono text-white">{{ shipment.origin_location?.code }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-white/10">
                        <span class="text-slate-400">Pelabuhan Tujuan:</span>
                        <span class="font-mono text-white">{{ shipment.destination_location?.code }}</span>
                    </div>
                    <div class="flex justify-between py-1.5 border-b border-white/10">
                        <span class="text-slate-400">Customer Akun:</span>
                        <span class="text-white font-semibold">{{ shipment.customer?.name }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-slate-400">Status Terkini:</span>
                        <span class="font-mono font-bold text-white">{{ shipment.current_status }}</span>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-200 text-[11px] leading-relaxed mt-4">
                        <i class="bi bi-shield-lock me-1 text-amber-400"></i>
                        Rute asal, tujuan, dan customer terkunci permanen untuk menjaga integritas data logistik.
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
