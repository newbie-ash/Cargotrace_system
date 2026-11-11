<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    location: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    code: props.location.code,
    name: props.location.name,
    city: props.location.city,
    country: props.location.country,
    type: props.location.type,
    latitude: props.location.latitude,
    longitude: props.location.longitude,
});

const isSearching = ref(false);
const searchFeedback = ref('');

const lookupCoordinates = async () => {
    if (!form.name.trim()) {
        alert('Silakan ketik nama fasilitas atau pelabuhan terlebih dahulu.');
        return;
    }

    isSearching.value = true;
    searchFeedback.value = '';

    try {
        const query = `${form.name} ${form.city} ${form.country}`.trim();
        const res = await fetch(`/locations/lookup-coordinates?query=${encodeURIComponent(query)}`);
        const json = await res.json();

        if (json.status === 'success' && json.data) {
            form.latitude = json.data.latitude;
            form.longitude = json.data.longitude;
            searchFeedback.value = `Ditemukan: ${json.data.display_name}`;
        } else {
            alert(json.message || 'Koordinat tidak ditemukan.');
        }
    } catch (err) {
        alert('Gagal menghubungi layanan geocoding Nominatim.');
    } finally {
        isSearching.value = false;
    }
};

const submit = () => {
    form.put(`/locations/${props.location.id}`);
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Edit Lokasi ${location.name}`" />

        <div class="max-w-2xl mx-auto">
            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 mb-6 shadow-xl flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-white">Edit Lokasi: {{ location.name }}</h1>
                    <p class="text-xs font-light text-[#9ABBC8] mt-0.5">Perbarui koordinat geografis atau metadata hub kargo.</p>
                </div>
                <Link href="/locations" class="px-3.5 py-1.5 border border-white/15 bg-white/5 hover:bg-white/10 text-xs font-semibold rounded-full text-[#D6E6EA] hover:text-white transition inline-flex items-center gap-1.5">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </Link>
            </div>

            <div class="bg-[#0A7075]/15 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#D6E6EA] mb-1">UN/LOCODE <span class="text-rose-400">*</span></label>
                            <input v-model="form.code" type="text" required class="w-full h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs font-mono uppercase text-white placeholder-[#6B8B97] focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]" />
                            <div v-if="form.errors.code" class="text-xs text-rose-400 mt-1">{{ form.errors.code }}</div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-[#D6E6EA] mb-1">Nama Fasilitas / Pelabuhan <span class="text-rose-400">*</span></label>
                            <div class="flex gap-2">
                                <input v-model="form.name" type="text" required class="flex-1 h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs text-white placeholder-[#6B8B97] focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]" />
                                <button type="button" @click="lookupCoordinates" :disabled="isSearching" class="px-3.5 h-10 bg-white/10 hover:bg-white/15 border border-white/15 rounded-xl text-xs font-bold text-white transition inline-flex items-center gap-1.5 shrink-0 cursor-pointer disabled:opacity-60" title="Cari koordinat via OpenStreetMap Nominatim">
                                    <span v-if="isSearching" class="inline-block w-3 h-3 border-2 border-white/20 border-t-[#0C969C] rounded-full animate-spin"></span>
                                    <i v-else class="bi bi-geo-alt text-[#7DE2E6]"></i>
                                    <span>Cari GPS</span>
                                </button>
                            </div>
                            <div v-if="searchFeedback" class="text-[11px] text-emerald-400 font-medium mt-1">{{ searchFeedback }}</div>
                            <div v-if="form.errors.name" class="text-xs text-rose-400 mt-1">{{ form.errors.name }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#D6E6EA] mb-1">Kota (City) <span class="text-rose-400">*</span></label>
                            <input v-model="form.city" type="text" required class="w-full h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs text-white placeholder-[#6B8B97] focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]" />
                            <div v-if="form.errors.city" class="text-xs text-rose-400 mt-1">{{ form.errors.city }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#D6E6EA] mb-1">Negara (Country) <span class="text-rose-400">*</span></label>
                            <input v-model="form.country" type="text" required class="w-full h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs text-white placeholder-[#6B8B97] focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]" />
                            <div v-if="form.errors.country" class="text-xs text-rose-400 mt-1">{{ form.errors.country }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#D6E6EA] mb-1">Tipe Fasilitas <span class="text-rose-400">*</span></label>
                            <select v-model="form.type" required class="w-full h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]">
                                <option value="PORT">PORT (Pelabuhan Laut)</option>
                                <option value="AIRPORT">AIRPORT (Bandara Kargo)</option>
                                <option value="WAREHOUSE">WAREHOUSE (Gudang)</option>
                                <option value="HUB">HUB (Distribusi)</option>
                            </select>
                            <div v-if="form.errors.type" class="text-xs text-rose-400 mt-1">{{ form.errors.type }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#D6E6EA] mb-1">Latitude (-90 s/d 90) <span class="text-rose-400">*</span></label>
                            <input v-model="form.latitude" type="number" step="0.0000001" required class="w-full h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs font-mono text-white placeholder-[#6B8B97] focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]" />
                            <div v-if="form.errors.latitude" class="text-xs text-rose-400 mt-1">{{ form.errors.latitude }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#D6E6EA] mb-1">Longitude (-180 s/d 180) <span class="text-rose-400">*</span></label>
                            <input v-model="form.longitude" type="number" step="0.0000001" required class="w-full h-10 px-3 bg-[#031716]/75 border border-white/15 rounded-xl text-xs font-mono text-white placeholder-[#6B8B97] focus:outline-none focus:ring-2 focus:ring-[#0C969C]/40 focus:border-[#0C969C]" />
                            <div v-if="form.errors.longitude" class="text-xs text-rose-400 mt-1">{{ form.errors.longitude }}</div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-white/10 flex justify-end gap-2">
                        <Link href="/locations" class="px-4 py-2 border border-white/15 bg-white/5 hover:bg-white/10 text-xs font-semibold rounded-full text-[#D6E6EA] transition">
                            Batal
                        </Link>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-[#274D60] hover:bg-[#0A7075] text-white text-xs font-semibold rounded-full transition shadow-lg shadow-black/25 disabled:opacity-60 cursor-pointer">
                            Perbarui Lokasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>