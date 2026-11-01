<script setup>
import { ref, watch, computed } from 'vue';
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
        <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 mb-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[#1A1A1C]">Daftar Carrier & Armada Logistik</h1>
                <p class="text-xs text-[#706E6B] mt-0.5">Penyedia jasa transportasi kargo laut, udara, darat, dan multimodal.</p>
            </div>
            <Link v-if="isAdmin" href="/carriers/create" class="px-4 py-2 bg-[#1A1A1C] hover:bg-[#2C2C2E] text-white text-xs font-semibold rounded-xl transition inline-flex items-center gap-2 shadow-xs">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Carrier</span>
            </Link>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-4 mb-6 shadow-xs flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="bi bi-search absolute left-3.5 top-3 text-[#706E6B]"></i>
                <input 
                    v-model="search" 
                    @keyup.enter="filter"
                    type="text" 
                    placeholder="Cari kode atau nama carrier..." 
                    class="w-full h-10 pl-9 pr-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20"
                />
            </div>
            <select v-model="mode" @change="filter" class="h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20 sm:w-48">
                <option value="">Semua Moda</option>
                <option value="SEA">SEA (Laut)</option>
                <option value="AIR">AIR (Udara)</option>
                <option value="LAND">LAND (Darat)</option>
                <option value="MULTIMODAL">MULTIMODAL</option>
            </select>
            <button @click="filter" class="h-10 px-4 bg-[#F2EFE9] hover:bg-[#E5E0D5] border border-[#D8D2C4] rounded-xl text-xs font-semibold text-[#1A1A1C] transition">
                Filter
            </button>
        </div>

        <!-- Carriers Table -->
        <div class="bg-white/80 backdrop-blur-md border border-[#E5E0D5] rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#F2EFE9] border-b border-[#E5E0D5] text-[#706E6B] font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Kode</th>
                            <th class="py-3 px-4">Nama Carrier</th>
                            <th class="py-3 px-4">Moda</th>
                            <th class="py-3 px-4">Kontak</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Shipments</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E0D5]">
                        <tr v-for="carrier in carriers.data" :key="carrier.id" class="hover:bg-white/90 transition">
                            <td class="py-3 px-4 font-mono font-bold text-[#1A1A1C]">{{ carrier.code }}</td>
                            <td class="py-3 px-4 font-semibold text-[#1A1A1C]">{{ carrier.name }}</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200 font-mono text-[10px]">
                                    {{ carrier.transport_mode }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-[#706E6B]">
                                <div>{{ carrier.contact_email || '-' }}</div>
                                <div class="text-[10px]">{{ carrier.contact_phone || '' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span v-if="carrier.is_active" class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold text-[10px]">
                                    Aktif
                                </span>
                                <span v-else class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 border border-gray-200 font-semibold text-[10px]">
                                    Nonaktif
                                </span>
                            </td>
                            <td class="py-3 px-4 font-mono text-[#706E6B]">{{ carrier.shipments_count }} items</td>
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <Link :href="`/carriers/${carrier.id}`" class="p-1.5 rounded-lg border border-[#D8D2C4] hover:bg-[#F2EFE9] text-[#1A1A1C]" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </Link>
                                    <Link v-if="isAdmin" :href="`/carriers/${carrier.id}/edit`" class="p-1.5 rounded-lg border border-[#D8D2C4] hover:bg-[#F2EFE9] text-[#1A1A1C]" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </Link>
                                    <button v-if="isAdmin" @click="deleteCarrier(carrier)" class="p-1.5 rounded-lg border border-[#D8D2C4] hover:bg-rose-50 text-rose-600" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!carriers.data?.length">
                            <td colspan="7" class="py-8 text-center text-[#706E6B]">
                                Tidak ada data carrier yang cocok.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="carriers.links?.length > 3" class="p-4 border-t border-[#E5E0D5] flex items-center justify-between text-xs text-[#706E6B]">
                <div>Menampilkan {{ carriers.from || 0 }} - {{ carriers.to || 0 }} dari {{ carriers.total }} carrier</div>
                <div class="flex gap-1">
                    <Link 
                        v-for="(link, i) in carriers.links" 
                        :key="i" 
                        :href="link.url || '#'" 
                        :class="['px-3 py-1 rounded-lg border', link.active ? 'bg-[#1A1A1C] text-white border-[#1A1A1C]' : 'border-[#D8D2C4] hover:bg-[#F2EFE9] text-[#1A1A1C]', !link.url ? 'opacity-40 pointer-events-none' : '']"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
