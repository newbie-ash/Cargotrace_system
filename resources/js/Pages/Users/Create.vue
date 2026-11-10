<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'operator',
    phone: '',
    company_name: '',
});

const submit = () => {
    form.post('/users');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Tambah Pengguna Baru" />

        <div class="max-w-2xl mx-auto">
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-6 mb-6 shadow-xl flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-white">Tambah Pengguna Baru</h1>
                    <p class="text-xs text-slate-400 mt-0.5">Daftarkan akun admin, operator, atau customer baru.</p>
                </div>
                <Link href="/users" class="px-3.5 py-1.5 border border-white/15 bg-white/5 hover:bg-white/10 text-xs font-semibold rounded-full text-slate-200 transition inline-flex items-center gap-1.5">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </Link>
            </div>

            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Lengkap <span class="text-rose-400">*</span></label>
                            <input v-model="form.name" type="text" placeholder="Budi Santoso" required class="w-full h-10 px-3 bg-slate-950/70 border border-white/15 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-400/40" />
                            <div v-if="form.errors.name" class="text-xs text-rose-400 mt-1">{{ form.errors.name }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Email Akun <span class="text-rose-400">*</span></label>
                            <input v-model="form.email" type="email" placeholder="budi@perusahaan.com" required class="w-full h-10 px-3 bg-slate-950/70 border border-white/15 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-400/40" />
                            <div v-if="form.errors.email" class="text-xs text-rose-400 mt-1">{{ form.errors.email }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Password <span class="text-rose-400">*</span></label>
                            <input v-model="form.password" type="password" placeholder="••••••••" required class="w-full h-10 px-3 bg-slate-950/70 border border-white/15 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-400/40" />
                            <div v-if="form.errors.password" class="text-xs text-rose-400 mt-1">{{ form.errors.password }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Konfirmasi Password <span class="text-rose-400">*</span></label>
                            <input v-model="form.password_confirmation" type="password" placeholder="••••••••" required class="w-full h-10 px-3 bg-slate-950/70 border border-white/15 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-400/40" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Peran Akses (Role) <span class="text-rose-400">*</span></label>
                            <select v-model="form.role" required class="w-full h-10 px-3 bg-slate-950/70 border border-white/15 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-cyan-400/40">
                                <option value="admin">Administrator</option>
                                <option value="operator">Logistics Operator</option>
                                <option value="customer">Customer</option>
                            </select>
                            <div v-if="form.errors.role" class="text-xs text-rose-400 mt-1">{{ form.errors.role }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Perusahaan</label>
                            <input v-model="form.company_name" type="text" placeholder="PT Logistik Global" class="w-full h-10 px-3 bg-slate-950/70 border border-white/15 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-400/40" />
                            <div v-if="form.errors.company_name" class="text-xs text-rose-400 mt-1">{{ form.errors.company_name }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">Nomor Telepon</label>
                            <input v-model="form.phone" type="text" placeholder="+62 812 0000 0000" class="w-full h-10 px-3 bg-slate-950/70 border border-white/15 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-400/40" />
                            <div v-if="form.errors.phone" class="text-xs text-rose-400 mt-1">{{ form.errors.phone }}</div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-white/10 flex justify-end gap-2">
                        <Link href="/users" class="px-4 py-2 border border-white/15 bg-white/5 hover:bg-white/10 text-xs font-semibold rounded-full text-slate-200 transition">
                            Batal
                        </Link>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-white hover:bg-cyan-50 text-slate-950 text-xs font-bold rounded-full transition shadow-lg shadow-white/10 disabled:opacity-60 cursor-pointer">
                            Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
