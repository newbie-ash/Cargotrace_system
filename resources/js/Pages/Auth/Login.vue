<script setup>
import { Head, useForm } from '@inertiajs/vue3';
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
            <h2 class="text-xl font-bold tracking-tight text-[#13293D]">Masuk ke Portal Delixie</h2>
            <p class="text-xs font-normal text-[#265D7A] mt-1">Masukkan kredensial Anda untuk mengakses sistem visibilitas pengiriman.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-[#13293D] mb-1.5">Email</label>
                <input
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    class="w-full h-11 px-3.5 bg-white/90 border border-[#265D7A]/20 rounded-xl text-sm text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A] transition"
                    placeholder="nama@perusahaan.com"
                />
                <div v-if="form.errors.email" class="text-xs text-rose-700 mt-1 font-semibold">{{ form.errors.email }}</div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-xs font-bold text-[#13293D]">Password</label>
                </div>
                <input
                    v-model="form.password"
                    type="password"
                    required
                    class="w-full h-11 px-3.5 bg-white/90 border border-[#265D7A]/20 rounded-xl text-sm text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A] transition"
                    placeholder="••••••••"
                />
                <div v-if="form.errors.password" class="text-xs text-rose-700 mt-1 font-semibold">{{ form.errors.password }}</div>
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-[#265D7A] hover:text-[#13293D]">
                    <input v-model="form.remember" type="checkbox" class="rounded border-[#265D7A]/30 bg-white text-[#265D7A] focus:ring-[#67C7C6]" />
                    <span>Ingat sesi saya</span>
                </label>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full h-11 bg-[#265D7A] hover:bg-[#13293D] text-white font-semibold text-sm rounded-full transition flex items-center justify-center gap-2 shadow-lg shadow-[#13293D]/20 disabled:opacity-60 cursor-pointer"
            >
                <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                <span v-else>Masuk Sekarang</span>
            </button>
        </form>

        <!-- Quick Demo Accounts for Code Ownership Interview -->
        <div class="mt-6 pt-5 border-t border-[#265D7A]/15">
            <p class="text-[11px] font-bold text-[#265D7A] uppercase tracking-wider mb-2.5 text-center">Akses Cepat Akun Demo (UAS)</p>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" @click="fillDemo('admin')" class="py-2 px-2 text-xs font-bold rounded-xl bg-[#EAF6F4] hover:bg-[#13293D] text-[#13293D] hover:text-[#A7E4DB] transition text-center border border-[#265D7A]/20 cursor-pointer">
                    Admin
                </button>
                <button type="button" @click="fillDemo('operator')" class="py-2 px-2 text-xs font-bold rounded-xl bg-[#EAF6F4] hover:bg-[#13293D] text-[#13293D] hover:text-[#A7E4DB] transition text-center border border-[#265D7A]/20 cursor-pointer">
                    Operator
                </button>
                <button type="button" @click="fillDemo('customer')" class="py-2 px-2 text-xs font-bold rounded-xl bg-[#EAF6F4] hover:bg-[#13293D] text-[#13293D] hover:text-[#A7E4DB] transition text-center border border-[#265D7A]/20 cursor-pointer">
                    Customer
                </button>
            </div>
        </div>
    </GuestLayout>
</template>
