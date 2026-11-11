<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    code: '',
    name: '',
    transport_mode: 'SEA',
    contact_email: '',
    contact_phone: '',
    is_active: true,
});

const submit = () => {
    form.post('/carriers');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Tambah Carrier Baru" />

        <div class="max-w-2xl mx-auto">
            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 mb-6 shadow-xl flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-white">Tambah Carrier Baru</h1>
                    <p class="text-xs font-light text-[#9ABBC8] mt-0.5">Daftarkan operator armada transportasi kargo baru.</p>
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
                            <input v-model="form.code" type="text" placeholder="Contoh: MAERSK" required class="w-full h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs font-mono uppercase text-white placeholder-[#6B8B97] focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]" />
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
                        <input v-model="form.name" type="text" placeholder="Contoh: A.P. Moller - Maersk Line" required class="w-full h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs text-white placeholder-[#6B8B97] focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]" />
                        <div v-if="form.errors.name" class="text-xs text-rose-400 mt-1">{{ form.errors.name }}</div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#D6E6EA] mb-1">Email Kontak</label>
                            <input v-model="form.contact_email" type="email" placeholder="support@carrier.com" class="w-full h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs text-white placeholder-[#6B8B97] focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]" />
                            <div v-if="form.errors.contact_email" class="text-xs text-rose-400 mt-1">{{ form.errors.contact_email }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#D6E6EA] mb-1">Telepon Kontak</label>
                            <input v-model="form.contact_phone" type="text" placeholder="+62 21 0000 0000" class="w-full h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs text-white placeholder-[#6B8B97] focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]" />
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
                            Simpan Carrier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>