<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import NotificationBell from '@/Components/NotificationBell.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user || {});
const flash = computed(() => page.props.flash || {});

const isAdmin = computed(() => user.value.role === 'admin');
const isOperator = computed(() => user.value.role === 'operator');
const isCustomer = computed(() => user.value.role === 'customer');

const sidebarOpen = ref(false);

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-cyber-grid bg-slate-950 text-slate-100 flex flex-col font-sans selection:bg-cyan-400 selection:text-slate-950">
        <!-- Top Glowing Multimodal Accent Line -->
        <div class="h-0.5 bg-gradient-to-r from-cyan-400 via-amber-400 to-emerald-400 sticky top-0 z-50"></div>

        <!-- Customer Top Navbar (when role is customer) -->
        <header v-if="isCustomer" class="sticky top-0.5 z-40 bg-slate-900/75 backdrop-blur-xl border-b border-white/10 px-4 lg:px-8 py-3.5 flex items-center justify-between shadow-lg">
            <div class="flex items-center gap-8">
                <Link href="/dashboard" class="flex items-center gap-2.5 font-extrabold text-xl tracking-tight text-white hover:text-cyan-400 transition">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 shadow-[0_0_12px_#22d3ee] animate-pulse"></span>
                    <span>CargoTrace</span>
                </Link>
                <nav class="hidden md:flex items-center gap-2 text-xs font-semibold">
                    <Link
                        href="/dashboard"
                        class="px-3.5 py-1.5 rounded-full transition"
                        :class="$page.url.startsWith('/dashboard') ? 'bg-white text-slate-950 font-bold shadow-sm shadow-white/10' : 'text-slate-300 hover:text-white hover:bg-white/10'"
                    >
                        Dashboard
                    </Link>
                    <Link
                        href="/shipments"
                        class="px-3.5 py-1.5 rounded-full transition"
                        :class="$page.url.startsWith('/shipments') ? 'bg-white text-slate-950 font-bold shadow-sm shadow-white/10' : 'text-slate-300 hover:text-white hover:bg-white/10'"
                    >
                        My Shipments
                    </Link>
                </nav>
            </div>

            <div class="flex items-center gap-4">
                <NotificationBell />
                <div class="text-right hidden sm:block">
                    <div class="text-xs font-bold text-white">{{ user.name }}</div>
                    <div class="text-[10px] text-cyan-400 font-mono font-semibold uppercase tracking-wider">{{ user.company_name || 'Customer' }}</div>
                </div>
                <button @click="logout" class="p-2 text-xs font-semibold text-rose-400 hover:bg-rose-500/15 rounded-xl transition border border-transparent hover:border-rose-500/30 cursor-pointer" title="Keluar">
                    <i class="bi bi-box-arrow-right text-base"></i>
                </button>
            </div>
        </header>

        <!-- Admin & Operator Layout (Sidebar + Main Area) -->
        <div v-else class="flex flex-1">
            <!-- Mobile Sidebar Backdrop -->
            <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-slate-950/80 z-40 lg:hidden backdrop-blur-sm"></div>

            <!-- Desktop & Mobile Sidebar -->
            <aside :class="['fixed lg:sticky top-0.5 inset-y-0 left-0 z-40 w-64 bg-slate-900/85 backdrop-blur-2xl border-r border-white/10 flex flex-col justify-between transition-transform duration-200 lg:translate-x-0', sidebarOpen ? 'translate-x-0' : '-translate-x-full']" style="height: calc(100vh - 2px);">
                <div class="flex flex-col flex-1 overflow-y-auto p-4">
                    <!-- Brand -->
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-white/10">
                        <Link href="/dashboard" class="flex items-center gap-2.5 font-extrabold text-xl tracking-tight text-white hover:text-cyan-400 transition">
                            <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 shadow-[0_0_12px_#22d3ee] animate-pulse"></span>
                            <span>CargoTrace</span>
                        </Link>
                        <button @click="sidebarOpen = false" class="lg:hidden p-1 text-slate-400 hover:text-white cursor-pointer">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <!-- User Badge -->
                    <div class="p-3 rounded-2xl bg-slate-950/70 border border-white/10 mb-5">
                        <div class="font-bold text-xs text-white truncate">{{ user.name }}</div>
                        <div class="text-[10px] font-mono font-bold text-cyan-400 uppercase tracking-wider mt-0.5">
                            {{ isAdmin ? 'ADMINISTRATOR' : 'LOGISTICS OPERATOR' }}
                        </div>
                    </div>

                    <!-- Navigation Links -->
                    <nav class="space-y-1 text-xs font-semibold">
                        <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-500 px-3 pt-2 pb-1">Operasional</div>
                        <Link
                            href="/dashboard"
                            :class="['flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition', $page.url === '/dashboard' ? 'bg-white text-slate-950 font-bold shadow-md shadow-white/10' : 'text-slate-300 hover:bg-white/10 hover:text-white']"
                        >
                            <i class="bi bi-grid-1x2"></i>
                            <span>Dashboard</span>
                        </Link>
                        <Link
                            href="/shipments"
                            :class="['flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition', $page.url.startsWith('/shipments') ? 'bg-white text-slate-950 font-bold shadow-md shadow-white/10' : 'text-slate-300 hover:bg-white/10 hover:text-white']"
                        >
                            <i class="bi bi-box-seam"></i>
                            <span>Shipments</span>
                        </Link>

                        <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-slate-500 px-3 pt-5 pb-1">Master Data</div>
                        <Link
                            href="/carriers"
                            :class="['flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition', $page.url.startsWith('/carriers') ? 'bg-white text-slate-950 font-bold shadow-md shadow-white/10' : 'text-slate-300 hover:bg-white/10 hover:text-white']"
                        >
                            <i class="bi bi-truck"></i>
                            <span>Carriers</span>
                        </Link>
                        <Link
                            href="/locations"
                            :class="['flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition', $page.url.startsWith('/locations') ? 'bg-white text-slate-950 font-bold shadow-md shadow-white/10' : 'text-slate-300 hover:bg-white/10 hover:text-white']"
                        >
                            <i class="bi bi-geo-alt"></i>
                            <span>Locations</span>
                        </Link>
                        <Link
                            v-if="isAdmin"
                            href="/users"
                            :class="['flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl transition', $page.url.startsWith('/users') ? 'bg-white text-slate-950 font-bold shadow-md shadow-white/10' : 'text-slate-300 hover:bg-white/10 hover:text-white']"
                        >
                            <i class="bi bi-people"></i>
                            <span>Users & RBAC</span>
                        </Link>
                    </nav>
                </div>

                <!-- Sidebar Footer -->
                <div class="p-4 border-t border-white/10">
                    <button @click="logout" class="w-full flex items-center gap-2 px-3 py-2 text-xs font-semibold text-rose-400 hover:bg-rose-500/15 rounded-xl transition border border-transparent hover:border-rose-500/30 cursor-pointer">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Desktop Top Bar -->
                <header class="hidden lg:flex sticky top-0.5 z-30 bg-slate-900/60 backdrop-blur-xl border-b border-white/10 px-8 py-3 items-center justify-between">
                    <div class="flex items-center gap-2 text-xs text-slate-400 font-mono">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Sistem Operasi Aktif &bull; Role: <strong class="text-white uppercase font-bold">{{ user.role }}</strong></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <NotificationBell />
                        <div class="h-4 w-px bg-white/10"></div>
                        <div class="text-right">
                            <div class="text-xs font-bold text-white">{{ user.name }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ user.email }}</div>
                        </div>
                    </div>
                </header>

                <!-- Mobile Header -->
                <header class="lg:hidden sticky top-0.5 z-30 bg-slate-900/75 backdrop-blur-xl border-b border-white/10 px-4 py-3 flex items-center justify-between">
                    <button @click="sidebarOpen = true" class="p-1.5 rounded-lg border border-white/10 text-white cursor-pointer">
                        <i class="bi bi-list text-xl"></i>
                    </button>
                    <span class="font-extrabold text-base text-white">CargoTrace</span>
                    <div class="flex items-center gap-2">
                        <NotificationBell />
                        <button @click="logout" class="text-rose-400 p-1 cursor-pointer">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </div>
                </header>

                <!-- Page Content Body -->
                <main class="flex-1 p-4 lg:p-8 max-w-7xl mx-auto w-full">
                    <!-- Flash Messages -->
                    <div v-if="flash.success" class="mb-5 p-3.5 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 flex items-center gap-2 text-xs font-medium">
                        <i class="bi bi-check-circle-fill text-emerald-400 text-sm"></i>
                        <span>{{ flash.success }}</span>
                    </div>

                    <div v-if="flash.error" class="mb-5 p-3.5 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 flex items-center gap-2 text-xs font-medium">
                        <i class="bi bi-exclamation-triangle-fill text-rose-400 text-sm"></i>
                        <span>{{ flash.error }}</span>
                    </div>

                    <slot />
                </main>
            </div>
        </div>

        <!-- Customer Main View Container -->
        <main v-if="isCustomer" class="flex-1 p-4 lg:p-8 max-w-7xl mx-auto w-full">
            <div v-if="flash.success" class="mb-5 p-3.5 rounded-2xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 flex items-center gap-2 text-xs font-medium">
                <i class="bi bi-check-circle-fill text-emerald-400 text-sm"></i>
                <span>{{ flash.success }}</span>
            </div>

            <div v-if="flash.error" class="mb-5 p-3.5 rounded-2xl bg-rose-500/15 border border-rose-500/30 text-rose-300 flex items-center gap-2 text-xs font-medium">
                <i class="bi bi-exclamation-triangle-fill text-rose-400 text-sm"></i>
                <span>{{ flash.error }}</span>
            </div>

            <slot />
        </main>
    </div>
</template>
