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
    <div class="min-h-screen bg-transparent text-[#13293D] flex flex-col font-sans selection:bg-[#67C7C6] selection:text-[#13293D]">
        <!-- Dynamic Island Flash Notification -->
        <DynamicIslandToast />

        <!-- Top Subtle Brand Accent Line / Global Progress Pulse Beam -->
        <div class="h-0.5 relative sticky top-0 z-50 overflow-hidden">
            <div class="h-full bg-gradient-to-r from-[#13293D] via-[#265D7A] to-[#67C7C6]"></div>
            <div
                v-if="isNavigating"
                class="absolute inset-0 bg-gradient-to-r from-transparent via-[#67C7C6] to-transparent animate-pulse"
                aria-hidden="true"
            ></div>
        </div>

        <!-- Customer Floating Liquid Pill Navbar -->
        <div v-if="isCustomer" class="sticky top-3 z-40 px-4 lg:px-8 mt-3 mb-2 pointer-events-none flex justify-center">
            <header
                class="pointer-events-auto w-full max-w-6xl rounded-full px-4 sm:px-6 py-2.5 flex items-center justify-between gap-3 sm:gap-5 backdrop-blur-2xl relative overflow-visible transition-all duration-300"
                style="
                    background: linear-gradient(135deg, rgba(255, 255, 255, 0.90) 0%, rgba(217, 242, 238, 0.50) 50%, rgba(255, 255, 255, 0.82) 100%);
                    box-shadow: inset 0 1.5px 1px 0 rgba(255, 255, 255, 0.95), inset 0 -2px 6px 0 rgba(103, 199, 198, 0.22), 0 14px 36px -8px rgba(19, 41, 61, 0.12);
                    border: 1px solid rgba(255, 255, 255, 0.95);
                "
            >
                <!-- Specular Top Highlight clipped cleanly to pill shape -->
                <div class="absolute inset-0 rounded-full overflow-hidden pointer-events-none" aria-hidden="true">
                    <div class="absolute inset-x-10 top-0 h-[1.5px] bg-gradient-to-r from-transparent via-white/95 to-transparent"></div>
                </div>

                <!-- Brand + Nav Pills -->
                <div class="flex items-center gap-3 sm:gap-6 relative z-10 min-w-0">
                    <Link href="/dashboard" class="flex items-center hover:opacity-90 transition group shrink-0">
                        <DelixieLogo size-class="w-7 h-7" wordmark-class="font-extrabold text-base sm:text-lg tracking-tight text-[#13293D]" />
                    </Link>

                    <nav class="flex items-center gap-1 sm:gap-1.5 text-xs font-semibold" aria-label="Navigasi Customer">
                        <Link
                            href="/dashboard"
                            class="px-3 sm:px-4 py-1.5 rounded-full transition whitespace-nowrap"
                            :class="$page.url.startsWith('/dashboard') ? 'bg-[#13293D] text-[#A7E4DB] font-bold shadow-sm' : 'text-[#265D7A] hover:text-[#13293D] hover:bg-white/75'"
                        >
                            Dashboard
                        </Link>
                        <Link
                            href="/shipments"
                            class="px-3 sm:px-4 py-1.5 rounded-full transition whitespace-nowrap"
                            :class="$page.url.startsWith('/shipments') ? 'bg-[#13293D] text-[#A7E4DB] font-bold shadow-sm' : 'text-[#265D7A] hover:text-[#13293D] hover:bg-white/75'"
                        >
                            Kargo Saya
                        </Link>
                    </nav>
                </div>

                <!-- Right Controls: Quick Track + Notification + User + Logout -->
                <div class="flex items-center gap-2.5 sm:gap-3.5 relative z-10 shrink-0">
                    <form @submit.prevent="submitQuickSearch" class="hidden lg:flex items-center relative">
                        <i class="bi bi-search absolute left-3 text-[#265D7A] text-[11px] pointer-events-none"></i>
                        <input
                            ref="quickSearchInput"
                            v-model="quickSearch"
                            type="text"
                            placeholder="Cari Resi... (Ctrl+K)"
                            aria-label="Cari Nomor Resi"
                            class="w-44 focus:w-56 transition-all duration-200 h-8 pl-8 pr-3 rounded-full bg-white/85 border border-[#265D7A]/20 text-[11px] text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/50 focus:border-[#265D7A]"
                        />
                    </form>

                    <NotificationBell />

                    <div class="text-right hidden md:block">
                        <div class="text-xs font-bold text-[#13293D] leading-tight">{{ user.name }}</div>
                        <div class="text-[10px] text-[#265D7A] font-mono font-bold uppercase tracking-wider">{{ user.company_name || 'Customer' }}</div>
                    </div>

                    <button
                        @click="logout"
                        class="p-2 text-xs font-semibold text-rose-700 hover:bg-rose-500/15 rounded-full transition border border-transparent hover:border-rose-500/30 cursor-pointer"
                        title="Keluar"
                        aria-label="Keluar"
                    >
                        <i class="bi bi-box-arrow-right text-sm"></i>
                    </button>
                </div>
            </header>
        </div>

        <!-- Admin & Operator Layout (Collapsible Sidebar + Main Area) -->
        <div v-else class="flex flex-1">
            <!-- Mobile Sidebar Backdrop -->
            <div v-if="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-[#13293D]/45 z-40 lg:hidden backdrop-blur-sm"></div>

            <!-- Desktop & Mobile Frosted Sidebar -->
            <aside
                :class="[
                    'fixed lg:sticky top-0.5 inset-y-0 left-0 z-40 bg-white/75 backdrop-blur-2xl border-r border-white/85 flex flex-col justify-between transition-all duration-200 lg:translate-x-0 shadow-[4px_0_32px_rgba(19,41,61,0.05)] w-64',
                    sidebarCollapsed ? 'lg:w-[76px]' : 'lg:w-64',
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                ]"
                style="height: calc(100vh - 2px);"
            >
                <div class="flex flex-col flex-1 overflow-y-auto p-3.5">
                    <!-- Brand Header -->
                    <div
                        class="flex items-center pb-4 mb-4 border-b border-[#265D7A]/15"
                        :class="sidebarCollapsed ? 'lg:justify-center justify-between' : 'justify-between'"
                    >
                        <Link href="/dashboard" class="flex items-center hover:opacity-90 transition group overflow-hidden">
                            <DelixieLogo
                                size-class="w-7 h-7"
                                :show-wordmark="!sidebarCollapsed"
                                wordmark-class="font-extrabold text-xl tracking-tight text-[#13293D]"
                            />
                        </Link>
                        <button
                            @click="sidebarOpen = false"
                            class="lg:hidden p-1 text-[#265D7A] hover:text-[#13293D] cursor-pointer"
                            aria-label="Tutup Menu"
                        >
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <!-- User Role Badge -->
                    <div
                        class="rounded-2xl bg-[#EAF6F4] border border-[#265D7A]/15 mb-5 transition-all"
                        :class="sidebarCollapsed ? 'lg:p-2 p-3 lg:flex lg:justify-center' : 'p-3'"
                        :title="sidebarCollapsed ? `${user.name} (${isAdmin ? 'ADMINISTRATOR' : 'OPERATOR'})` : undefined"
                    >
                        <template v-if="sidebarCollapsed">
                            <div class="hidden lg:flex w-8 h-8 rounded-xl bg-[#13293D] text-[#A7E4DB] items-center justify-center font-mono font-bold text-xs">
                                {{ (user.name || 'U').slice(0, 2).toUpperCase() }}
                            </div>
                            <div class="lg:hidden">
                                <div class="font-bold text-xs text-[#13293D] truncate">{{ user.name }}</div>
                                <div class="text-[10px] font-mono font-bold text-[#265D7A] uppercase tracking-wider mt-0.5">
                                    {{ isAdmin ? 'ADMINISTRATOR' : 'LOGISTICS OPERATOR' }}
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <div class="font-bold text-xs text-[#13293D] truncate">{{ user.name }}</div>
                            <div class="text-[10px] font-mono font-bold text-[#265D7A] uppercase tracking-wider mt-0.5">
                                {{ isAdmin ? 'ADMINISTRATOR' : 'LOGISTICS OPERATOR' }}
                            </div>
                        </template>
                    </div>

                    <!-- Navigation Links -->
                    <nav class="space-y-1 text-xs font-semibold" aria-label="Navigasi Operasional">
                        <div
                            v-if="!sidebarCollapsed"
                            class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#265D7A] px-3 pt-2 pb-1"
                        >
                            Operasional
                        </div>

                        <Link
                            href="/dashboard"
                            :title="sidebarCollapsed ? 'Dashboard' : undefined"
                            :class="[
                                'flex items-center gap-2.5 py-2.5 rounded-xl transition',
                                sidebarCollapsed ? 'lg:justify-center lg:px-0 px-3.5' : 'px-3.5',
                                $page.url === '/dashboard' ? 'bg-[#13293D] text-[#A7E4DB] font-bold shadow-md' : 'text-[#265D7A] hover:bg-white/80 hover:text-[#13293D]'
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
                                $page.url.startsWith('/shipments') ? 'bg-[#13293D] text-[#A7E4DB] font-bold shadow-md' : 'text-[#265D7A] hover:bg-white/80 hover:text-[#13293D]'
                            ]"
                        >
                            <i class="bi bi-box-seam text-sm shrink-0"></i>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Shipments</span>
                        </Link>

                        <div
                            v-if="!sidebarCollapsed"
                            class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#265D7A] px-3 pt-5 pb-1"
                        >
                            Master Data
                        </div>
                        <div v-else class="hidden lg:block my-3 border-t border-[#265D7A]/15"></div>

                        <Link
                            href="/carriers"
                            :title="sidebarCollapsed ? 'Carriers' : undefined"
                            :class="[
                                'flex items-center gap-2.5 py-2.5 rounded-xl transition',
                                sidebarCollapsed ? 'lg:justify-center lg:px-0 px-3.5' : 'px-3.5',
                                $page.url.startsWith('/carriers') ? 'bg-[#13293D] text-[#A7E4DB] font-bold shadow-md' : 'text-[#265D7A] hover:bg-white/80 hover:text-[#13293D]'
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
                                $page.url.startsWith('/locations') ? 'bg-[#13293D] text-[#A7E4DB] font-bold shadow-md' : 'text-[#265D7A] hover:bg-white/80 hover:text-[#13293D]'
                            ]"
                        >
                            <i class="bi bi-geo-alt text-sm shrink-0"></i>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Locations</span>
                        </Link>

                        <Link
                            v-if="isAdmin"
                            href="/users"
                            :title="sidebarCollapsed ? 'Users & RBAC' : undefined"
                            :class="[
                                'flex items-center gap-2.5 py-2.5 rounded-xl transition',
                                sidebarCollapsed ? 'lg:justify-center lg:px-0 px-3.5' : 'px-3.5',
                                $page.url.startsWith('/users') ? 'bg-[#13293D] text-[#A7E4DB] font-bold shadow-md' : 'text-[#265D7A] hover:bg-white/80 hover:text-[#13293D]'
                            ]"
                        >
                            <i class="bi bi-people text-sm shrink-0"></i>
                            <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Users &amp; RBAC</span>
                        </Link>
                    </nav>
                </div>

                <!-- Sidebar Footer -->
                <div class="p-3.5 border-t border-[#265D7A]/15">
                    <button
                        @click="logout"
                        :title="sidebarCollapsed ? 'Logout' : undefined"
                        :class="[
                            'w-full flex items-center gap-2 py-2 text-xs font-bold text-rose-700 hover:bg-rose-500/15 rounded-xl transition border border-transparent hover:border-rose-500/30 cursor-pointer',
                            sidebarCollapsed ? 'lg:justify-center lg:px-0 px-3' : 'px-3'
                        ]"
                    >
                        <i class="bi bi-box-arrow-right text-sm shrink-0"></i>
                        <span :class="sidebarCollapsed ? 'lg:hidden' : ''">Logout</span>
                    </button>
                </div>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Desktop Top Bar -->
                <header class="hidden lg:flex sticky top-0.5 z-30 bg-white/70 backdrop-blur-2xl border-b border-white/85 px-6 py-2.5 items-center justify-between gap-4 shadow-xs">
                    <!-- Left: Collapse Toggle + Telemetry Status -->
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="toggleCollapse"
                            class="p-2 rounded-xl border border-[#265D7A]/20 bg-white/80 hover:bg-[#EAF6F4] text-[#265D7A] hover:text-[#13293D] transition cursor-pointer"
                            :title="sidebarCollapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'"
                            :aria-label="sidebarCollapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'"
                        >
                            <i :class="['bi text-sm', sidebarCollapsed ? 'bi-layout-sidebar' : 'bi-layout-sidebar-inset']"></i>
                        </button>

                        <div class="flex items-center gap-2 text-xs text-[#265D7A] font-mono">
                            <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                            <span>Operasional &bull;</span>
                            <span class="px-2 py-0.5 rounded-md bg-[#13293D] text-[#A7E4DB] text-[10px] font-bold uppercase tracking-wider">
                                {{ user.role }}
                            </span>
                        </div>
                    </div>

                    <!-- Center: Quick Shipment Search -->
                    <form @submit.prevent="submitQuickSearch" class="flex-1 max-w-md relative">
                        <i class="bi bi-search absolute left-3.5 top-2.5 text-[#265D7A] text-xs pointer-events-none"></i>
                        <input
                            ref="quickSearchInput"
                            v-model="quickSearch"
                            type="text"
                            placeholder="Cari cepat No. Shipment, Ref, atau Kargo..."
                            aria-label="Cari cepat pengiriman"
                            class="w-full h-9 pl-9 pr-16 rounded-full bg-white/85 border border-[#265D7A]/20 text-xs text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A] transition"
                        />
                        <kbd class="absolute right-3 top-2 px-1.5 py-0.5 rounded bg-[#EAF6F4] border border-[#265D7A]/15 font-mono text-[9px] font-bold text-[#265D7A] pointer-events-none">
                            Ctrl+K
                        </kbd>
                    </form>

                    <!-- Right: Quick Create + Bell + User -->
                    <div class="flex items-center gap-3">
                        <Link
                            v-if="isAdmin || isOperator"
                            href="/shipments/create"
                            class="px-3.5 py-1.5 rounded-full bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold transition inline-flex items-center gap-1.5 shadow-sm"
                        >
                            <i class="bi bi-plus-lg text-[11px]"></i>
                            <span>Buat Kargo</span>
                        </Link>
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
                    <SkeletonLoader v-if="isNavigating" :variant="navSkeletonVariant" />
                    <slot v-else />
                </main>
            </div>
        </div>

        <!-- Customer Main View Container -->
        <main v-if="isCustomer" class="flex-1 p-4 lg:p-8 max-w-7xl mx-auto w-full">
            <SkeletonLoader v-if="isNavigating" :variant="navSkeletonVariant" />
            <slot v-else />
        </main>
    </div>
</template>
