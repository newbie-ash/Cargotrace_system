<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import NotificationBell from '@/Components/NotificationBell.vue';
import DelixieLogo from '@/Components/DelixieLogo.vue';
import DynamicIslandToast from '@/Components/DynamicIslandToast.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user || {});

const isAdmin = computed(() => user.value.role === 'admin');
const isOperator = computed(() => user.value.role === 'operator');
const isCustomer = computed(() => user.value.role === 'customer');

const sidebarOpen = ref(false);

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-transparent text-[#13293D] flex flex-col font-sans selection:bg-[#67C7C6] selection:text-[#13293D]">
        <!-- Dynamic Island Flash Notification -->
        <DynamicIslandToast />

        <!-- Top Subtle Brand Accent Line -->
        <div class="h-0.5 bg-gradient-to-r from-[#13293D] via-[#265D7A] to-[#67C7C6] sticky top-0 z-50"></div>

        <!-- Customer Top Navbar -->
        <header v-if="isCustomer" class="sticky top-0.5 z-40 bg-white/75 backdrop-blur-2xl border-b border-white/85 px-4 lg:px-8 py-3.5 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-8">
                <Link href="/dashboard" class="flex items-center hover:opacity-90 transition group">
                    <DelixieLogo size-class="w-7 h-7" wordmark-class="font-extrabold text-xl tracking-tight text-[#13293D]" />
                </Link>
                <nav class="hidden md:flex items-center gap-2 text-xs font-semibold">
                    <Link
                        href="/dashboard"
                        class="px-4 py-1.5 rounded-full transition"
                        :class="$page.url.startsWith('/dashboard') ? 'bg-[#13293D] text-[#A7E4DB] font-bold shadow-sm' : 'text-[#265D7A] hover:text-[#13293D] hover:bg-white/70'"
                    >
                        Dashboard
                    </Link>
                    <Link
                        href="/shipments"
                        class="px-4 py-1.5 rounded-full transition"
                        :class="$page.url.startsWith('/shipments') ? 'bg-[#13293D] text-[#A7E4DB] font-bold shadow-sm' : 'text-[#265D7A] hover:text-[#13293D] hover:bg-white/70'"
                    >
                        My Shipments
                    </Link>
                </nav>
            </div>

            <div class="flex items-center gap-4">
                <NotificationBell />
                <div class="text-right hidden sm:block">
                    <div class="text-xs font-bold text-[#13293D]">{{ user.name }}</div>
                    <div class="text-[10px] text-[#265D7A] font-mono font-bold uppercase tracking-wider">{{ user.company_name || 'Customer' }}</div>
                </div>
                <button @click="logout" class="p-2 text-xs font-semibold text-rose-700 hover:bg-rose-500/15 rounded-xl transition border border-transparent hover:border-rose-500/30 cursor-pointer" title="Keluar" aria-label="Keluar">
                    <i class="bi bi-box-arrow-right text-base"></i>
                </button>
            </div>
        </header>

        <!-- Admin & Operator Layout (Sidebar + Main Area) -->
        <div v-else class="flex flex-1">
            <!-- Mobile Sidebar Backdrop -->
            <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-[#13293D]/45 z-40 lg:hidden backdrop-blur-sm"></div>

            <!-- Desktop & Mobile Frosted Sidebar -->
            <aside :class="['fixed lg:sticky top-0.5 inset-y-0 left-0 z-40 w-64 bg-white/75 backdrop-blur-2xl border-r border-white/85 flex flex-col justify-between transition-transform duration-200 lg:translate-x-0 shadow-[4px_0_32px_rgba(19,41,61,0.05)]', sidebarOpen ? 'translate-x-0' : '-translate-x-full']" style="height: calc(100vh - 2px);">
                <div class="flex flex-col flex-1 overflow-y-auto p-4">
                    <!-- Brand -->
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-[#265D7A]/15">
                        <Link href="/dashboard" class="flex items-center hover:opacity-90 transition group">
                            <DelixieLogo size-class="w-7 h-7" wordmark-class="font-extrabold text-xl tracking-tight text-[#13293D]" />
                        </Link>
                        <button @click="sidebarOpen = false" class="lg:hidden p-1 text-[#265D7A] hover:text-[#13293D] cursor-pointer" aria-label="Tutup Menu">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <!-- User Badge -->
                    <div class="p-3 rounded-2xl bg-[#EAF6F4] border border-[#265D7A]/15 mb-5">
                        <div class="font-bold text-xs text-[#13293D] truncate">{{ user.name }}</div>
                        <div class="text-[10px] font-mono font-bold text-[#265D7A] uppercase tracking-wider mt-0.5">
                            {{ isAdmin ? 'ADMINISTRATOR' : 'LOGISTICS OPERATOR' }}
                        </div>
                    </div>

                    <!-- Navigation Links -->
                    <nav class="space-y-1 text-xs font-semibold">
                        <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#265D7A] px-3 pt-2 pb-1">Operasional</div>
                        <Link
                            href="/dashboard"
                            :class="['flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition', $page.url === '/dashboard' ? 'bg-[#13293D] text-[#A7E4DB] font-bold shadow-md' : 'text-[#265D7A] hover:bg-white/80 hover:text-[#13293D]']"
                        >
                            <i class="bi bi-grid-1x2"></i>
                            <span>Dashboard</span>
                        </Link>
                        <Link
                            href="/shipments"
                            :class="['flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition', $page.url.startsWith('/shipments') ? 'bg-[#13293D] text-[#A7E4DB] font-bold shadow-md' : 'text-[#265D7A] hover:bg-white/80 hover:text-[#13293D]']"
                        >
                            <i class="bi bi-box-seam"></i>
                            <span>Shipments</span>
                        </Link>

                        <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#265D7A] px-3 pt-5 pb-1">Master Data</div>
                        <Link
                            href="/carriers"
                            :class="['flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition', $page.url.startsWith('/carriers') ? 'bg-[#13293D] text-[#A7E4DB] font-bold shadow-md' : 'text-[#265D7A] hover:bg-white/80 hover:text-[#13293D]']"
                        >
                            <i class="bi bi-truck"></i>
                            <span>Carriers</span>
                        </Link>
                        <Link
                            href="/locations"
                            :class="['flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition', $page.url.startsWith('/locations') ? 'bg-[#13293D] text-[#A7E4DB] font-bold shadow-md' : 'text-[#265D7A] hover:bg-white/80 hover:text-[#13293D]']"
                        >
                            <i class="bi bi-geo-alt"></i>
                            <span>Locations</span>
                        </Link>
                        <Link
                            v-if="isAdmin"
                            href="/users"
                            :class="['flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition', $page.url.startsWith('/users') ? 'bg-[#13293D] text-[#A7E4DB] font-bold shadow-md' : 'text-[#265D7A] hover:bg-white/80 hover:text-[#13293D]']"
                        >
                            <i class="bi bi-people"></i>
                            <span>Users &amp; RBAC</span>
                        </Link>
                    </nav>
                </div>

                <!-- Sidebar Footer -->
                <div class="p-4 border-t border-[#265D7A]/15">
                    <button @click="logout" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-bold text-rose-700 hover:bg-rose-500/15 rounded-xl transition border border-transparent hover:border-rose-500/30 cursor-pointer">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Desktop Top Bar -->
                <header class="hidden lg:flex sticky top-0.5 z-30 bg-white/70 backdrop-blur-2xl border-b border-white/85 px-8 py-3 items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2 text-xs text-[#265D7A] font-mono">
                        <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        <span>Sistem Operasi Aktif &bull; Role: <strong class="text-[#13293D] uppercase font-bold">{{ user.role }}</strong></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <NotificationBell />
                        <div class="h-4 w-px bg-[#265D7A]/20"></div>
                        <div class="text-right">
                            <div class="text-xs font-bold text-[#13293D]">{{ user.name }}</div>
                            <div class="text-[10px] text-[#265D7A] font-mono">{{ user.email }}</div>
                        </div>
                    </div>
                </header>

                <!-- Mobile Header -->
                <header class="lg:hidden sticky top-0.5 z-30 bg-white/80 backdrop-blur-2xl border-b border-white/85 px-4 py-3 flex items-center justify-between">
                    <button @click="sidebarOpen = true" class="p-1.5 rounded-lg border border-[#265D7A]/20 text-[#13293D] cursor-pointer" aria-label="Buka Menu">
                        <i class="bi bi-list text-xl"></i>
                    </button>
                    <DelixieLogo size-class="w-6 h-6" wordmark-class="font-extrabold text-base text-[#13293D]" />
                    <div class="flex items-center gap-2">
                        <NotificationBell />
                        <button @click="logout" class="text-rose-700 p-1 cursor-pointer" aria-label="Keluar">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </div>
                </header>

                <!-- Page Content Body -->
                <main class="flex-1 p-4 lg:p-8 max-w-7xl mx-auto w-full">
                    <slot />
                </main>
            </div>
        </div>

        <!-- Customer Main View Container -->
        <main v-if="isCustomer" class="flex-1 p-4 lg:p-8 max-w-7xl mx-auto w-full">
            <slot />
        </main>
    </div>
</template>
