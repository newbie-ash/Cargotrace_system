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
            class="relative p-2 rounded-xl border border-[#265D7A]/20 bg-white/80 hover:bg-[#EAF6F4] text-[#265D7A] hover:text-[#13293D] transition cursor-pointer"
            title="Notifikasi Pengiriman"
            aria-label="Notifikasi Pengiriman"
        >
            <i class="bi bi-bell text-sm"></i>
            <span
                v-if="notifications.unread_count > 0"
                class="absolute -top-1 -right-1 px-1.5 py-0.2 rounded-full bg-[#13293D] text-[#A7E4DB] font-mono text-[9px] font-extrabold shadow-sm"
            >
                {{ notifications.unread_count > 9 ? '9+' : notifications.unread_count }}
            </span>
        </button>

        <!-- Dropdown Card -->
        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 w-80 sm:w-96 bg-white/95 backdrop-blur-2xl rounded-2xl border border-white shadow-[0_16px_48px_0_rgba(19,41,61,0.18)] z-50 overflow-hidden font-sans"
        >
            <!-- Dropdown Header -->
            <div class="p-3.5 bg-[#EAF6F4] border-b border-[#265D7A]/15 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-xs text-[#13293D] uppercase tracking-wider">Notifikasi Kargo</span>
                    <span v-if="notifications.unread_count > 0" class="px-2 py-0.5 rounded-full bg-[#13293D] text-[#A7E4DB] text-[10px] font-bold font-mono">
                        {{ notifications.unread_count }} baru
                    </span>
                </div>
                <button
                    v-if="notifications.unread_count > 0"
                    @click="markAllRead"
                    type="button"
                    class="text-[11px] font-bold text-[#265D7A] hover:text-[#13293D] cursor-pointer"
                >
                    Tandai Semua Dibaca
                </button>
            </div>

            <!-- Notification Items List -->
            <div class="max-h-80 overflow-y-auto divide-y divide-[#265D7A]/10 text-xs">
                <div
                    v-for="item in notifications.recent"
                    :key="item.id"
                    @click="openNotification(item)"
                    :class="['p-3.5 hover:bg-[#EAF6F4]/70 cursor-pointer transition flex items-start gap-3', !item.read ? 'bg-[#A7E4DB]/25' : 'bg-transparent']"
                >
                    <div class="mt-1">
                        <span v-if="!item.read" class="w-2 h-2 rounded-full bg-[#265D7A] block"></span>
                        <span v-else class="w-2 h-2 rounded-full bg-[#67C7C6]/50 block"></span>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-[#13293D] text-xs truncate">
                            {{ item.data?.title || 'Pemberitahuan Pengiriman' }}
                        </div>
                        <p class="text-[11px] font-normal text-[#265D7A] line-clamp-2 mt-0.5 leading-relaxed">
                            {{ item.data?.message || '-' }}
                        </p>
                        <div class="mt-1.5 flex items-center gap-2 text-[10px] text-[#265D7A] font-mono">
                            <span>{{ item.created_at }}</span>
                            <span v-if="item.data?.shipment_number" class="text-[#13293D] font-bold">
                                &bull; {{ item.data.shipment_number }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-if="!notifications.recent?.length" class="p-8 text-center text-[#265D7A] text-xs">
                    <i class="bi bi-bell-slash text-2xl text-[#67C7C6] mb-2 block"></i>
                    Belum ada notifikasi terkini.
                </div>
            </div>
        </div>
    </div>
</template>
