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
            <!-- KOLOM KIRI: Brand, Massive Editorial Headline & 3D Centerpiece Showcase (Reference Sprout) -->
            <div class="lg:col-span-7 flex flex-col justify-between space-y-8 py-4 sm:py-6 lg:py-8">
                <!-- Top Brand Row with Back Link -->
                <div class="flex items-center justify-between">
                    <Link href="/" class="inline-flex items-center gap-2 hover:opacity-90 transition group">
                        <DelixieLogo
                            size-class="w-8 h-8"
                            wordmark-class="font-extrabold text-2xl tracking-tight text-[#FAEFDB]"
                            wordmark-text="Delixie"
                        />
                    </Link>

                    <Link
                        href="/"
                        class="inline-flex items-center gap-2 text-xs font-mono font-bold text-[#FAEFDB]/70 hover:text-white transition px-3.5 py-1.5 rounded-full bg-white/5 border border-[#FAEFDB]/15 hover:border-[#BE2388]"
                    >
                        <i class="bi bi-arrow-left text-xs" aria-hidden="true"></i>
                        <span>Kembali ke Beranda</span>
                    </Link>
                </div>

                <!-- Massive Editorial Headline (Inspired by 'Every big thing starts small') -->
                <div class="space-y-4 max-w-xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#001F18] border border-[#FAEFDB]/15 text-[11px] font-mono font-bold text-[#BE2388]">
                        <span class="w-2 h-2 rounded-full bg-[#BE2388] animate-pulse"></span>
                        <span>PORTAL VISIBILITAS RANTAI PASOK GLOBAL</span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-[1.08] text-[#FAEFDB] drop-shadow-sm">
                        Every global journey
                        <span class="block text-transparent bg-clip-text bg-gradient-to-r from-[#FAEFDB] via-[#E2B7D6] to-[#BE2388]">starts with a single shipment.</span>
                    </h1>

                    <p class="text-sm sm:text-base text-[#FAEFDB]/80 font-normal leading-relaxed">
                        Masuk ke portal operasional untuk memantau pergerakan kontainer maritim, status armada antar-benua, dan resolusi kendala rute secara real-time.
                    </p>
                </div>

                <!-- 3D Intermodal Showcase Card with Ambient Glow (Fills Left Negative Space) -->
                <div class="relative rounded-3xl bg-gradient-to-br from-[#02332A]/90 to-[#001F18]/90 border border-[#FAEFDB]/18 p-6 shadow-[0_20px_50px_rgba(0,0,0,0.50)] overflow-hidden group">
                    <div class="absolute -right-12 -top-12 w-48 h-48 rounded-full bg-[#BE2388]/20 blur-3xl pointer-events-none"></div>

                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-[#FAEFDB]/15">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#BE2388] to-[#D42B8A] flex items-center justify-center text-white shadow-md shadow-[#BE2388]/30">
                                <i class="bi bi-water text-lg" aria-hidden="true"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#FAEFDB]/60 block">KORIDOR MARITIM AKTIF</span>
                                <strong class="text-sm font-extrabold font-mono text-[#FAEFDB]">IDJKT &rarr; NLRTM (Tanjung Priok &bull; Rotterdam)</strong>
                            </div>
                        </div>
                        <span class="text-[11px] font-mono font-bold px-3 py-1 rounded-full bg-[#BE2388] text-white">
                            21.4 KTS &bull; On-Time
                        </span>
                    </div>

                    <!-- Telemetry Counters in Showcase Card -->
                    <div class="grid grid-cols-3 gap-4 pt-4 text-center">
                        <div>
                            <div class="text-xl sm:text-2xl font-mono font-extrabold text-[#FAEFDB]">99.4%</div>
                            <div class="text-[10px] font-semibold text-[#FAEFDB]/60 uppercase font-mono mt-0.5">SLA On-Time</div>
                        </div>
                        <div class="border-x border-[#FAEFDB]/15">
                            <div class="text-xl sm:text-2xl font-mono font-extrabold text-[#FAEFDB]">6 Hub</div>
                            <div class="text-[10px] font-semibold text-[#FAEFDB]/60 uppercase font-mono mt-0.5">Transit Gates</div>
                        </div>
                        <div>
                            <div class="text-xl sm:text-2xl font-mono font-extrabold text-emerald-400">0 Critical</div>
                            <div class="text-[10px] font-semibold text-[#FAEFDB]/60 uppercase font-mono mt-0.5">Open Incidents</div>
                        </div>
                    </div>
                </div>

                <!-- Left Footer Info -->
                <div class="text-xs font-mono text-[#FAEFDB]/50 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Delixie CargoTrace Platform &bull; REST API Sanctum Protected</span>
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
