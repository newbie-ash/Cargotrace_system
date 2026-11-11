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
        <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 mb-6 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-white">Daftar Hub & Pelabuhan Global</h1>
                <p class="text-xs font-light text-[#9ABBC8] mt-0.5">Master data simpul rute logistik maritim, bandara, dan gudang distribusi.</p>
            </div>
            <Link v-if="isAdmin" href="/locations/create" class="px-5 py-2 bg-[#274D60] hover:bg-[#0A7075] text-white text-xs font-semibold rounded-full transition inline-flex items-center gap-2 shadow-lg shadow-black/25">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Lokasi</span>
            </Link>
        </div>

        <!-- Filter Bar -->
        <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-4 mb-6 shadow-xl flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-3.5 top-3 text-[#6BA3BE]"></i>
                <input 
                    v-model="search" 
                    @keyup.enter="filter"
                    type="text" 
                    placeholder="Cari kode, nama, kota, atau negara..." 
                    class="w-full h-10 pl-9 pr-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs text-white placeholder-[#6B8B97] focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]"
                />
            </div>
            <select v-model="type" @change="filter" class="h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C] sm:w-48">
                <option value="">Semua Fasilitas</option>
                <option value="PORT">PORT (Pelabuhan Laut)</option>
                <option value="AIRPORT">AIRPORT (Bandara Kargo)</option>
                <option value="WAREHOUSE">WAREHOUSE (Gudang)</option>
                <option value="HUB">HUB (Distribusi)</option>
            </select>
            <button @click="filter" class="h-10 px-5 bg-[#274D60] hover:bg-[#0A7075] border border-white/10 rounded-full text-xs font-semibold text-white transition cursor-pointer">
                Filter
            </button>
        </div>

        <!-- Locations Table -->
        <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#031716]/75 border-b border-white/10 text-[#9ABBC8] font-mono font-bold uppercase tracking-wider text-[10px]">
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
                            <td class="py-3.5 px-4 font-mono font-bold text-[#7DE2E6]">{{ location.code }}</td>
                            <td class="py-3.5 px-4 font-semibold text-white">{{ location.name }}</td>
                            <td class="py-3.5 px-4 text-[#D6E6EA]">
                                <div>{{ location.city }}</div>
                                <div class="text-[10px] font-semibold uppercase text-[#9ABBC8]">{{ location.country }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span
                                    :class="[
                                        'px-2.5 py-0.5 rounded-md border font-mono text-[10px] font-bold',
                                        location.type === 'PORT'
                                            ? 'bg-[#0C969C]/15 text-[#7DE2E6] border-[#0C969C]/35'
                                            : location.type === 'AIRPORT'
                                              ? 'bg-purple-500/15 text-purple-300 border-purple-400/30'
                                              : 'bg-white/10 text-[#D6E6EA] border-white/15',
                                    ]"
                                >
                                    {{ location.type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[#D6E6EA] text-[11px]">
                                {{ Number(location.latitude).toFixed(4) }}, {{ Number(location.longitude).toFixed(4) }}
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[#D6E6EA]">
                                <span>{{ (location.origin_shipments_count || 0) + (location.destination_shipments_count || 0) }} rute</span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <Link :href="`/locations/${location.id}`" class="p-1.5 rounded-lg border border-white/15 bg-white/5 hover:bg-white/15 text-[#D6E6EA] hover:text-white transition" title="Detail" aria-label="Detail Lokasi">
                                        <i class="bi bi-eye"></i>
                                    </Link>
                                    <Link v-if="isAdmin" :href="`/locations/${location.id}/edit`" class="p-1.5 rounded-lg border border-white/15 bg-white/5 hover:bg-white/15 text-[#D6E6EA] hover:text-white transition" title="Edit" aria-label="Edit Lokasi">
                                        <i class="bi bi-pencil"></i>
                                    </Link>
                                    <button v-if="isAdmin" @click="deleteLocation(location)" class="p-1.5 rounded-lg border border-rose-500/25 bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 transition cursor-pointer" title="Hapus" aria-label="Hapus Lokasi">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!locations.data?.length">
                            <td colspan="7" class="py-8 text-center text-[#9ABBC8]">
                                Tidak ada data lokasi yang sesuai filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="locations.links?.length > 3" class="p-4 border-t border-white/10 flex items-center justify-between text-xs text-[#9ABBC8]">
                <div>Menampilkan <strong class="text-white">{{ locations.from || 0 }}</strong> - <strong class="text-white">{{ locations.to || 0 }}</strong> dari <strong class="text-white">{{ locations.total }}</strong> lokasi</div>
                <div class="flex gap-1">
                    <Link 
                        v-for="(link, i) in locations.links" 
                        :key="i" 
                        :href="link.url || '#'" 
                        :class="['px-3 py-1 rounded-lg border font-mono text-xs transition', link.active ? 'bg-[#0C969C] text-[#031716] font-bold border-[#0C969C] shadow-sm' : 'border-white/15 bg-white/5 hover:bg-white/15 text-[#D6E6EA]', !link.url ? 'opacity-30 pointer-events-none' : '']"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>