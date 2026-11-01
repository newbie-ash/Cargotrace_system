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
    <div class="min-h-screen bg-[#F6F3EC] text-[#1A1A1C] flex flex-col font-sans">
        <!-- Top Hairline Accent -->
        <div class="h-0.5 bg-gradient-to-r from-[#1A1A1C] via-[#D8D2C4] to-[#F8F5EE] sticky top-0 z-50"></div>

        <!-- Customer Top Navbar (when role is customer) -->
        <header v-if="isCustomer" class="sticky top-0.5 z-40 bg-white/70 backdrop-blur-md border-b border-[#E5E0D5] px-4 lg:px-8 py-3.5 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-6">
                <Link href="/dashboard" class="flex items-center gap-2 font-extrabold text-xl tracking-tight text-[#1A1A1C]">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#1A1A1C]"></span>
                    <span>CargoTrace</span>
                </Link>
                <nav class="hidden md:flex items-center gap-4 text-sm font-medium">
                    <Link href="/dashboard" class="px-3 py-1.5 rounded-lg transition" :class="$page.url.startsWith('/dashboard') ? 'bg-white text-[#1A1A1C] font-semibold shadow-xs' : 'text-[#706E6B] hover:text-[#1A1A1C]'">
                        Dashboard
                    </Link>
                    <Link href="/shipments" class="px-3 py-1.5 rounded-lg transition" :class="$page.url.startsWith('/shipments') ? 'bg-white text-[#1A1A1C] font-semibold shadow-xs' : 'text-[#706E6B] hover:text-[#1A1A1C]'">
                        My Shipments
                    </Link>
                </nav>
            </div>

            <div class="flex items-center gap-3">
                <NotificationBell />
                <div class="text-right hidden sm:block">
                    <div class="text-xs font-bold text-[#1A1A1C]">{{ user.name }}</div>
                    <div class="text-[10px] text-[#706E6B] font-semibold uppercase tracking-wider">{{ user.company_name || 'Customer' }}</div>
                </div>
                <button @click="logout" class="p-2 text-xs font-semibold text-red-600 hover:bg-red-50 rounded-lg transition border border-transparent hover:border-red-200" title="Keluar">
                    <i class="bi bi-box-arrow-right text-base"></i>
                </button>
            </div>
        </header>

        <!-- Admin & Operator Layout (Sidebar + Main Area) -->
        <div v-else class="flex flex-1">
            <!-- Mobile Sidebar Backdrop -->
            <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-black/40 z-40 lg:hidden backdrop-blur-xs"></div>

            <!-- Desktop & Mobile Sidebar -->
            <aside :class="['fixed lg:sticky top-0.5 inset-y-0 left-0 z-40 w-64 bg-white/70 backdrop-blur-xl border-r border-[#E5E0D5] flex flex-col justify-between transition-transform duration-200 lg:translate-x-0', sidebarOpen ? 'translate-x-0' : '-translate-x-full']" style="height: calc(100vh - 2px);">
                <div class="flex flex-col flex-1 overflow-y-auto p-4">
                    <!-- Brand -->
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-[#E5E0D5]">
                        <Link href="/dashboard" class="flex items-center gap-2 font-extrabold text-xl tracking-tight text-[#1A1A1C]">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#1A1A1C]"></span>
                            <span>CargoTrace</span>
                        </Link>
                        <button @click="sidebarOpen = false" class="lg:hidden p-1 text-[#706E6B] hover:text-black">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <!-- User Badge -->
                    <div class="p-2.5 rounded-xl bg-white/80 border border-[#E5E0D5] mb-5">
                        <div class="font-bold text-xs text-[#1A1A1C] truncate">{{ user.name }}</div>
                        <div class="text-[10px] font-semibold text-[#706E6B] uppercase tracking-wider">
                            {{ isAdmin ? 'ADMINISTRATOR' : 'LOGISTICS OPERATOR' }}
                        </div>
                    </div>

                    <!-- Navigation Links -->
                    <nav class="space-y-1 text-sm font-medium">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-[#706E6B] px-3 pt-2 pb-1">Operasional</div>
                        <Link href="/dashboard" :class="['flex items-center gap-2.5 px-3 py-2 rounded-xl transition', $page.url === '/dashboard' ? 'bg-[#1A1A1C] text-white font-semibold shadow-xs' : 'text-[#706E6B] hover:bg-white hover:text-[#1A1A1C]']">
                            <i class="bi bi-grid-1x2"></i>
                            <span>Dashboard</span>
                        </Link>
                        <Link href="/shipments" :class="['flex items-center gap-2.5 px-3 py-2 rounded-xl transition', $page.url.startsWith('/shipments') ? 'bg-[#1A1A1C] text-white font-semibold shadow-xs' : 'text-[#706E6B] hover:bg-white hover:text-[#1A1A1C]']">
                            <i class="bi bi-box-seam"></i>
                            <span>Shipments</span>
                        </Link>

                        <div class="text-[10px] font-bold uppercase tracking-wider text-[#706E6B] px-3 pt-4 pb-1">Master Data</div>
                        <Link href="/carriers" :class="['flex items-center gap-2.5 px-3 py-2 rounded-xl transition', $page.url.startsWith('/carriers') ? 'bg-[#1A1A1C] text-white font-semibold shadow-xs' : 'text-[#706E6B] hover:bg-white hover:text-[#1A1A1C]']">
                            <i class="bi bi-truck"></i>
                            <span>Carriers</span>
                        </Link>
                        <Link href="/locations" :class="['flex items-center gap-2.5 px-3 py-2 rounded-xl transition', $page.url.startsWith('/locations') ? 'bg-[#1A1A1C] text-white font-semibold shadow-xs' : 'text-[#706E6B] hover:bg-white hover:text-[#1A1A1C]']">
                            <i class="bi bi-geo-alt"></i>
                            <span>Locations</span>
                        </Link>
                        <Link v-if="isAdmin" href="/users" :class="['flex items-center gap-2.5 px-3 py-2 rounded-xl transition', $page.url.startsWith('/users') ? 'bg-[#1A1A1C] text-white font-semibold shadow-xs' : 'text-[#706E6B] hover:bg-white hover:text-[#1A1A1C]']">
                            <i class="bi bi-people"></i>
                            <span>Users & RBAC</span>
                        </Link>
                    </nav>
                </div>

                <!-- Sidebar Footer -->
                <div class="p-4 border-t border-[#E5E0D5]">
                    <button @click="logout" class="w-full flex items-center gap-2 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 rounded-xl transition">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Desktop Top Bar -->
                <header class="hidden lg:flex sticky top-0.5 z-30 bg-white/70 backdrop-blur-md border-b border-[#E5E0D5] px-8 py-3 items-center justify-between">
                    <div class="flex items-center gap-2 text-xs text-[#706E6B] font-mono">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Sistem Operasi Aktif &bull; Role: <strong class="text-[#1A1A1C] uppercase font-bold">{{ user.role }}</strong></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <NotificationBell />
                        <div class="h-4 w-px bg-[#E5E0D5]"></div>
                        <div class="text-right">
                            <div class="text-xs font-bold text-[#1A1A1C]">{{ user.name }}</div>
                            <div class="text-[10px] text-[#706E6B] font-mono">{{ user.email }}</div>
                        </div>
                    </div>
                </header>

                <!-- Mobile Header -->
                <header class="lg:hidden sticky top-0.5 z-30 bg-white/70 backdrop-blur-md border-b border-[#E5E0D5] px-4 py-3 flex items-center justify-between">
                    <button @click="sidebarOpen = true" class="p-1.5 rounded-lg border border-[#E5E0D5] text-[#1A1A1C]">
                        <i class="bi bi-list text-xl"></i>
                    </button>
                    <span class="font-bold text-base">CargoTrace</span>
                    <div class="flex items-center gap-2">
                        <NotificationBell />
                        <button @click="logout" class="text-red-600 p-1">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </div>
                </header>

                <!-- Page Content Body -->
                <main class="flex-1 p-4 lg:p-8 max-w-7xl mx-auto w-full">
                    <!-- Flash Messages -->
                    <div v-if="flash.success" class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-2 text-sm font-medium">
                        <i class="bi bi-check-circle-fill text-emerald-600"></i>
                        <span>{{ flash.success }}</span>
                    </div>

                    <div v-if="flash.error" class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-2 text-sm font-medium">
                        <i class="bi bi-exclamation-triangle-fill text-rose-600"></i>
                        <span>{{ flash.error }}</span>
                    </div>

                    <slot />
                </main>
            </div>
        </div>

        <!-- Customer Main View Container -->
        <main v-if="isCustomer" class="flex-1 p-4 lg:p-8 max-w-7xl mx-auto w-full">
            <div v-if="flash.success" class="mb-5 p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-2 text-sm font-medium">
                <i class="bi bi-check-circle-fill text-emerald-600"></i>
                <span>{{ flash.success }}</span>
            </div>

            <div v-if="flash.error" class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center gap-2 text-sm font-medium">
                <i class="bi bi-exclamation-triangle-fill text-rose-600"></i>
                <span>{{ flash.error }}</span>
            </div>

            <slot />
        </main>
    </div>
</template>
