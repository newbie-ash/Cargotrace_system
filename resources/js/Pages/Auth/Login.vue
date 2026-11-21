<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import DelixieLogo from '@/Components/DelixieLogo.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

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

        <div class="w-full max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-14 items-center relative z-10">
            <!-- KOLOM KIRI: Brand, Massive Editorial Headline & Narrative (Clean & Minimalist like Sprout) -->
            <div class="lg:col-span-7 flex flex-col justify-center space-y-8 py-6 lg:py-16">
                <!-- Top Brand Row with Back Link -->
                <div class="flex items-center justify-between">
                    <Link href="/" class="inline-flex items-center gap-2 hover:opacity-90 transition group">
                        <DelixieLogo
                            size-class="w-9 h-9"
                            wordmark-class="font-extrabold text-2xl sm:text-3xl tracking-tight text-[#FAEFDB]"
                            wordmark-text="Delixie"
                        />
                    </Link>

                    <Link
                        href="/"
                        class="inline-flex items-center gap-2 text-xs font-mono font-bold text-[#FAEFDB]/70 hover:text-white transition px-4 py-2 rounded-full bg-white/5 border border-[#FAEFDB]/15 hover:border-[#BE2388]"
                    >
                        <i class="bi bi-arrow-left text-xs" aria-hidden="true"></i>
                        <span>Kembali ke Beranda</span>
                    </Link>
                </div>

                <!-- Massive Editorial Headline & Narrative -->
                <div class="space-y-6 max-w-xl">
                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.06] text-[#FAEFDB] drop-shadow-md">
                        Every global journey
                        <span class="block text-transparent bg-clip-text bg-gradient-to-r from-[#FAEFDB] via-[#E2B7D6] to-[#BE2388]">starts with a single shipment.</span>
                    </h1>

                    <p class="text-base sm:text-lg text-[#FAEFDB]/85 font-normal leading-relaxed">
                        Masuk ke portal operasional untuk memantau pergerakan kontainer maritim, status armada antar-benua, dan resolusi kendala rute secara real-time.
                    </p>
                </div>
            </div>

            <!-- KOLOM KANAN: Elevated Warm Bone Card (Like Image 2 "Sprout" Login) -->
            <div class="lg:col-span-5">
                <div class="bg-[#FAEFDB] text-[#012B23] rounded-[32px] p-7 sm:p-9 shadow-[0_28px_70px_rgba(0,0,0,0.45)] border border-white/80 relative overflow-hidden">
                    <!-- Top Card Header -->
                    <div class="mb-7">
                        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[#012B23]">
                            Log in to your account
                        </h2>
                        <p class="text-xs font-medium text-[#012B23]/70 mt-1.5 leading-relaxed">
                            Akses dashboard kendali dan pantau logistik Anda.
                        </p>
                    </div>

                    <!-- Form Login -->
                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- Email Input with Mail Icon inside -->
                        <div>
                            <label for="login-email" class="block text-xs font-bold text-[#012B23] mb-2">
                                Email address
                            </label>
                            <div class="relative">
                                <i class="bi bi-envelope absolute left-4 top-3.5 text-[#012B23]/50 text-base" aria-hidden="true"></i>
                                <input
                                    id="login-email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    class="w-full h-12 pl-11 pr-4 bg-white border border-[#012B23]/15 rounded-2xl text-sm font-medium text-[#012B23] placeholder-[#012B23]/40 focus:outline-none focus:ring-2 focus:ring-[#BE2388] focus:border-transparent transition shadow-xs"
                                    placeholder="Enter your email"
                                />
                            </div>
                            <div v-if="form.errors.email" class="text-xs text-rose-700 mt-1 font-semibold flex items-center gap-1">
                                <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ form.errors.email }}</span>
                            </div>
                        </div>

                        <!-- Password Input with Lock Icon & Eye Toggle inside -->
                        <div>
                            <label for="login-password" class="block text-xs font-bold text-[#012B23] mb-2">
                                Password
                            </label>
                            <div class="relative">
                                <i class="bi bi-lock absolute left-4 top-3.5 text-[#012B23]/50 text-base" aria-hidden="true"></i>
                                <input
                                    id="login-password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    autocomplete="current-password"
                                    class="w-full h-12 pl-11 pr-11 bg-white border border-[#012B23]/15 rounded-2xl text-sm font-medium text-[#012B23] placeholder-[#012B23]/40 focus:outline-none focus:ring-2 focus:ring-[#BE2388] focus:border-transparent transition shadow-xs"
                                    placeholder="Enter your password"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-4 top-3.5 text-[#012B23]/50 hover:text-[#012B23] transition cursor-pointer"
                                    :title="showPassword ? 'Sembunyikan password' : 'Lihat password'"
                                    aria-label="Toggle password visibility"
                                >
                                    <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'" text-base aria-hidden="true"></i>
                                </button>
                            </div>
                            <div v-if="form.errors.password" class="text-xs text-rose-700 mt-1 font-semibold flex items-center gap-1">
                                <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
                                <span>{{ form.errors.password }}</span>
                            </div>
                        </div>

                        <!-- Remember Me & Forgot Password Helper Row -->
                        <div class="flex items-center justify-between pt-1">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-[#012B23]/70 hover:text-[#012B23]">
                                <input
                                    v-model="form.remember"
                                    type="checkbox"
                                    class="w-4 h-4 rounded-md border-[#012B23]/25 bg-white text-[#BE2388] focus:ring-[#BE2388]"
                                />
                                <span>Remember me</span>
                            </label>

                            <a
                                href="#"
                                @click.prevent="fillDemo('admin')"
                                class="text-xs font-bold text-[#BE2388] hover:underline"
                            >
                                Forgot password?
                            </a>
                        </div>

                        <!-- Primary CTA Button (High Impact Magenta Gradient Pill) -->
                        <div class="pt-2">
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full h-12 bg-gradient-to-r from-[#BE2388] via-[#C82A8F] to-[#D42B8A] hover:brightness-110 text-white font-bold text-sm rounded-2xl transition-all duration-300 shadow-lg shadow-[#BE2388]/30 hover:-translate-y-0.5 flex items-center justify-center gap-2 disabled:opacity-60 cursor-pointer"
                            >
                                <span v-if="form.processing" class="inline-block w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
                                <span v-else class="flex items-center gap-2">
                                    <span>Log in</span>
                                    <i class="bi bi-arrow-right text-xs" aria-hidden="true"></i>
                                </span>
                            </button>
                        </div>
                    </form>

                    <!-- Divider 'or' (Exact Reference from Image 2) -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="w-full border-t border-[#012B23]/15"></div>
                        </div>
                        <div class="relative flex justify-center text-xs">
                            <span class="bg-[#FAEFDB] px-3 font-mono font-bold text-[#012B23]/50 uppercase text-[10px] tracking-wider">
                                Atau Akses Cepat Akun Demo (UAS)
                            </span>
                        </div>
                    </div>

                    <!-- Quick Demo Account Buttons for Code Ownership Demo -->
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            type="button"
                            @click="fillDemo('admin')"
                            class="py-2.5 px-2 text-xs font-bold rounded-xl bg-white hover:bg-[#012B23] text-[#012B23] hover:text-[#FAEFDB] border border-[#012B23]/15 transition text-center shadow-xs cursor-pointer flex flex-col items-center gap-0.5 group"
                        >
                            <span class="font-extrabold">Admin</span>
                            <span class="text-[9px] font-mono text-[#012B23]/50 group-hover:text-[#FAEFDB]/70">Full Access</span>
                        </button>
                        <button
                            type="button"
                            @click="fillDemo('operator')"
                            class="py-2.5 px-2 text-xs font-bold rounded-xl bg-white hover:bg-[#012B23] text-[#012B23] hover:text-[#FAEFDB] border border-[#012B23]/15 transition text-center shadow-xs cursor-pointer flex flex-col items-center gap-0.5 group"
                        >
                            <span class="font-extrabold">Operator</span>
                            <span class="text-[9px] font-mono text-[#012B23]/50 group-hover:text-[#FAEFDB]/70">Field Dispatch</span>
                        </button>
                        <button
                            type="button"
                            @click="fillDemo('customer')"
                            class="py-2.5 px-2 text-xs font-bold rounded-xl bg-white hover:bg-[#012B23] text-[#012B23] hover:text-[#FAEFDB] border border-[#012B23]/15 transition text-center shadow-xs cursor-pointer flex flex-col items-center gap-0.5 group"
                        >
                            <span class="font-extrabold">Customer</span>
                            <span class="text-[9px] font-mono text-[#012B23]/50 group-hover:text-[#FAEFDB]/70">Client View</span>
                        </button>
                    </div>

                    <!-- Bottom Signup / Help Prompt -->
                    <div class="mt-6 text-center text-xs text-[#012B23]/70 font-medium">
                        Belum memiliki kredensial akun?
                        <a href="mailto:support@cargotrace.app" class="font-bold text-[#BE2388] hover:underline">
                            Hubungi Admin
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
