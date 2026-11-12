<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import ConfirmDialog from '@/Components/ConfirmDialog.vue';

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

const userToDelete = ref(null);
const deleting = ref(false);

const deleteUser = (user) => {
    userToDelete.value = user;
};

const executeDeleteUser = () => {
    if (!userToDelete.value) return;
    deleting.value = true;
    router.delete(`/users/${userToDelete.value.id}`, {
        onFinish: () => {
            deleting.value = false;
            userToDelete.value = null;
        },
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Users & RBAC Management" />

        <!-- Header -->
        <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 mb-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-[#13293D]">Manajemen Pengguna &amp; Akses RBAC</h1>
                <p class="text-xs font-normal text-[#265D7A] mt-0.5">Kelola akun administrator, operator logistik, dan customer terdaftar.</p>
            </div>
            <Link href="/users/create" class="px-5 py-2 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-full transition inline-flex items-center gap-2 shadow-md shadow-[#13293D]/15">
                <i class="bi bi-plus-lg"></i>
                <span>Tambah Pengguna</span>
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
                    placeholder="Cari nama, email, atau perusahaan..."
                    class="w-full h-10 pl-9 pr-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]"
                />
            </div>
            <select v-model="role" @change="filter" class="h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A] sm:w-48">
                <option value="">Semua Peran (Role)</option>
                <option value="admin">Administrator</option>
                <option value="operator">Logistics Operator</option>
                <option value="customer">Customer</option>
            </select>
            <button @click="filter" class="h-10 px-5 bg-[#265D7A] hover:bg-[#13293D] rounded-full text-xs font-semibold text-white transition cursor-pointer shadow-sm">
                Filter
            </button>
        </div>

        <!-- Users Table -->
        <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl overflow-hidden shadow-[0_16px_40px_rgba(19,41,61,0.08)]">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-[#EAF6F4] border-b border-[#265D7A]/15 text-[#265D7A] font-mono font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-3.5 px-4">Nama Lengkap</th>
                            <th class="py-3.5 px-4">Email</th>
                            <th class="py-3.5 px-4">Peran (Role)</th>
                            <th class="py-3.5 px-4">Perusahaan / Telepon</th>
                            <th class="py-3.5 px-4">Shipments</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#265D7A]/10">
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-white/60 transition">
                            <td class="py-3.5 px-4 font-bold text-[#13293D]">{{ user.name }}</td>
                            <td class="py-3.5 px-4 font-mono text-[#265D7A]">{{ user.email }}</td>
                            <td class="py-3.5 px-4">
                                <span v-if="user.role === 'admin'" class="px-2.5 py-0.5 rounded-md bg-[#13293D] text-[#A7E4DB] border border-[#13293D] font-bold text-[10px] uppercase">
                                    Admin
                                </span>
                                <span v-else-if="user.role === 'operator'" class="px-2.5 py-0.5 rounded-md bg-[#67C7C6]/25 text-[#13293D] border border-[#265D7A]/25 font-bold text-[10px] uppercase">
                                    Operator
                                </span>
                                <span v-else class="px-2.5 py-0.5 rounded-md bg-emerald-500/15 text-emerald-900 border border-emerald-600/30 font-bold text-[10px] uppercase">
                                    Customer
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-[#13293D]">
                                <div>{{ user.company_name || '-' }}</div>
                                <div class="text-[10px] text-[#265D7A] font-mono">{{ user.phone || '' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono font-semibold text-[#13293D]">{{ user.shipments_count }} items</td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <Link :href="`/users/${user.id}`" class="p-1.5 rounded-lg border border-[#265D7A]/20 bg-white/80 hover:bg-[#13293D] hover:text-[#A7E4DB] text-[#13293D] transition" title="Detail" aria-label="Detail Pengguna">
                                        <i class="bi bi-eye"></i>
                                    </Link>
                                    <Link :href="`/users/${user.id}/edit`" class="p-1.5 rounded-lg border border-[#265D7A]/20 bg-white/80 hover:bg-[#13293D] hover:text-[#A7E4DB] text-[#13293D] transition" title="Edit" aria-label="Edit Pengguna">
                                        <i class="bi bi-pencil"></i>
                                    </Link>
                                    <button @click="deleteUser(user)" class="p-1.5 rounded-lg border border-rose-600/25 bg-rose-500/15 hover:bg-rose-500/25 text-rose-800 transition cursor-pointer" title="Hapus" aria-label="Hapus Pengguna">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!users.data?.length">
                            <td colspan="6" class="p-0">
                                <EmptyState
                                    icon="bi-people"
                                    title="Tidak Ada Pengguna Ditemukan"
                                    description="Tidak ada akun administrator, operator, atau customer yang sesuai pencarian."
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="users.links?.length > 3" class="p-4 border-t border-[#265D7A]/15 flex items-center justify-between text-xs text-[#265D7A]">
                <div>Menampilkan <strong class="text-[#13293D]">{{ users.from || 0 }}</strong> - <strong class="text-[#13293D]">{{ users.to || 0 }}</strong> dari <strong class="text-[#13293D]">{{ users.total }}</strong> pengguna</div>
                <div class="flex gap-1">
                    <Link
                        v-for="(link, i) in users.links"
                        :key="i"
                        :href="link.url || '#'"
                        :class="['px-3 py-1 rounded-lg border font-mono text-xs transition', link.active ? 'bg-[#13293D] text-[#A7E4DB] font-bold border-[#13293D] shadow-sm' : 'border-[#265D7A]/20 bg-white/80 hover:bg-white text-[#13293D]', !link.url ? 'opacity-30 pointer-events-none' : '']"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>

        <!-- Confirm Delete User Modal -->
        <ConfirmDialog
            :show="!!userToDelete"
            title="Hapus Akun Pengguna?"
            message="Akun ini tidak lagi dapat mengakses portal Delixie setelah dihapus."
            :item-name="userToDelete ? `${userToDelete.name} (${userToDelete.email})` : ''"
            confirm-text="Ya, Hapus Akun"
            :loading="deleting"
            @close="userToDelete = null"
            @confirm="executeDeleteUser"
        />
    </AuthenticatedLayout>
</template>