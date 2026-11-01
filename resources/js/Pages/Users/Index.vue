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
        <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 mb-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[#1A1A1C]">Manajemen Pengguna & Akses RBAC</h1>
                <p class="text-xs text-[#706E6B] mt-0.5">Kelola akun administrator, operator logistik, dan customer terdaftar.</p>
            </div>
            <Link href="/users/create" class="px-4 py-2 bg-[#1A1A1C] hover:bg-[#2C2C2E] text-white text-xs font-semibold rounded-xl transition inline-flex items-center gap-2 shadow-xs">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Pengguna</span>
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
                    placeholder="Cari nama, email, atau perusahaan..." 
                    class="w-full h-10 pl-9 pr-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20"
                />
            </div>
            <select v-model="role" @change="filter" class="h-10 px-3 bg-white border border-[#D8D2C4] rounded-xl text-xs text-[#1A1A1C] focus:outline-none focus:ring-2 focus:ring-[#1A1A1C]/20 sm:w-48">
                <option value="">Semua Peran (Role)</option>
                <option value="admin">Administrator</option>
                <option value="operator">Logistics Operator</option>
                <option value="customer">Customer</option>
            </select>
            <button @click="filter" class="h-10 px-4 bg-[#F2EFE9] hover:bg-[#E5E0D5] border border-[#D8D2C4] rounded-xl text-xs font-semibold text-[#1A1A1C] transition">
                Filter
            </button>
        </div>

        <!-- Users Table -->
        <div class="bg-white/80 backdrop-blur-md border border-[#E5E0D5] rounded-2xl overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#F2EFE9] border-b border-[#E5E0D5] text-[#706E6B] font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4">Nama Lengkap</th>
                            <th class="py-3 px-4">Email</th>
                            <th class="py-3 px-4">Peran (Role)</th>
                            <th class="py-3 px-4">Perusahaan / Telepon</th>
                            <th class="py-3 px-4">Shipments</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5E0D5]">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-white/90 transition">
                            <td class="py-3 px-4 font-semibold text-[#1A1A1C]">{{ user.name }}</td>
                            <td class="py-3 px-4 font-mono text-[#706E6B]">{{ user.email }}</td>
                            <td class="py-3 px-4">
                                <span v-if="user.role === 'admin'" class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-200 font-bold text-[10px] uppercase">
                                    Admin
                                </span>
                                <span v-else-if="user.role === 'operator'" class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-200 font-bold text-[10px] uppercase">
                                    Operator
                                </span>
                                <span v-else class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold text-[10px] uppercase">
                                    Customer
                                </span>
                            </td>
                            <td class="py-3 px-4 text-[#706E6B]">
                                <div>{{ user.company_name || '-' }}</div>
                                <div class="text-[10px]">{{ user.phone || '' }}</div>
                            </td>
                            <td class="py-3 px-4 font-mono text-[#706E6B]">{{ user.shipments_count }} items</td>
                            <td class="py-3 px-4 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <Link :href="`/users/${user.id}`" class="p-1.5 rounded-lg border border-[#D8D2C4] hover:bg-[#F2EFE9] text-[#1A1A1C]" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </Link>
                                    <Link :href="`/users/${user.id}/edit`" class="p-1.5 rounded-lg border border-[#D8D2C4] hover:bg-[#F2EFE9] text-[#1A1A1C]" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </Link>
                                    <button @click="deleteUser(user)" class="p-1.5 rounded-lg border border-[#D8D2C4] hover:bg-rose-50 text-rose-600" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!users.data?.length">
                            <td colspan="6" class="py-8 text-center text-[#706E6B]">
                                Tidak ada data pengguna yang cocok.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="users.links?.length > 3" class="p-4 border-t border-[#E5E0D5] flex items-center justify-between text-xs text-[#706E6B]">
                <div>Menampilkan {{ users.from || 0 }} - {{ users.to || 0 }} dari {{ users.total }} pengguna</div>
                <div class="flex gap-1">
                    <Link 
                        v-for="(link, i) in users.links" 
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
