<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

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

const carrierToDelete = ref(null);
const deleting = ref(false);

const deleteCarrier = (carrier) => {
    carrierToDelete.value = carrier;
};

const executeDeleteCarrier = () => {
    if (!carrierToDelete.value) return;
    deleting.value = true;
    router.delete(`/carriers/${carrierToDelete.value.id}`, {
        onFinish: () => {
            deleting.value = false;
            carrierToDelete.value = null;
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Master Data Carriers" />

        <!-- Header -->
        <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 mb-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-[#13293D]">Daftar Carrier &amp; Armada Logistik</h1>
                <p class="text-xs font-normal text-[#265D7A] mt-0.5">Penyedia jasa transportasi kargo laut, udara, darat, dan multimodal.</p>
            </div>
            <Link v-if="isAdmin" href="/carriers/create" class="px-5 py-2 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-full transition inline-flex items-center gap-2 shadow-md shadow-[#13293D]/15">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Carrier</span>
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
                    placeholder="Cari kode atau nama carrier..."
                    class="w-full h-10 pl-9 pr-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]"
                />
            </div>
            <select v-model="mode" @change="filter" class="h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A] sm:w-48">
                <option value="">Semua Moda</option>
                <option value="SEA">SEA (Laut)</option>
                <option value="AIR">AIR (Udara)</option>
                <option value="LAND">LAND (Darat)</option>
                <option value="MULTIMODAL">MULTIMODAL</option>
            </select>
            <button @click="filter" class="h-10 px-5 bg-[#265D7A] hover:bg-[#13293D] rounded-full text-xs font-semibold text-white transition cursor-pointer shadow-sm">
                Filter
            </button>
        </div>

        <!-- Carriers Table -->
        <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl overflow-hidden shadow-[0_16px_40px_rgba(19,41,61,0.08)]">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#EAF6F4] border-b border-[#265D7A]/15 text-[#265D7A] font-mono font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3.5 px-4">Kode</th>
                            <th class="py-3.5 px-4">Nama Carrier</th>
                            <th class="py-3.5 px-4">Moda</th>
                            <th class="py-3.5 px-4">Kontak</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Shipments</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#265D7A]/10">
                        <tr v-for="carrier in carriers.data" :key="carrier.id" class="hover:bg-white/60 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-[#265D7A]">{{ carrier.code }}</td>
                            <td class="py-3.5 px-4 font-bold text-[#13293D]">{{ carrier.name }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-md bg-[#13293D] text-[#A7E4DB] font-mono text-[10px] font-bold">
                                    {{ carrier.transport_mode }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-[#13293D]">
                                <div>{{ carrier.contact_email || '-' }}</div>
                                <div class="text-[10px] text-[#265D7A] font-mono">{{ carrier.contact_phone || '' }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span v-if="carrier.is_active" class="px-2.5 py-0.5 rounded-full bg-emerald-500/15 text-emerald-900 border border-emerald-600/30 font-bold text-[10px]">
                                    Aktif
                                </span>
                                <span v-else class="px-2.5 py-0.5 rounded-full bg-[#EAF6F4] text-[#265D7A] border border-[#265D7A]/20 font-bold text-[10px]">
                                    Nonaktif
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-[#13293D]">{{ carrier.shipments_count }} items</td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <Link :href="`/carriers/${carrier.id}`" class="p-1.5 rounded-lg border border-[#265D7A]/20 bg-white/80 hover:bg-[#13293D] hover:text-[#A7E4DB] text-[#13293D] transition" title="Detail" aria-label="Detail Carrier">
                                        <i class="bi bi-eye"></i>
                                    </Link>
                                    <Link v-if="isAdmin" :href="`/carriers/${carrier.id}/edit`" class="p-1.5 rounded-lg border border-[#265D7A]/20 bg-white/80 hover:bg-[#13293D] hover:text-[#A7E4DB] text-[#13293D] transition" title="Edit" aria-label="Edit Carrier">
                                        <i class="bi bi-pencil"></i>
                                    </Link>
                                    <button v-if="isAdmin" @click="deleteCarrier(carrier)" class="p-1.5 rounded-lg border border-rose-600/25 bg-rose-500/15 hover:bg-rose-500/25 text-rose-800 transition cursor-pointer" title="Hapus" aria-label="Hapus Carrier">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!carriers.data?.length">
                            <td colspan="7" class="p-0">
                                <EmptyState
                                    icon="bi-truck"
                                    title="Tidak Ada Carrier Ditemukan"
                                    description="Tidak ada data carrier yang cocok dengan pencarian atau filter moda yang dipilih."
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="carriers.links?.length > 3" class="p-4 border-t border-[#265D7A]/15 flex items-center justify-between text-xs text-[#265D7A]">
                <div>Menampilkan <strong class="text-[#13293D]">{{ carriers.from || 0 }}</strong> - <strong class="text-[#13293D]">{{ carriers.to || 0 }}</strong> dari <strong class="text-[#13293D]">{{ carriers.total }}</strong> carrier</div>
                <div class="flex gap-1">
                    <Link
                        v-for="(link, i) in carriers.links"
                        :key="i"
                        :href="link.url || '#'"
                        :class="['px-3 py-1 rounded-lg border font-mono text-xs transition', link.active ? 'bg-[#13293D] text-[#A7E4DB] font-bold border-[#13293D] shadow-sm' : 'border-[#265D7A]/20 bg-white/80 hover:bg-white text-[#13293D]', !link.url ? 'opacity-30 pointer-events-none' : '']"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>

        <!-- Confirm Delete Carrier Modal -->
        <ConfirmDialog
            :show="!!carrierToDelete"
            title="Hapus Data Carrier?"
            message="Carrier beserta tarif dan relasi operasional terkait akan dihapus. Pastikan tidak ada kargo aktif yang tertaut."
            :item-name="carrierToDelete ? `${carrierToDelete.code} - ${carrierToDelete.name}` : ''"
            confirm-text="Ya, Hapus Carrier"
            :loading="deleting"
            @close="carrierToDelete = null"
            @confirm="executeDeleteCarrier"
        />
    </AuthenticatedLayout>
</template>
