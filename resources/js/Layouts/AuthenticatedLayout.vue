<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import NotificationBell from '@/Components/NotificationBell.vue';
import DelixieLogo from '@/Components/DelixieLogo.vue';
import DynamicIslandToast from '@/Components/DynamicIslandToast.vue';
import SkeletonLoader from '@/Components/SkeletonLoader.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user || {});

const isAdmin = computed(() => user.value.role === 'admin');
const isOperator = computed(() => user.value.role === 'operator');
const isCustomer = computed(() => user.value.role === 'customer');

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);
const quickSearch = ref('');
const quickSearchInput = ref(null);
const isNavigating = ref(false);
const navSkeletonVariant = ref('cards');
let navTimer = null;
let removeStartListener = null;
let removeFinishListener = null;

const toggleCollapse = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    try {
        localStorage.setItem('delixie_sidebar_collapsed', String(sidebarCollapsed.value));
    } catch {
        // Ignore storage quota / privacy mode errors
    }
};

const submitQuickSearch = () => {
    const term = quickSearch.value.trim();
    if (!term) return;
    router.get('/shipments', { search: term });
};

const handleKeydown = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        quickSearchInput.value?.focus();
    }
};

const logout = () => {
    router.post('/logout');
};

onMounted(() => {
    try {
        sidebarCollapsed.value = localStorage.getItem('delixie_sidebar_collapsed') === 'true';
    } catch {
        sidebarCollapsed.value = false;
    }
    window.addEventListener('keydown', handleKeydown);

    removeStartListener = router.on('start', (event) => {
        if (event.detail?.visit?.preserveState || event.detail?.visit?.prefetch) return;
        const path = event.detail?.visit?.url?.pathname || '';
        if (/^\/shipments\/\d+/.test(path)) {
            navSkeletonVariant.value = 'detail';
        } else if (/^\/(shipments|carriers|locations|users)/.test(path)) {
            navSkeletonVariant.value = 'table';
        } else {
            navSkeletonVariant.value = 'cards';
        }
        if (navTimer) clearTimeout(navTimer);
        navTimer = setTimeout(() => {
            isNavigating.value = true;
        }, 120);
    });

    removeFinishListener = router.on('finish', () => {
        if (navTimer) clearTimeout(navTimer);
        isNavigating.value = false;
    });
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
    if (navTimer) clearTimeout(navTimer);
    if (removeStartListener) removeStartListener();
    if (removeFinishListener) removeFinishListener();
});
</script>

