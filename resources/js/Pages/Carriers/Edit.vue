<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    carrier: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    code: props.carrier.code,
    name: props.carrier.name,
    transport_mode: props.carrier.transport_mode,
    contact_email: props.carrier.contact_email || '',
    contact_phone: props.carrier.contact_phone || '',
    is_active: Boolean(props.carrier.is_active),
});

const submit = () => {
    form.put(`/carriers/${props.carrier.id}`);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Edit Carrier ${carrier.name}`" />

        <div class="max-w-2xl mx-auto">
            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 mb-6 shadow-xl flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-white">Edit Carrier: {{ carrier.name }}</h1>
                    <p class="text-xs font-light text-[#9ABBC8] mt-0.5">Perbarui informasi kontak dan status armada.</p>
                </div>
                <Link href="/carriers" class="px-3.5 py-1.5 border border-white/15 bg-white/5 hover:bg-white/10 text-xs font-semibold rounded-full text-[#D6E6EA] hover:text-white transition inline-flex items-center gap-1.5">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </Link>
            </div>

            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#D6E6EA] mb-1">Kode Carrier <span class="text-rose-400">*</span></label>
                            <input v-model="form.code" type="text" required class="w-full h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs font-mono uppercase text-white focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]" />
                            <div v-if="form.errors.code" class="text-xs text-rose-400 mt-1">{{ form.errors.code }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#D6E6EA] mb-1">Moda Transportasi <span class="text-rose-400">*</span></label>
                            <select v-model="form.transport_mode" required class="w-full h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]">
                                <option value="SEA">SEA (Laut)</option>
                                <option value="AIR">AIR (Udara)</option>
                                <option value="LAND">LAND (Darat)</option>
                                <option value="MULTIMODAL">MULTIMODAL</option>
                            </select>
                            <div v-if="form.errors.transport_mode" class="text-xs text-rose-400 mt-1">{{ form.errors.transport_mode }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#D6E6EA] mb-1">Nama Lengkap Carrier <span class="text-rose-400">*</span></label>
                        <input v-model="form.name" type="text" required class="w-full h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]" />
                        <div v-if="form.errors.name" class="text-xs text-rose-400 mt-1">{{ form.errors.name }}</div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#D6E6EA] mb-1">Email Kontak</label>
                            <input v-model="form.contact_email" type="email" class="w-full h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]" />
                            <div v-if="form.errors.contact_email" class="text-xs text-rose-400 mt-1">{{ form.errors.contact_email }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#D6E6EA] mb-1">Telepon Kontak</label>
                            <input v-model="form.contact_phone" type="text" class="w-full h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]" />
                            <div v-if="form.errors.contact_phone" class="text-xs text-rose-400 mt-1">{{ form.errors.contact_phone }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input v-model="form.is_active" type="checkbox" id="isActive" class="rounded border-white/20 bg-[#031716] text-[#0C969C] focus:ring-0" />
                        <label for="isActive" class="text-xs font-medium text-[#D6E6EA] cursor-pointer">Carrier Aktif Beroperasi</label>
                    </div>

                    <div class="pt-4 border-t border-white/10 flex justify-end gap-2">
                        <Link href="/carriers" class="px-4 py-2 border border-white/15 bg-white/5 hover:bg-white/10 text-xs font-semibold rounded-full text-[#D6E6EA] transition">
                            Batal
                        </Link>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-[#274D60] hover:bg-[#0A7075] text-white text-xs font-semibold rounded-full transition shadow-lg shadow-black/25 disabled:opacity-60 cursor-pointer">
                            Perbarui Carrier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>