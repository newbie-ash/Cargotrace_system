<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const form = useForm({
    code: '',
    name: '',
    city: '',
    country: '',
    type: 'PORT',
    latitude: '',
    longitude: '',
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
    form.post('/locations');
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Tambah Lokasi Baru" />

        <div class="max-w-2xl mx-auto">
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 mb-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)] flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-[#13293D]">Tambah Lokasi / Hub Baru</h1>
                    <p class="text-xs font-normal text-[#265D7A] mt-0.5">Daftarkan pelabuhan, bandara, atau hub transit logistik dengan koordinat geospasial.</p>
                </div>
                <Link href="/locations" class="px-3.5 py-1.5 border border-[#265D7A]/20 bg-white/80 hover:bg-white text-xs font-semibold rounded-full text-[#13293D] transition inline-flex items-center gap-1.5">
                    <i class="bi bi-arrow-left"></i>
                    <span>Kembali</span>
                </Link>
            </div>

            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 shadow-[0_16px_40px_rgba(19,41,61,0.08)]">
                <form @submit.prevent="submit" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">UN/LOCODE <span class="text-rose-600">*</span></label>
                            <input v-model="form.code" type="text" placeholder="Contoh: IDTPP" required class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs font-mono uppercase text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                            <div v-if="form.errors.code" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.code }}</div>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Nama Fasilitas / Pelabuhan <span class="text-rose-600">*</span></label>
                            <div class="flex gap-2">
                                <input v-model="form.name" type="text" placeholder="Port of Tanjung Priok" required class="flex-1 h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                                <button type="button" @click="lookupCoordinates" :disabled="isSearching" class="px-3.5 h-10 bg-[#EAF6F4] hover:bg-[#13293D] border border-[#265D7A]/20 rounded-xl text-xs font-bold text-[#13293D] hover:text-[#A7E4DB] transition inline-flex items-center gap-1.5 shrink-0 cursor-pointer disabled:opacity-60" title="Cari koordinat via OpenStreetMap Nominatim">
                                    <span v-if="isSearching" class="inline-block w-3 h-3 border-2 border-[#265D7A]/25 border-t-[#265D7A] rounded-full animate-spin"></span>
                                    <i v-else class="bi bi-geo-alt"></i>
                                    <span>Cari GPS</span>
                                </button>
                            </div>
                            <div v-if="searchFeedback" class="text-[11px] text-emerald-800 font-semibold mt-1">{{ searchFeedback }}</div>
                            <div v-if="form.errors.name" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.name }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Kota (City) <span class="text-rose-600">*</span></label>
                            <input v-model="form.city" type="text" placeholder="Jakarta" required class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                            <div v-if="form.errors.city" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.city }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Negara (Country) <span class="text-rose-600">*</span></label>
                            <input v-model="form.country" type="text" placeholder="Indonesia" required class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                            <div v-if="form.errors.country" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.country }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Tipe Fasilitas <span class="text-rose-600">*</span></label>
                            <select v-model="form.type" required class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs text-[#13293D] focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]">
                                <option value="PORT">PORT (Pelabuhan Laut)</option>
                                <option value="AIRPORT">AIRPORT (Bandara Kargo)</option>
                                <option value="WAREHOUSE">WAREHOUSE (Gudang)</option>
                                <option value="HUB">HUB (Distribusi)</option>
                            </select>
                            <div v-if="form.errors.type" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.type }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Latitude (-90 s/d 90) <span class="text-rose-600">*</span></label>
                            <input v-model="form.latitude" type="number" step="0.0000001" placeholder="-6.1022" required class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs font-mono text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                            <div v-if="form.errors.latitude" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.latitude }}</div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#13293D] mb-1">Longitude (-180 s/d 180) <span class="text-rose-600">*</span></label>
                            <input v-model="form.longitude" type="number" step="0.0000001" placeholder="106.8833" required class="w-full h-10 px-3 bg-white/90 border border-[#265D7A]/20 rounded-xl text-xs font-mono text-[#13293D] placeholder-[#265D7A]/60 focus:outline-none focus:ring-2 focus:ring-[#67C7C6]/45 focus:border-[#265D7A]" />
                            <div v-if="form.errors.longitude" class="text-xs text-rose-700 font-semibold mt-1">{{ form.errors.longitude }}</div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-[#265D7A]/15 flex justify-end gap-2">
                        <Link href="/locations" class="px-4 py-2 border border-[#265D7A]/20 bg-white/80 hover:bg-white text-xs font-semibold rounded-full text-[#13293D] transition">
                            Batal
                        </Link>
                        <button type="submit" :disabled="form.processing" class="px-5 py-2 bg-[#265D7A] hover:bg-[#13293D] text-white text-xs font-semibold rounded-full transition shadow-md shadow-[#13293D]/15 disabled:opacity-60 cursor-pointer">
                            Simpan Lokasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>