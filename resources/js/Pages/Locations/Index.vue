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
        <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 mb-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-[#13293D]">Daftar Hub &amp; Pelabuhan Global</h1>
                <p class="text-xs font-normal text-[#265D7A] mt-0.5">Master data simpul rute logistik maritim, bandara, dan gudang distribusi.</p>
            </div>
            <Link v-if="isAdmin" href="/locations/create" class="px-5 py-2 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-full transition inline-flex items-center gap-2 shadow-md shadow-[#13293D]/15">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Lokasi</span>
            </Link>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-4 mb-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)] flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-3.5 top-3 text-[#265D7A]"></i>
                <input
                    v-model="search"
                    @keyup.enter="filter"
                    type="text"
                    placeholder="Cari kode, nama, kota, atau negara..."
                    class="w-full h-10 pl-9 pr-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]"
                />
            </div>
            <select v-model="type" @change="filter" class="h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A] sm:w-48">
                <option value="">Semua Fasilitas</option>
                <option value="PORT">PORT (Pelabuhan Laut)</option>
                <option value="AIRPORT">AIRPORT (Bandara Kargo)</option>
                <option value="WAREHOUSE">WAREHOUSE (Gudang)</option>
                <option value="HUB">HUB (Distribusi)</option>
            </select>
            <button @click="filter" class="h-10 px-5 bg-[#265D7A] hover:bg-[#13293D] rounded-full text-xs font-semibold text-white transition cursor-pointer shadow-sm">
                Filter
            </button>
        </div>

        <!-- Locations Table -->
        <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl overflow-hidden shadow-[0_16px_40px_rgba(19,41,61,0.08)]">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#EAF6F4] border-b border-[#265D7A]/15 text-[#265D7A] font-mono font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3.5 px-4">UN/LOCODE</th>
                            <th class="py-3.5 px-4">Fasilitas / Hub</th>
                            <th class="py-3.5 px-4">Kota &amp; Negara</th>
                            <th class="py-3.5 px-4">Tipe</th>
                            <th class="py-3.5 px-4">Koordinat GPS</th>
                            <th class="py-3.5 px-4">Shipments</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#265D7A]/10">
                        <tr v-for="location in locations.data" :key="location.id" class="hover:bg-white/60 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-[#265D7A]">{{ location.code }}</td>
                            <td class="py-3.5 px-4 font-bold text-[#13293D]">{{ location.name }}</td>
                            <td class="py-3.5 px-4 text-[#13293D]">
                                <div>{{ location.city }}</div>
                                <div class="text-[10px] font-semibold uppercase text-[#265D7A]">{{ location.country }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span
                                    :class="[
                                        'px-2.5 py-0.5 rounded-md border font-mono text-[10px] font-bold',
                                        location.type === 'PORT'
                                            ? 'bg-[#13293D] text-[#A7E4DB] border-[#13293D]'
                                            : location.type === 'AIRPORT'
                                              ? 'bg-[#67C7C6]/25 text-[#13293D] border-[#265D7A]/25'
                                              : 'bg-[#EAF6F4] text-[#265D7A] border-[#265D7A]/20',
                                    ]"
                                >
                                    {{ location.type }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[#13293D] text-[11px]">
                                {{ Number(location.latitude).toFixed(4) }}, {{ Number(location.longitude).toFixed(4) }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-[#13293D]">
                                <span>{{ (location.origin_shipments_count || 0) + (location.destination_shipments_count || 0) }} rute</span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <Link :href="`/locations/${location.id}`" class="p-1.5 rounded-lg border border-[#265D7A]/20 bg-white/80 hover:bg-[#13293D] hover:text-[#A7E4DB] text-[#13293D] transition" title="Detail" aria-label="Detail Lokasi">
                                        <i class="bi bi-eye"></i>
                                    </Link>
                                    <Link v-if="isAdmin" :href="`/locations/${location.id}/edit`" class="p-1.5 rounded-lg border border-[#265D7A]/20 bg-white/80 hover:bg-[#13293D] hover:text-[#A7E4DB] text-[#13293D] transition" title="Edit" aria-label="Edit Lokasi">
                                        <i class="bi bi-pencil"></i>
                                    </Link>
                                    <button v-if="isAdmin" @click="deleteLocation(location)" class="p-1.5 rounded-lg border border-rose-600/25 bg-rose-500/15 hover:bg-rose-500/25 text-rose-800 transition cursor-pointer" title="Hapus" aria-label="Hapus Lokasi">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!locations.data?.length">
                            <td colspan="7" class="py-8 text-center text-[#265D7A]">
                                Tidak ada data lokasi yang sesuai filter.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="locations.links?.length > 3" class="p-4 border-t border-[#265D7A]/15 flex items-center justify-between text-xs text-[#265D7A]">
                <div>Menampilkan <strong class="text-[#13293D]">{{ locations.from || 0 }}</strong> - <strong class="text-[#13293D]">{{ locations.to || 0 }}</strong> dari <strong class="text-[#13293D]">{{ locations.total }}</strong> lokasi</div>
                <div class="flex gap-1">
                    <Link
                        v-for="(link, i) in locations.links"
                        :key="i"
                        :href="link.url || '#'"
                        :class="['px-3 py-1 rounded-lg border font-mono text-xs transition', link.active ? 'bg-[#13293D] text-[#A7E4DB] font-bold border-[#13293D] shadow-sm' : 'border-[#265D7A]/20 bg-white/80 hover:bg-white text-[#13293D]', !link.url ? 'opacity-30 pointer-events-none' : '']"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>