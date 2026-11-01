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
        <Head title="Masuk ke Akun" />

        <div class="mb-6">
            <h2 class="text-xl font-bold tracking-tight text-[#1A1A1C]">Masuk ke Akun</h2>
            <p class="text-xs text-[#706E6B] mt-1">Masukkan kredensial Anda untuk mengakses sistem visibilitas pengiriman.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-[#1A1A1C] mb-1">Email</label>
                <input 
                    v-model="form.email" 
                    type="email" 
                    required 
                    autofocus 
                    class="w-full h-11 px-3.5 bg-white border border-[#D8D2C4] rounded-xl text-sm text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20 focus:border-[#1A1A1C] transition"
                    placeholder="nama@perusahaan.com"
                />
                <div v-if="form.errors.email" class="text-xs text-rose-600 mt-1 font-medium">{{ form.errors.email }}</div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="text-xs font-semibold text-[#1A1A1C]">Password</label>
                </div>
                <input 
                    v-model="form.password" 
                    type="password" 
                    required 
                    class="w-full h-11 px-3.5 bg-white border border-[#D8D2C4] rounded-xl text-sm text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20 focus:border-[#1A1A1C] transition"
                    placeholder="••••••••"
                />
                <div v-if="form.errors.password" class="text-xs text-rose-600 mt-1 font-medium">{{ form.errors.password }}</div>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer text-xs text-[#706E6B]">
                    <input v-model="form.remember" type="checkbox" class="rounded border-[#D8D2C4] text-[#1A1A1C] focus:ring-0" />
                    <span>Ingat sesi saya</span>
                </label>
            </div>

            <button 
                type="submit" 
                :disabled="form.processing"
                class="w-full h-11 bg-[#1A1A1C] hover:bg-[#2C2C2E] text-white font-semibold text-sm rounded-xl transition flex items-center justify-center gap-2 shadow-xs disabled:opacity-75"
            >
                <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                <span>Masuk Sekarang</span>
            </button>
        </form>

        <!-- Quick Demo Accounts for Code Ownership Interview -->
        <div class="mt-6 pt-5 border-t border-[#E5E0D5]">
            <p class="text-[11px] font-semibold text-[#706E6B] uppercase tracking-wider mb-2 text-center">Akses Cepat Akun Demo (UAS)</p>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" @click="fillDemo('admin')" class="py-1.5 px-2 text-xs font-semibold rounded-lg bg-[#F2EFE9] hover:bg-[#E5E0D5] text-[#1A1A1C] transition text-center border border-[#E5E0D5]">
                    Admin
                </button>
                <button type="button" @click="fillDemo('operator')" class="py-1.5 px-2 text-xs font-semibold rounded-lg bg-[#F2EFE9] hover:bg-[#E5E0D5] text-[#1A1A1C] transition text-center border border-[#E5E0D5]">
                    Operator
                </button>
                <button type="button" @click="fillDemo('customer')" class="py-1.5 px-2 text-xs font-semibold rounded-lg bg-[#F2EFE9] hover:bg-[#E5E0D5] text-[#1A1A1C] transition text-center border border-[#E5E0D5]">
                    Customer
                </button>
            </div>
        </div>
    </GuestLayout>
</template>
