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
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 mb-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)] flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-[#13293D]">Edit Pengguna: {{ user.name }}</h1>
                    <p class="text-xs font-normal text-[#265D7A] mt-0.5">Perbarui profil atau hak akses peran pengguna.</p>
                </div>
                <Link href="/users" class="px-3.5 py-1.5 border border-[#265D7A]/20 bg-white/80 hover:bg-white text-xs font-semibold rounded-full text-[#13293D] transition inline-flex items-center gap-1.5">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </Link>
            </div>

            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)]">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Nama Lengkap <span class="text-rose-600">*</span></label>
                            <input v-model="form.name" type="text" required class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                            <div v-if="form.errors.name" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Email Akun <span class="text-rose-600">*</span></label>
                            <input v-model="form.email" type="email" required class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                            <div v-if="form.errors.email" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.email }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Password Baru (Opsional)</label>
                            <input v-model="form.password" type="password" placeholder="Kosongkan jika tidak diubah" class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                            <div v-if="form.errors.password" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.password }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Konfirmasi Password Baru</label>
                            <input v-model="form.password_confirmation" type="password" placeholder="Ulangi password baru" class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Peran Akses (Role) <span class="text-rose-600">*</span></label>
                            <select v-model="form.role" required class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]">
                                <option value="admin">Administrator</option>
                                <option value="operator">Logistics Operator</option>
                                <option value="customer">Customer</option>
                            </select>
                            <div v-if="form.errors.role" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.role }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Nama Perusahaan</label>
                            <input v-model="form.company_name" type="text" class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                            <div v-if="form.errors.company_name" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.company_name }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Nomor Telepon</label>
                            <input v-model="form.phone" type="text" class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                            <div v-if="form.errors.phone" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.phone }}</div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-[#265D7A]/15 flex justify-end gap-2">
                        <Link href="/users" class="px-4 py-2 border border-[#265D7A]/20 bg-white/80 hover:bg-white text-xs font-semibold rounded-full text-[#13293D] transition">
                            Batal
                        </Link>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-full transition shadow-md shadow-[#13293D]/15 disabled:opacity-60 cursor-pointer">
                            Perbarui Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>