<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    user: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role,
    phone: props.user.phone || '',
    company_name: props.user.company_name || '',
});

const submit = () => {
    form.put(`/users/${props.user.id}`);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Edit User ${user.name}`" />

        <div class="max-w-2xl mx-auto">
            <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 mb-6 shadow-xs flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[#1A1A1C]">Edit Pengguna: {{ user.name }}</h1>
                    <p class="text-xs text-[#706E6B] mt-0.5">Perbarui profil atau hak akses peran pengguna.</p>
                </div>
                <Link href="/users" class="px-3 py-1.5 border border-[#D8D2C4] hover:bg-[#F2EFE9] text-xs font-semibold rounded-xl text-[#1A1A1C] transition inline-flex items-center gap-1.5">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </Link>
            </div>

            <div class="bg-white/80 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 shadow-xs">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Nama Lengkap <span class="text-rose-600">*</span></label>
                            <input v-model="form.name" type="text" required class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20" />
                            <div v-if="form.errors.name" class="text-xs text-rose-600 mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Email Akun <span class="text-rose-600">*</span></label>
                            <input v-model="form.email" type="email" required class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20" />
                            <div v-if="form.errors.email" class="text-xs text-rose-600 mt-1">{{ form.errors.email }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Password Baru (Opsional)</label>
                            <input v-model="form.password" type="password" placeholder="Kosongkan jika tidak diubah" class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20" />
                            <div v-if="form.errors.password" class="text-xs text-rose-600 mt-1">{{ form.errors.password }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Konfirmasi Password Baru</label>
                            <input v-model="form.password_confirmation" type="password" placeholder="Ulangi password baru" class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Peran Akses (Role) <span class="text-rose-600">*</span></label>
                            <select v-model="form.role" required class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20">
                                <option value="admin">Administrator</option>
                                <option value="operator">Logistics Operator</option>
                                <option value="customer">Customer</option>
                            </select>
                            <div v-if="form.errors.role" class="text-xs text-rose-600 mt-1">{{ form.errors.role }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Nama Perusahaan</label>
                            <input v-model="form.company_name" type="text" class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20" />
                            <div v-if="form.errors.company_name" class="text-xs text-rose-600 mt-1">{{ form.errors.company_name }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Nomor Telepon</label>
                            <input v-model="form.phone" type="text" class="w-full h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20" />
                            <div v-if="form.errors.phone" class="text-xs text-rose-600 mt-1">{{ form.errors.phone }}</div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-[#E5E0D5] flex justify-end gap-2">
                        <Link href="/users" class="px-4 py-2 border border-[#D8D2C4] hover:bg-[#F2EFE9] text-xs font-semibold rounded-xl text-[#1A1A1C] transition">
                            Batal
                        </Link>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-[#1A1A1C] hover:bg-[#2C2C2E] text-white text-xs font-semibold rounded-xl transition shadow-xs disabled:opacity-75">
                            Perbarui Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
