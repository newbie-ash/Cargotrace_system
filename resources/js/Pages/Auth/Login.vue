<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};

const fillDemo = (role) => {
    if (role === 'admin') {
        form.email = 'admin@cargotrace.app';
        form.password = 'password';
    } else if (role === 'operator') {
        form.email = 'operator@cargotrace.app';
        form.password = 'password';
    } else if (role === 'customer') {
        form.email = 'customer@cargotrace.app';
        form.password = 'password';
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="Masuk ke Akun Delixie" />

        <div class="mb-6">
            <h2 class="text-xl font-bold tracking-tight text-white">Masuk ke Portal Delixie</h2>
            <p class="text-xs font-light text-[#9ABBC8] mt-1">Masukkan kredensial Anda untuk mengakses sistem visibilitas pengiriman.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-[#D6E6EA] mb-1.5">Email</label>
                <input
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    class="w-full h-11 px-3.5 bg-[#031716]/75 border border-white/10 rounded-xl text-sm text-white placeholder-[#6B8B97] focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C] transition"
                    placeholder="nama@perusahaan.com"
                />
                <div v-if="form.errors.email" class="text-xs text-rose-400 mt-1 font-medium">{{ form.errors.email }}</div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-semibold text-[#D6E6EA]">Password</label>
                </div>
                <input
                    v-model="form.password"
                    type="password"
                    required
                    class="w-full h-11 px-3.5 bg-[#031716]/75 border border-white/10 rounded-xl text-sm text-white placeholder-[#6B8B97] focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C] transition"
                    placeholder="••••••••"
                />
                <div v-if="form.errors.password" class="text-xs text-rose-400 mt-1 font-medium">{{ form.errors.password }}</div>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer text-xs text-[#9ABBC8] hover:text-white">
                    <input v-model="form.remember" type="checkbox" class="rounded border-white/20 bg-[#031716] text-[#0C969C] focus:ring-0" />
                    <span>Ingat sesi saya</span>
                </label>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full h-11 bg-[#274D60] hover:bg-[#0A7075] text-white font-semibold text-sm rounded-full transition flex items-center justify-center gap-2 shadow-lg shadow-black/25 disabled:opacity-60 cursor-pointer"
            >
                <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                <span v-else>Masuk Sekarang</span>
            </button>
        </form>

        <!-- Quick Demo Accounts for Code Ownership Interview -->
        <div class="mt-6 pt-5 border-t border-white/10">
            <p class="text-[11px] font-semibold text-[#9ABBC8] uppercase tracking-wider mb-2.5 text-center">Akses Cepat Akun Demo (UAS)</p>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" @click="fillDemo('admin')" class="py-2 px-2 text-xs font-bold rounded-xl bg-[#031716]/60 hover:bg-[#0A7075]/30 text-white transition text-center border border-white/10 hover:border-[#0C969C]/40 cursor-pointer">
                    Admin
                </button>
                <button type="button" @click="fillDemo('operator')" class="py-2 px-2 text-xs font-bold rounded-xl bg-[#031716]/60 hover:bg-[#0A7075]/30 text-white transition text-center border border-white/10 hover:border-[#0C969C]/40 cursor-pointer">
                    Operator
                </button>
                <button type="button" @click="fillDemo('customer')" class="py-2 px-2 text-xs font-bold rounded-xl bg-[#031716]/60 hover:bg-[#0A7075]/30 text-white transition text-center border border-white/10 hover:border-[#0C969C]/40 cursor-pointer">
                    Customer
                </button>
            </div>
        </div>
    </GuestLayout>
</template>
