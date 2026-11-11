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
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 mb-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)] flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-[#13293D]">Edit Carrier: {{ carrier.name }}</h1>
                    <p class="text-xs font-normal text-[#265D7A] mt-0.5">Perbarui informasi kontak dan status armada.</p>
                </div>
                <Link href="/carriers" class="px-3.5 py-1.5 border border-[#265D7A]/20 bg-white/80 hover:bg-white text-xs font-semibold rounded-full text-[#13293D] transition inline-flex items-center gap-1.5">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </Link>
            </div>

            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)]">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Kode Carrier <span class="text-rose-600">*</span></label>
                            <input v-model="form.code" type="text" required class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs font-mono uppercase text-[#13293D] focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                            <div v-if="form.errors.code" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.code }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Moda Transportasi <span class="text-rose-600">*</span></label>
                            <select v-model="form.transport_mode" required class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]">
                                <option value="SEA">SEA (Laut)</option>
                                <option value="AIR">AIR (Udara)</option>
                                <option value="LAND">LAND (Darat)</option>
                                <option value="MULTIMODAL">MULTIMODAL</option>
                            </select>
                            <div v-if="form.errors.transport_mode" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.transport_mode }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-[#13293D] mb-1">Nama Lengkap Carrier <span class="text-rose-600">*</span></label>
                        <input v-model="form.name" type="text" required class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                        <div v-if="form.errors.name" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.name }}</div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Email Kontak</label>
                            <input v-model="form.contact_email" type="email" class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                            <div v-if="form.errors.contact_email" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.contact_email }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Telepon Kontak</label>
                            <input v-model="form.contact_phone" type="text" class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                            <div v-if="form.errors.contact_phone" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.contact_phone }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <input v-model="form.is_active" type="checkbox" id="isActive" class="rounded border-[#265D7A]/30 bg-white text-[#265D7A] focus:ring-[#67C7C6]/45" />
                        <label for="isActive" class="text-xs font-semibold text-[#13293D] cursor-pointer">Carrier Aktif Beroperasi</label>
                    </div>

                    <div class="pt-4 border-t border-[#265D7A]/15 flex justify-end gap-2">
                        <Link href="/carriers" class="px-4 py-2 border border-[#265D7A]/20 bg-white/80 hover:bg-white text-xs font-semibold rounded-full text-[#13293D] transition">
                            Batal
                        </Link>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-full transition shadow-md shadow-[#13293D]/15 disabled:opacity-60 cursor-pointer">
                            Perbarui Carrier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>