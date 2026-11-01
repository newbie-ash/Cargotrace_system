<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';

const page = usePage();
const notifications = computed(() => page.props.notifications || { unread_count: 0, recent: [] });

const isOpen = ref(false);
const dropdownRef = ref(null);

const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
};

const closeDropdown = (e) => {
    if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
        isOpen.value = false;
    }
};

const markAllRead = () => {
    router.post('/notifications/read-all', {}, {
        preserveScroll: true,
        onSuccess: () => {
            isOpen.value = false;
        },
    });
};

const openNotification = (notif) => {
    // Mark single notification read
    if (!notif.read) {
        router.post(`/notifications/${notif.id}/read`, {}, { preserveScroll: true });
    }
    isOpen.value = false;
    if (notif.data?.shipment_id) {
        router.visit(`/shipments/${notif.data.shipment_id}`);
    }
};

onMounted(() => {
    window.addEventListener('click', closeDropdown);
});

onUnmounted(() => {
    window.removeEventListener('click', closeDropdown);
});
</script>

<template>
    <div class="relative" ref="dropdownRef">
        <!-- Bell Trigger Button -->
        <button
            @click.stop="toggleDropdown"
            type="button"
            class="relative p-2 rounded-xl border border-[#E5E0D5] bg-white/80 hover:bg-[#F2EFE9] text-[#1A1A1C] transition shadow-2xs"
            title="Notifikasi Pengiriman"
        >
            <i class="bi bi-bell text-sm"></i>
            <span
                v-if="notifications.unread_count > 0"
                class="absolute -top-1 -right-1 px-1.5 py-0.2 rounded-full bg-rose-600 text-white font-mono text-[9px] font-bold shadow-xs animate-pulse"
            >
                {{ notifications.unread_count > 9 ? '9+' : notifications.unread_count }}
            </span>
        </button>

        <!-- Dropdown Card -->
        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl border border-[#E5E0D5] shadow-xl z-50 overflow-hidden font-sans"
        >
            <!-- Dropdown Header -->
            <div class="p-3.5 bg-[#F6F3EC] border-b border-[#E5E0D5] flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-xs text-[#1A1A1C] uppercase tracking-wider">Notifikasi Kargo</span>
                    <span v-if="notifications.unread_count > 0" class="px-1.5 py-0.2 rounded bg-rose-100 text-rose-800 text-[10px] font-bold font-mono">
                        {{ notifications.unread_count }} baru
                    </span>
                </div>
                <button
                    v-if="notifications.unread_count > 0"
                    @click="markAllRead"
                    type="button"
                    class="text-[11px] font-semibold text-blue-800 hover:underline"
                >
                    Tandai Semua Dibaca
                </button>
            </div>

            <!-- Notification Items List -->
            <div class="max-h-80 overflow-y-auto divide-y divide-[#E5E0D5] text-xs">
                <div
                    v-for="item in notifications.recent"
                    :key="item.id"
                    @click="openNotification(item)"
                    :class="['p-3 hover:bg-[#F6F3EC]/70 cursor-pointer transition flex items-start gap-3', !item.read ? 'bg-amber-50/40' : 'bg-white']"
                >
                    <div class="mt-0.5">
                        <span v-if="!item.read" class="w-2 h-2 rounded-full bg-blue-600 block"></span>
                        <span v-else class="w-2 h-2 rounded-full bg-gray-300 block"></span>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-[#1A1A1C] text-xs truncate">
                            {{ item.data?.title || 'Pemberitahuan Pengiriman' }}
                        </div>
                        <p class="text-[11px] text-[#706E6B] line-clamp-2 mt-0.5">
                            {{ item.data?.message || '-' }}
                        </p>
                        <div class="mt-1 flex items-center gap-2 text-[10px] text-[#8E8B85]">
                            <span class="font-mono">{{ item.created_at }}</span>
                            <span v-if="item.data?.shipment_number" class="font-mono font-semibold text-[#1A1A1C]">
                                &bull; {{ item.data.shipment_number }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="!notifications.recent?.length" class="p-6 text-center text-[#706E6B] text-xs">
                    <i class="bi bi-bell-slash text-2xl text-[#8E8B85] mb-1.5 block"></i>
                    Belum ada notifikasi.
                </div>
            </div>
        </div>
    </div>
</template>
