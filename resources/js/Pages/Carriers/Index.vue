<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    carriers: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');

const search = ref(props.filters.search || '');
const mode = ref(props.filters.mode || '');

const filter = () => {
    router.get('/carriers', {
        search: search.value || undefined,
        mode: mode.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const deleteCarrier = (carrier) => {
    if (confirm(`Apakah Anda yakin ingin menghapus carrier ${carrier.name}?`)) {
        router.delete(`/carriers/${carrier.id}`);
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Master Data Carriers" />

        <!-- Header -->
        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-6 mb-6 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-white">Daftar Carrier & Armada Logistik</h1>
                <p class="text-xs text-slate-400 mt-0.5">Penyedia jasa transportasi kargo laut, udara, darat, dan multimodal.</p>
            </div>
            <Link v-if="isAdmin" href="/carriers/create" class="px-4 py-2 bg-white hover:bg-cyan-50 text-slate-950 text-xs font-bold rounded-full transition inline-flex items-center gap-2 shadow-lg shadow-white/10">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Carrier</span>
            </Link>
        </div>

        <!-- Filter Bar -->
        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-4 mb-6 shadow-xl flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-3.5 top-3 text-slate-500"></i>
                <input 
                    v-model="search" 
                    @keyup.enter="filter"
                    type="text" 
                    placeholder="Cari kode atau nama carrier..." 
                    class="w-full h-10 pl-9 pr-3 bg-slate-950/70 border border-white/15 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-400/40"
                />
            </div>
            <select v-model="mode" @change="filter" class="h-10 px-3 bg-slate-950/70 border border-white/15 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-cyan-400/40 sm:w-48">
                <option value="">Semua Moda</option>
                <option value="SEA">SEA (Laut)</option>
                <option value="AIR">AIR (Udara)</option>
                <option value="LAND">LAND (Darat)</option>
                <option value="MULTIMODAL">MULTIMODAL</option>
            </select>
            <button @click="filter" class="h-10 px-5 bg-white/10 hover:bg-white/15 border border-white/15 rounded-xl text-xs font-bold text-white transition cursor-pointer">
                Filter
            </button>
        </div>

        <!-- Carriers Table -->
        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-950/70 border-b border-white/10 text-slate-400 font-mono font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3.5 px-4">Kode</th>
                            <th class="py-3.5 px-4">Nama Carrier</th>
                            <th class="py-3.5 px-4">Moda</th>
                            <th class="py-3.5 px-4">Kontak</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Shipments</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        <tr v-for="carrier in carriers.data" :key="carrier.id" class="hover:bg-white/[0.04] transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-cyan-400">{{ carrier.code }}</td>
                            <td class="py-3.5 px-4 font-semibold text-white">{{ carrier.name }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-md bg-cyan-500/15 text-cyan-300 border border-cyan-500/30 font-mono text-[10px] font-bold">
                                    {{ carrier.transport_mode }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-300">
                                <div>{{ carrier.contact_email || '-' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ carrier.contact_phone || '' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span v-if="carrier.is_active" class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 font-bold text-[10px]">
                                    Aktif
                                </span>
                                <span v-else class="px-2.5 py-0.5 rounded-full bg-slate-500/20 text-slate-400 border border-slate-400/30 font-bold text-[10px]">
                                    Nonaktif
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-300">{{ carrier.shipments_count }} items</td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <Link :href="`/carriers/${carrier.id}`" class="p-1.5 rounded-lg border border-white/15 bg-white/5 hover:bg-white/15 text-slate-200 transition" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </Link>
                                    <Link v-if="isAdmin" :href="`/carriers/${carrier.id}/edit`" class="p-1.5 rounded-lg border border-white/15 bg-white/5 hover:bg-white/15 text-slate-200 transition" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </Link>
                                    <button v-if="isAdmin" @click="deleteCarrier(carrier)" class="p-1.5 rounded-lg border border-rose-500/25 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 transition cursor-pointer" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!carriers.data?.length">
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Tidak ada data carrier yang cocok.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="carriers.links?.length > 3" class="p-4 border-t border-white/10 flex items-center justify-between text-xs text-slate-400">
                <div>Menampilkan <strong class="text-white">{{ carriers.from || 0 }}</strong> - <strong class="text-white">{{ carriers.to || 0 }}</strong> dari <strong class="text-white">{{ carriers.total }}</strong> carrier</div>
                <div class="flex gap-1">
                    <Link 
                        v-for="(link, i) in carriers.links" 
                        :key="i" 
                        :href="link.url || '#'" 
                        :class="['px-3 py-1 rounded-lg border font-mono text-xs transition', link.active ? 'bg-white text-slate-950 font-bold border-white shadow-sm' : 'border-white/15 bg-white/5 hover:bg-white/15 text-slate-300', !link.url ? 'opacity-30 pointer-events-none' : '']"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
