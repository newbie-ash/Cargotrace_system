<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    locations: {
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
const type = ref(props.filters.type || '');

const filter = () => {
    router.get('/locations', {
        search: search.value || undefined,
        type: type.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const deleteLocation = (location) => {
    if (confirm(`Apakah Anda yakin ingin menghapus lokasi ${location.name}?`)) {
        router.delete(`/locations/${location.id}`);
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Master Data Locations" />

        <!-- Header -->
        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-6 mb-6 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-white">Daftar Hub & Pelabuhan Global</h1>
                <p class="text-xs text-slate-400 mt-0.5">Master data simpul rute logistik maritim, bandara, dan gudang distribusi.</p>
            </div>
            <Link v-if="isAdmin" href="/locations/create" class="px-4 py-2 bg-white hover:bg-cyan-50 text-slate-950 text-xs font-bold rounded-full transition inline-flex items-center gap-2 shadow-lg shadow-white/10">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Lokasi</span>
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
                    placeholder="Cari kode, nama, kota, atau negara..." 
                    class="w-full h-10 pl-9 pr-3 bg-slate-950/70 border border-white/15 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-400/40"
                />
            </div>
            <select v-model="type" @change="filter" class="h-10 px-3 bg-slate-950/70 border border-white/15 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-cyan-400/40 sm:w-48">
                <option value="">Semua Fasilitas</option>
                <option value="PORT">PORT (Pelabuhan Laut)</option>
                <option value="AIRPORT">AIRPORT (Bandara Kargo)</option>
                <option value="WAREHOUSE">WAREHOUSE (Gudang)</option>
                <option value="HUB">HUB (Distribusi)</option>
            </select>
            <button @click="filter" class="h-10 px-5 bg-white/10 hover:bg-white/15 border border-white/15 rounded-xl text-xs font-bold text-white transition cursor-pointer">
                Filter
            </button>
        </div>

        <!-- Locations Table -->
        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-950/70 border-b border-white/10 text-slate-400 font-mono font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3.5 px-4">UN/LOCODE</th>
                            <th class="py-3.5 px-4">Fasilitas / Hub</th>
                            <th class="py-3.5 px-4">Kota & Negara</th>
                            <th class="py-3.5 px-4">Tipe</th>
                            <th class="py-3.5 px-4">Koordinat GPS</th>
                            <th class="py-3.5 px-4">Shipments</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        <tr v-for="location in locations.data" :key="location.id" class="hover:bg-white/[0.04] transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-cyan-400">{{ location.code }}</td>
                            <td class="py-3.5 px-4 font-semibold text-white">{{ location.name }}</td>
                            <td class="py-3.5 px-4 text-slate-300">
                                <div>{{ location.city }}</div>
                                <div class="text-[10px] font-semibold uppercase text-slate-400">{{ location.country }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span
                                    :class="[
                                        'px-2.5 py-0.5 rounded-md border font-mono text-[10px] font-bold',
                                        location.type === 'PORT'
                                            ? 'bg-cyan-500/15 text-cyan-300 border-cyan-500/30'
                                            : location.type === 'AIRPORT'
                                              ? 'bg-purple-500/15 text-purple-300 border-purple-500/30'
                                              : 'bg-slate-500/20 text-slate-300 border-slate-400/30',
                                    ]"
                                >
                                    {{ location.type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-300 text-[11px]">
                                {{ Number(location.latitude).toFixed(4) }}, {{ Number(location.longitude).toFixed(4) }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-300">
                                <span>{{ (location.origin_shipments_count || 0) + (location.destination_shipments_count || 0) }} rute</span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <Link :href="`/locations/${location.id}`" class="p-1.5 rounded-lg border border-white/15 bg-white/5 hover:bg-white/15 text-slate-200 transition" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </Link>
                                    <Link v-if="isAdmin" :href="`/locations/${location.id}/edit`" class="p-1.5 rounded-lg border border-white/15 bg-white/5 hover:bg-white/15 text-slate-200 transition" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </Link>
                                    <button v-if="isAdmin" @click="deleteLocation(location)" class="p-1.5 rounded-lg border border-rose-500/25 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 transition cursor-pointer" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!locations.data?.length">
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                Tidak ada data lokasi yang sesuai filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="locations.links?.length > 3" class="p-4 border-t border-white/10 flex items-center justify-between text-xs text-slate-400">
                <div>Menampilkan <strong class="text-white">{{ locations.from || 0 }}</strong> - <strong class="text-white">{{ locations.to || 0 }}</strong> dari <strong class="text-white">{{ locations.total }}</strong> lokasi</div>
                <div class="flex gap-1">
                    <Link 
                        v-for="(link, i) in locations.links" 
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