<template>
    <div class="min-h-screen bg-transparent text-[#2E1E16] flex flex-col font-sans selection:bg-[#2563EB] selection:text-white">
        <!-- Dynamic Island Flash Notification -->
        <DynamicIslandToast />

        <!-- Top Subtle Progress Beam -->
        <div class="h-0.5 relative sticky top-0 z-50 overflow-hidden">
            <div class="h-full bg-gradient-to-r from-[#2E1E16] via-[#2563EB] to-[#93C5FD]"></div>
            <div
                v-if="isNavigating"
                class="absolute inset-0 bg-gradient-to-r from-transparent via-[#2563EB] to-transparent animate-pulse"
                aria-hidden="true"
            ></div>
        </div>

        <!-- Customer Layout (Opsi A: Desktop Floating Liquid Glass Left Sidebar + Mobile Floating Bottom Bar) -->
        <div v-if="isCustomer" class="flex flex-1 min-h-screen">
            <!-- Desktop Floating Liquid Glass Left Sidebar -->
            <aside
                class="hidden lg:flex lg:flex-col lg:justify-between w-64 shrink-0 m-4 mr-0 rounded-3xl sticky top-4 overflow-hidden relative transition-all duration-300"
                style="
                    height: calc(100vh - 2rem);
                    background: linear-gradient(160deg, #2E1E16 0%, #241610 60%, #1A0F0A 100%);
                    backdrop-filter: blur(24px) saturate(140%);
                    -webkit-backdrop-filter: blur(24px) saturate(140%);
                    border: 1.5px solid rgba(255, 255, 255, 0.15);
                    box-shadow: 0 24px 50px -10px rgba(36, 22, 16, 0.45), inset 0 1.5px 1px 0 rgba(255, 255, 255, 0.20);
                "
            >
                <!-- Specular Top Light -->
                <div class="absolute inset-x-6 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-white/40 to-transparent pointer-events-none" aria-hidden="true"></div>

                <div class="p-4 flex flex-col flex-1 overflow-y-auto relative z-10">
                    <!-- Brand Header in Frost Pill -->
                    <Link
                        href="/dashboard"
                        class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl bg-white/95 backdrop-blur-md shadow-sm hover:bg-white transition mb-5 group"
                    >
                        <DelixieLogo size-class="w-7 h-7" wordmark-class="font-extrabold text-lg tracking-tight text-[#2E1E16]" />
                        <span class="text-[9px] font-mono font-bold px-1.5 py-0.5 rounded-md bg-[#FAF7F2] text-[#2563EB] uppercase">PORTAL</span>
                    </Link>

                    <!-- Customer Company Badge -->
                    <div class="rounded-2xl bg-white/10 border border-white/15 p-3.5 mb-5 backdrop-blur-md">
                        <div class="text-[10px] font-mono uppercase tracking-wider text-[#93C5FD] font-bold">Akun Klien</div>
                        <div class="font-bold text-xs text-white truncate mt-0.5">{{ user.name }}</div>
                        <div class="text-[10px] font-mono text-[#EDE5DB] truncate mt-0.5">{{ user.company_name || user.email }}</div>
                    </div>

                    <!-- Navigation Links -->
                    <nav class="space-y-1.5 text-xs font-semibold" aria-label="Navigasi Customer">
                        <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#93C5FD]/80 px-3 pb-1">
                            Menu Utama
                        </div>
                        <Link
                            href="/dashboard"
                            class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl transition"
                            :class="$page.url.startsWith('/dashboard')
                                ? 'bg-[#2563EB] text-white font-bold shadow-lg shadow-blue-500/30'
                                : 'text-[#EDE5DB] hover:bg-white/12 hover:text-white'"
                        >
                            <i class="bi bi-grid-1x2-fill text-sm shrink-0"></i>
                            <span>Dashboard Tracking</span>
                        </Link>
                        <Link
                            href="/shipments"
                            class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl transition"
                            :class="$page.url.startsWith('/shipments')
                                ? 'bg-[#2563EB] text-white font-bold shadow-lg shadow-blue-500/30'
                                : 'text-[#EDE5DB] hover:bg-white/12 hover:text-white'"
                        >
                            <i class="bi bi-box-seam-fill text-sm shrink-0"></i>
                            <span>Kargo Saya</span>
                        </Link>
                        <Link
                            href="/documents"
                            class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl transition"
                            :class="$page.url.startsWith('/documents')
                                ? 'bg-[#2563EB] text-white font-bold shadow-lg shadow-blue-500/30'
                                : 'text-[#EDE5DB] hover:bg-white/12 hover:text-white'"
                        >
                            <i class="bi bi-file-earmark-text-fill text-sm shrink-0"></i>
                            <span>Dokumen &amp; e-B/L</span>
                        </Link>
                        <Link
                            href="/calculator"
                            class="flex items-center gap-3 px-3.5 py-2.5 rounded-2xl transition"
                            :class="$page.url.startsWith('/calculator')
                                ? 'bg-[#2563EB] text-white font-bold shadow-lg shadow-blue-500/30'
                                : 'text-[#EDE5DB] hover:bg-white/12 hover:text-white'"
                        >
                            <i class="bi bi-calculator-fill text-sm shrink-0"></i>
                            <span>Kalkulator Tarif &amp; CO₂</span>
                        </Link>

                        <div class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#93C5FD]/80 px-3 pt-4 pb-1">
                            Akses Cepat
                        </div>
                        <a
                            href="/#utility-hub"
                            target="_blank"
                            class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-[#EDE5DB] hover:bg-white/12 hover:text-white transition"
                        >
                            <span class="flex items-center gap-3">
                                <i class="bi bi-globe text-sm shrink-0"></i>
                                <span>Portal Publik</span>
                            </span>
                            <i class="bi bi-arrow-up-right text-[10px] text-white/50"></i>
                        </a>
                    </nav>
                </div>

                <!-- Footer User / Live Telemetry Status & Logout -->
                <div class="p-4 border-t border-white/10 relative z-10 space-y-3">
                    <div class="flex items-center gap-2 text-[11px] font-mono text-[#93C5FD]">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                        <span class="truncate">Live Telemetry Aktif</span>
                    </div>
                    <button
                        @click="logout"
                        class="w-full flex items-center justify-center gap-2 py-2.5 px-3 text-xs font-bold text-rose-100 bg-rose-500/20 hover:bg-rose-500/35 border border-rose-300/25 rounded-2xl transition cursor-pointer"
                    >
                        <i class="bi bi-box-arrow-right text-sm shrink-0"></i>
                        <span>Keluar Akun</span>
                    </button>
                </div>
            </aside>

            <!-- Customer Main Content Column -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Customer Floating Liquid Pill Header -->
                <div class="sticky top-2 z-30 px-4 lg:px-6 pt-2 pb-1 pointer-events-none flex justify-center">
                    <header
                        class="pointer-events-auto w-full rounded-full px-4 sm:px-6 py-2 flex items-center justify-between gap-3 backdrop-blur-2xl relative overflow-visible transition-all duration-300"
                        style="
                            background: rgba(255, 255, 255, 0.85);
                            backdrop-filter: blur(20px) saturate(140%);
                            -webkit-backdrop-filter: blur(20px) saturate(140%);
                            border: 1px solid rgba(255, 255, 255, 0.95);
                            box-shadow: 0 10px 30px rgba(46, 30, 22, 0.06);
                        "
                    >
                        <!-- Specular Top Highlight -->
                        <div class="absolute inset-0 rounded-full overflow-hidden pointer-events-none" aria-hidden="true">
                            <div class="absolute inset-x-8 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-white/95 to-transparent"></div>
                        </div>

                        <!-- Mobile Brand -->
                        <Link href="/dashboard" class="lg:hidden flex items-center shrink-0 relative z-10">
                            <DelixieLogo size-class="w-6 h-6" wordmark-class="font-extrabold text-base tracking-tight text-[#2E1E16]" />
                        </Link>

                        <!-- Desktop Context Breadcrumb -->
                        <div class="hidden lg:flex items-center gap-2 relative z-10 text-xs">
                            <span class="font-bold text-[#2E1E16]">Customer Portal</span>
                            <span class="text-[#5A4A40]/40">/</span>
                            <span class="text-[11px] font-mono text-[#5A4A40] font-semibold">{{ user.company_name || 'Global Cargo Tracking' }}</span>
                        </div>

                        <!-- Quick Track Search in Glass Pill -->
                        <form @submit.prevent="submitQuickSearch" class="flex-1 max-w-md relative z-10">
                            <i class="bi bi-search absolute left-3.5 top-2 text-[#5A4A40] text-xs pointer-events-none"></i>
                            <input
                                ref="quickSearchInput"
                                v-model="quickSearch"
                                type="text"
                                placeholder="Lacak cepat No. Resi / Kargo..."
                                aria-label="Cari Nomor Resi"
                                class="w-full h-8 pl-9 pr-14 rounded-full bg-[#FAF7F2] border border-[#2E1E16]/15 text-xs text-[#2E1E16] placeholder-[#5A4A40]/60 focus:outline-none focus:ring-2 focus:ring-[#2563EB]/40 focus:border-[#2563EB] transition shadow-inner"
                            />
                            <kbd class="hidden sm:inline-block absolute right-3 top-1.5 px-1.5 py-0.5 rounded bg-white/80 border border-[#2E1E16]/10 font-mono text-[9px] font-bold text-[#5A4A40] pointer-events-none">
                                Ctrl+K
                            </kbd>
                        </form>

                        <!-- Right Utilities: Bell + Avatar -->
                        <div class="flex items-center gap-2.5 shrink-0 relative z-10">
                            <NotificationBell />
                            <div class="hidden sm:flex items-center gap-2 pl-2 border-l border-[#2E1E16]/15">
                                <div class="w-7 h-7 rounded-full bg-[#2E1E16] text-[#FAF7F2] flex items-center justify-center font-mono font-bold text-xs shadow-xs">
                                    {{ (user.name || 'C').slice(0, 2).toUpperCase() }}
                                </div>
                                <div class="hidden xl:block text-left">
                                    <div class="text-xs font-bold text-[#2E1E16] leading-tight">{{ user.name }}</div>
                                    <div class="text-[10px] text-[#5A4A40] font-mono leading-tight">Customer</div>
                                </div>
                            </div>
                        </div>
                    </header>
                </div>

                <!-- Customer Page Content -->
                <main class="flex-1 p-4 lg:p-6 pb-24 lg:pb-8 max-w-7xl mx-auto w-full">
                    <SkeletonLoader v-if="isNavigating" :variant="navSkeletonVariant" />
                    <slot v-else />
                </main>
            </div>

            <!-- Mobile Floating Glass Bottom Bar (< 1024px) -->
            <nav
                aria-label="Navigasi Mobile Customer"
                class="lg:hidden fixed bottom-3 inset-x-4 z-40 rounded-full px-3 py-2 flex items-center justify-around shadow-2xl"
                style="
                    background: rgba(46, 30, 22, 0.94);
                    backdrop-filter: blur(20px) saturate(140%);
                    -webkit-backdrop-filter: blur(20px) saturate(140%);
                    border: 1px solid rgba(255, 255, 255, 0.20);
                    box-shadow: 0 16px 32px rgba(46, 30, 22, 0.35);
                "
            >
                <Link
                    href="/dashboard"
                    class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-full transition text-[10px] font-semibold"
                    :class="$page.url.startsWith('/dashboard') ? 'bg-[#2563EB] text-white font-bold' : 'text-[#EDE5DB]'"
                >
                    <i class="bi bi-grid-1x2-fill text-sm"></i>
                    <span>Dashboard</span>
                </Link>
                <Link
                    href="/shipments"
                    class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-full transition text-[10px] font-semibold"
                    :class="$page.url.startsWith('/shipments') ? 'bg-[#2563EB] text-white font-bold' : 'text-[#EDE5DB]'"
                >
                    <i class="bi bi-box-seam-fill text-sm"></i>
                    <span>Kargo</span>
                </Link>
                <button
                    type="button"
                    @click="quickSearchInput?.focus()"
                    class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-full transition text-[10px] font-semibold text-[#EDE5DB] cursor-pointer"
                >
                    <i class="bi bi-search text-sm"></i>
                    <span>Lacak</span>
                </button>
                <button
                    type="button"
                    @click="logout"
                    class="flex flex-col items-center gap-0.5 px-3 py-1 rounded-full transition text-[10px] font-semibold text-rose-200 cursor-pointer"
                >
                    <i class="bi bi-box-arrow-right text-sm"></i>
                    <span>Keluar</span>
                </button>
            </nav>
        </div>

        <!-- Admin & Operator Layout (Collapsible Sidebar + Main Area) -->
        <div v-else class="flex flex-1">
            <!-- Mobile Sidebar Backdrop -->
            <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-[#2E1E16]/40 z-40 lg:hidden backdrop-blur-sm"></div>

            <!-- Desktop & Mobile Frosted Sidebar -->
            <aside
                :class="[
                    'fixed lg:sticky top-0.5 inset-y-0 left-0 z-40 bg-white/85 backdrop-blur-2xl border-r border-[#2E1E16]/10 flex flex-col justify-between transition-all duration-200 lg:translate-x-0 shadow-[4px_0_32px_rgba(46,30,22,0.05)] w-64',
                    sidebarCollapsed ? 'lg:w-[76px]' : 'lg:w-64',
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                ]"
                style="height: calc(100vh - 2px);"
            >
                <div class="flex flex-col flex-1 overflow-y-auto p-3.5">
                    <!-- Brand Header -->
                    <div
                        class="flex items-center pb-4 mb-4 border-b border-[#2E1E16]/10"
                        :class="sidebarCollapsed ? 'lg:justify-center justify-between' : 'justify-between'"
                    >
                        <Link href="/dashboard" class="flex items-center hover:opacity-90 transition group overflow-hidden">
                            <DelixieLogo
                                size-class="w-7 h-7"
                                :show-wordmark="!sidebarCollapsed"
                                wordmark-class="font-extrabold text-xl tracking-tight text-[#2E1E16]"
                            />
                        </Link>
                        <button
                            @click="sidebarOpen = false"
                            class="lg:hidden p-1 text-[#5A4A40] hover:text-[#2E1E16] cursor-pointer"
                            aria-label="Tutup Menu"
                        >
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <!-- User Role Badge -->
                    <div
                        class="rounded-2xl bg-[#FAF7F2] border border-[#2E1E16]/10 mb-5 transition-all"
                        :class="sidebarCollapsed ? 'lg:p-2 p-3 lg:flex lg:justify-center' : 'p-3'"
                        :title="sidebarCollapsed ? `${user.name} (${isAdmin ? 'ADMINISTRATOR' : 'OPERATOR'})` : undefined"
                    >
                        <template v-if="sidebarCollapsed">
                            <div class="hidden lg:flex w-8 h-8 rounded-xl bg-[#2E1E16] text-[#FAF7F2] items-center justify-center font-mono font-bold text-xs">
                                {{ (user.name || 'U').slice(0, 2).toUpperCase() }}
                            </div>
                            <div class="lg:hidden">
                                <div class="font-bold text-xs text-[#2E1E16] truncate">{{ user.name }}</div>
                                <div class="text-[10px] font-mono font-bold text-[#2563EB] uppercase tracking-wider mt-0.5">
                                    {{ isAdmin ? 'ADMINISTRATOR' : 'LOGISTICS OPERATOR' }}
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div class="font-bold text-xs text-[#2E1E16] truncate">{{ user.name }}</div>
                            <div class="text-[10px] font-mono font-bold text-[#2563EB] uppercase tracking-wider mt-0.5">
                                {{ isAdmin ? 'ADMINISTRATOR' : 'LOGISTICS OPERATOR' }}
                            </div>
                        </template>
                    </div>

                    <!-- Navigation Links -->
                    <nav class="space-y-1 text-xs font-semibold" aria-label="Navigasi Operasional">
                        <div
                            v-if="!sidebarCollapsed"
                            class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#5A4A40] px-3 pt-2 pb-1"
                        >
                            Operasional
                        </div>

                        <Link
                            href="/dashboard"
                            :title="sidebarCollapsed ? 'Dashboard' : undefined"
                            :class="[
                                'flex items-center gap-2.5 py-2.5 rounded-xl transition',
                                sidebarCollapsed ? 'lg:justify-center lg:px-0 px-3.5' : 'px-3.5',
                                $page.url === '/dashboard' ? 'bg-[#2563EB] text-white font-bold shadow-md shadow-blue-500/25' : 'text-[#5A4A40] hover:bg-[#FAF7F2] hover:text-[#2E1E16]'
                            ]"
                        >
                            <i class="bi bi-grid-1x2 text-sm shrink-0"></i>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Dashboard</span>
                        </Link>

                        <Link
                            href="/shipments"
                            :title="sidebarCollapsed ? 'Shipments' : undefined"
                            :class="[
                                'flex items-center gap-2.5 py-2.5 rounded-xl transition',
                                sidebarCollapsed ? 'lg:justify-center lg:px-0 px-3.5' : 'px-3.5',
                                $page.url.startsWith('/shipments') ? 'bg-[#2563EB] text-white font-bold shadow-md shadow-blue-500/25' : 'text-[#5A4A40] hover:bg-[#FAF7F2] hover:text-[#2E1E16]'
                            ]"
                        >
                            <i class="bi bi-box-seam text-sm shrink-0"></i>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Shipments</span>
                        </Link>

                        <div
                            v-if="!sidebarCollapsed"
                            class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#5A4A40] px-3 pt-5 pb-1"
                        >
                            Master Data
                        </div>

                        <Link
                            href="/carriers"
                            :title="sidebarCollapsed ? 'Carriers' : undefined"
                            :class="[
                                'flex items-center gap-2.5 py-2.5 rounded-xl transition',
                                sidebarCollapsed ? 'lg:justify-center lg:px-0 px-3.5' : 'px-3.5',
                                $page.url.startsWith('/carriers') ? 'bg-[#2563EB] text-white font-bold shadow-md shadow-blue-500/25' : 'text-[#5A4A40] hover:bg-[#FAF7F2] hover:text-[#2E1E16]'
                            ]"
                        >
                            <i class="bi bi-truck text-sm shrink-0"></i>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Carriers</span>
                        </Link>

                        <Link
                            href="/locations"
                            :title="sidebarCollapsed ? 'Locations' : undefined"
                            :class="[
                                'flex items-center gap-2.5 py-2.5 rounded-xl transition',
                                sidebarCollapsed ? 'lg:justify-center lg:px-0 px-3.5' : 'px-3.5',
                                $page.url.startsWith('/locations') ? 'bg-[#2563EB] text-white font-bold shadow-md shadow-blue-500/25' : 'text-[#5A4A40] hover:bg-[#FAF7F2] hover:text-[#2E1E16]'
                            ]"
                        >
                            <i class="bi bi-geo-alt text-sm shrink-0"></i>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Locations &amp; Hubs</span>
                        </Link>

                        <Link
                            href="/exceptions"
                            :title="sidebarCollapsed ? 'Exceptions' : undefined"
                            :class="[
                                'flex items-center gap-2.5 py-2.5 rounded-xl transition',
                                sidebarCollapsed ? 'lg:justify-center lg:px-0 px-3.5' : 'px-3.5',
                                $page.url.startsWith('/exceptions') ? 'bg-[#2563EB] text-white font-bold shadow-md shadow-blue-500/25' : 'text-[#5A4A40] hover:bg-[#FAF7F2] hover:text-[#2E1E16]'
                            ]"
                        >
                            <i class="bi bi-exclamation-triangle text-sm shrink-0"></i>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Exception Management</span>
                        </Link>

                        <!-- System Administration Section (Admin Only) -->
                        <template v-if="isAdmin">
                            <div
                                v-if="!sidebarCollapsed"
                                class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#5A4A40] px-3 pt-5 pb-1"
                            >
                                Administrasi
                            </div>

                            <Link
                                href="/users"
                                :title="sidebarCollapsed ? 'User RBAC' : undefined"
                                :class="[
                                    'flex items-center gap-2.5 py-2.5 rounded-xl transition',
                                    sidebarCollapsed ? 'lg:justify-center lg:px-0 px-3.5' : 'px-3.5',
                                    $page.url.startsWith('/users') ? 'bg-[#2563EB] text-white font-bold shadow-md shadow-blue-500/25' : 'text-[#5A4A40] hover:bg-[#FAF7F2] hover:text-[#2E1E16]'
                                ]"
                            >
                                <i class="bi bi-people text-sm shrink-0"></i>
                                <span :class="sidebarCollapsed ? 'lg:hidden' : ''">User Management</span>
                            </Link>

                            <Link
                                href="/audit-logs"
                                :title="sidebarCollapsed ? 'Audit Logs' : undefined"
                                :class="[
                                    'flex items-center gap-2.5 py-2.5 rounded-xl transition',
                                    sidebarCollapsed ? 'lg:justify-center lg:px-0 px-3.5' : 'px-3.5',
                                    $page.url.startsWith('/audit-logs') ? 'bg-[#2563EB] text-white font-bold shadow-md shadow-blue-500/25' : 'text-[#5A4A40] hover:bg-[#FAF7F2] hover:text-[#2E1E16]'
                                ]"
                            >
                                <i class="bi bi-shield-check text-sm shrink-0"></i>
                                <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Audit Logs</span>
                            </Link>
                        </template>

                        <div
                            v-if="!sidebarCollapsed"
                            class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#5A4A40] px-3 pt-5 pb-1"
                        >
                            Akses Cepat
                        </div>

                        <a
                            href="/"
                            target="_blank"
                            :title="sidebarCollapsed ? 'Landing Page' : undefined"
                            :class="[
                                'flex items-center gap-2.5 py-2.5 rounded-xl transition text-[#5A4A40] hover:bg-[#FAF7F2] hover:text-[#2E1E16]',
                                sidebarCollapsed ? 'lg:justify-center lg:px-0 px-3.5' : 'px-3.5'
                            ]"
                        >
                            <i class="bi bi-globe text-sm shrink-0"></i>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Landing Page</span>
                            <i v-if="!sidebarCollapsed" class="bi bi-arrow-up-right text-[10px] ml-auto text-slate-400"></i>
                        </a>
                    </nav>
                </div>

                <!-- Footer User Badge & Logout -->
                <div class="p-3 border-t border-[#2E1E16]/10">
                    <button
                        @click="logout"
                        :title="sidebarCollapsed ? 'Keluar' : undefined"
                        :class="[
                            'w-full flex items-center gap-2.5 py-2 px-3 text-xs font-semibold text-rose-700 hover:bg-rose-500/10 rounded-xl transition cursor-pointer',
                            sidebarCollapsed ? 'lg:justify-center lg:px-0' : ''
                        ]"
                    >
                        <i class="bi bi-box-arrow-right text-sm shrink-0"></i>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Keluar</span>
                    </button>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Desktop Top Bar -->
                <header class="hidden lg:flex sticky top-0.5 z-30 bg-white/80 backdrop-blur-2xl border-b border-[#2E1E16]/10 px-6 py-2.5 items-center justify-between gap-4 shadow-xs">
                    <!-- Left: Collapse Toggle + Telemetry Status -->
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="toggleCollapse"
                            class="p-2 rounded-xl border border-[#2E1E16]/15 bg-white/80 hover:bg-[#FAF7F2] text-[#2E1E16] transition cursor-pointer"
                            :title="sidebarCollapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'"
                            :aria-label="sidebarCollapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'"
                        >
                            <i :class="['bi text-sm', sidebarCollapsed ? 'bi-layout-sidebar' : 'bi-layout-sidebar-inset']"></i>
                        </button>

                        <div class="flex items-center gap-2 text-xs text-[#5A4A40] font-mono">
                            <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                            <span>Operasional &bull;</span>
                            <span class="px-2 py-0.5 rounded-md bg-[#2E1E16] text-[#FAF7F2] text-[10px] font-bold uppercase tracking-wider">
                                {{ user.role }}
                            </span>
                        </div>
                    </div>

                    <!-- Center: Quick Shipment Search -->
                    <form @submit.prevent="submitQuickSearch" class="flex-1 max-w-md relative">
                        <i class="bi bi-search absolute left-3.5 top-2.5 text-[#5A4A40] text-xs pointer-events-none"></i>
                        <input
                            ref="quickSearchInput"
                            v-model="quickSearch"
                            type="text"
                            placeholder="Cari cepat No. Shipment, Ref, atau Kargo..."
                            aria-label="Cari cepat pengiriman"
                            class="w-full h-9 pl-9 pr-16 rounded-full bg-[#FAF7F2] border border-[#2E1E16]/15 text-xs text-[#2E1E16] placeholder-[#5A4A40]/60 focus:outline-none focus:ring-2 focus:ring-[#2563EB]/40 focus:border-[#2563EB] transition"
                        />
                        <kbd class="absolute right-3 top-2 px-1.5 py-0.5 rounded bg-white/80 border border-[#2E1E16]/10 font-mono text-[9px] font-bold text-[#5A4A40] pointer-events-none">
                            Ctrl+K
                        </kbd>
                    </form>

                    <!-- Right: Quick Create + Bell + User -->
                    <div class="flex items-center gap-3">
                        <Link
                            v-if="isAdmin || isOperator"
                            href="/shipments/create"
                            class="px-3.5 py-1.5 rounded-full bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-bold transition inline-flex items-center gap-1.5 shadow-sm shadow-blue-500/25"
                        >
                            <i class="bi bi-plus-lg text-[11px]"></i>
                            <span>Buat Kargo</span>
                        </Link>
                        <NotificationBell />
                        <div class="h-4 w-px bg-[#2E1E16]/15"></div>
                        <div class="text-right">
                            <div class="text-xs font-bold text-[#2E1E16]">{{ user.name }}</div>
                            <div class="text-[10px] text-[#5A4A40] font-mono">{{ user.email }}</div>
                        </div>
                    </div>
                </header>

                <!-- Mobile Header -->
                <header class="lg:hidden sticky top-0.5 z-30 bg-white/85 backdrop-blur-2xl border-b border-[#2E1E16]/10 px-4 py-3 flex items-center justify-between">
                    <button @click="sidebarOpen = true" class="p-1.5 rounded-lg border border-[#2E1E16]/15 text-[#2E1E16] cursor-pointer" aria-label="Buka Menu">
                        <i class="bi bi-list text-xl"></i>
                    </button>
                    <DelixieLogo size-class="w-6 h-6" wordmark-class="font-extrabold text-base text-[#2E1E16]" />
                    <div class="flex items-center gap-2">
                        <NotificationBell />
                        <button @click="logout" class="text-rose-700 p-1 cursor-pointer" aria-label="Keluar">
                            <i class="bi bi-box-arrow-right"></i>
                        </button>
                    </div>
                </header>

                <!-- Page Content Body -->
                <main class="flex-1 p-4 lg:p-8 max-w-7xl mx-auto w-full">
                    <SkeletonLoader v-if="isNavigating" :variant="navSkeletonVariant" />
                    <slot v-else />
                </main>
            </div>
        </div>
    </div>
</template>
