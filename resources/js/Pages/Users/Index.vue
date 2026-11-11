<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    users: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search || '');
const role = ref(props.filters.role || '');

const filter = () => {
    router.get('/users', {
        search: search.value || undefined,
        role: role.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const deleteUser = (user) => {
    if (confirm(`Apakah Anda yakin ingin menghapus akun ${user.name}?`)) {
        router.delete(`/users/${user.id}`);
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Users & RBAC Management" />

        <!-- Header -->
        <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 mb-6 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-white">Manajemen Pengguna & Akses RBAC</h1>
                <p class="text-xs font-light text-[#9ABBC8] mt-0.5">Kelola akun administrator, operator logistik, dan customer terdaftar.</p>
            </div>
            <Link href="/users/create" class="px-5 py-2 bg-[#274D60] hover:bg-[#0A7075] text-white text-xs font-semibold rounded-full transition inline-flex items-center gap-2 shadow-lg shadow-black/25">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Pengguna</span>
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
                    placeholder="Cari nama, email, atau perusahaan..." 
                    class="w-full h-10 pl-9 pr-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs text-white placeholder-[#6B8B97] focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]"
                />
            </div>
            <select v-model="role" @change="filter" class="h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C] sm:w-48">
                <option value="">Semua Peran (Role)</option>
                <option value="admin">Administrator</option>
                <option value="operator">Logistics Operator</option>
                <option value="customer">Customer</option>
            </select>
            <button @click="filter" class="h-10 px-5 bg-[#274D60] hover:bg-[#0A7075] border border-white/10 rounded-full text-xs font-semibold text-white transition cursor-pointer">
                Filter
            </button>
        </div>

        <!-- Users Table -->
        <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#031716]/75 border-b border-white/10 text-[#9ABBC8] font-mono font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3.5 px-4">Nama Lengkap</th>
                            <th class="py-3.5 px-4">Email</th>
                            <th class="py-3.5 px-4">Peran (Role)</th>
                            <th class="py-3.5 px-4">Perusahaan / Telepon</th>
                            <th class="py-3.5 px-4">Shipments</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-white/[0.04] transition">
                            <td class="py-3.5 px-4 font-semibold text-white">{{ user.name }}</td>
                            <td class="py-3.5 px-4 font-mono text-[#D6E6EA]">{{ user.email }}</td>
                            <td class="py-3.5 px-4">
                                <span v-if="user.role === 'admin'" class="px-2.5 py-0.5 rounded-md bg-purple-500/15 text-purple-300 border border-purple-400/30 font-bold text-[10px] uppercase">
                                    Admin
                                </span>
                                <span v-else-if="user.role === 'operator'" class="px-2.5 py-0.5 rounded-md bg-[#0C969C]/15 text-[#7DE2E6] border border-[#0C969C]/35 font-bold text-[10px] uppercase">
                                    Operator
                                </span>
                                <span v-else class="px-2.5 py-0.5 rounded-md bg-emerald-500/15 text-emerald-300 border border-emerald-400/30 font-bold text-[10px] uppercase">
                                    Customer
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-[#D6E6EA]">
                                <div>{{ user.company_name || '-' }}</div>
                                <div class="text-[10px] text-[#9ABBC8] font-mono">{{ user.phone || '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[#D6E6EA]">{{ user.shipments_count }} items</td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <Link :href="`/users/${user.id}`" class="p-1.5 rounded-lg border border-white/15 bg-white/5 hover:bg-white/15 text-[#D6E6EA] hover:text-white transition" title="Detail" aria-label="Detail Pengguna">
                                        <i class="bi bi-eye"></i>
                                    </Link>
                                    <Link :href="`/users/${user.id}/edit`" class="p-1.5 rounded-lg border border-white/15 bg-white/5 hover:bg-white/15 text-[#D6E6EA] hover:text-white transition" title="Edit" aria-label="Edit Pengguna">
                                        <i class="bi bi-pencil"></i>
                                    </Link>
                                    <button @click="deleteUser(user)" class="p-1.5 rounded-lg border border-rose-500/25 bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 transition cursor-pointer" title="Hapus" aria-label="Hapus Pengguna">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!users.data?.length">
                            <td colspan="6" class="py-8 text-center text-[#9ABBC8]">
                                Tidak ada data pengguna yang cocok.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="users.links?.length > 3" class="p-4 border-t border-white/10 flex items-center justify-between text-xs text-[#9ABBC8]">
                <div>Menampilkan <strong class="text-white">{{ users.from || 0 }}</strong> - <strong class="text-white">{{ users.to || 0 }}</strong> dari <strong class="text-white">{{ users.total }}</strong> pengguna</div>
                <div class="flex gap-1">
                    <Link 
                        v-for="(link, i) in users.links" 
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