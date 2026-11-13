<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import VehicleGraphic from '@/Components/VehicleGraphic.vue';
import { calculateDistanceKm } from '@/Utils/carbonCalculator';

const props = defineProps({
    locations: {
        type: Array,
        default: () => [],
    },
    tariffs: {
        type: Array,
        default: () => [],
    },
});

const form = ref({
    origin_id: props.locations[0]?.id || '',
    destination_id: props.locations[1]?.id || '',
    transport_mode: 'SEA',
    weight_kg: 2500,
});

const isCalculating = ref(false);
const result = ref(null);
const errorMessage = ref('');
const copiedEstimate = ref(false);

const selectedOrigin = computed(() => {
    return props.locations.find((l) => l.id === Number(form.value.origin_id)) || null;
});

const selectedDestination = computed(() => {
    return props.locations.find((l) => l.id === Number(form.value.destination_id)) || null;
});

const activeTariff = computed(() => {
    return props.tariffs.find((t) => t.transport_mode === form.value.transport_mode) || null;
});

// Emission factor in kg CO2 per tonne-kilometer
const emissionFactor = computed(() => {
    switch (form.value.transport_mode) {
        case 'AIR': return 0.500;
        case 'LAND': return 0.095;
        case 'SEA':
        default: return 0.015;
    }
});

const calculate = async () => {
    if (!form.value.origin_id || !form.value.destination_id) {
        errorMessage.value = 'Silakan pilih pelabuhan asal dan pelabuhan tujuan.';
        return;
    }

    if (form.value.origin_id === form.value.destination_id) {
        errorMessage.value = 'Pelabuhan asal dan destinasi tidak boleh sama.';
        return;
    }

    errorMessage.value = '';
    isCalculating.value = true;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const response = await fetch('/calculator/estimate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken || '',
            },
            body: JSON.stringify(form.value),
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Also compute carbon footprint
            const dist = data.data.distance_km || 1500;
            const weightTonnes = Math.max(0.1, Number(form.value.weight_kg) / 1000);
            const carbonKg = Math.round(dist * weightTonnes * emissionFactor.value * 10) / 10;

            result.value = {
                ...data.data,
                carbon_kg: carbonKg,
                carbon_rating: form.value.transport_mode === 'AIR' ? 'High-Speed Express' : (carbonKg < 300 ? 'GLEC Eco-Efficient' : 'Standard Maritime'),
            };
        } else {
            errorMessage.value = data.message || 'Gagal menghitung tarif. Coba lagi.';
        }
    } catch (e) {
        errorMessage.value = 'Terjadi kesalahan koneksi server saat menghitung tarif.';
    } finally {
        isCalculating.value = false;
    }
};

const copyEstimate = () => {
    if (!result.value) return;
    const summary = `DELIXIE CARGOTRACE ESTIMASI:\nModa: ${result.value.transport_mode} Freight\nRute: ${result.value.origin.code} (${result.value.origin.city}) -> ${result.value.destination.code} (${result.value.destination.city})\nJarak: ${result.value.distance_km} km\nBerat: ${form.value.weight_kg} kg\nEstimasi Transit: ${result.value.estimated_transit_days} Hari\nTotal Biaya: $${result.value.total_cost_usd} (Rp ${result.value.total_cost_idr?.toLocaleString()})\nJejak Emisi: ${result.value.carbon_kg} kg CO2e`;
    if (navigator.clipboard) {
        navigator.clipboard.writeText(summary).then(() => {
            copiedEstimate.value = true;
            setTimeout(() => {
                copiedEstimate.value = false;
            }, 2500);
        });
    }
};

// Initial calculation on mount
calculate();
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Kalkulator Tarif & Emisi Karbon Kargo" />

        <!-- Top Header Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 mb-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight text-[#13293D]">
                        Kalkulator Tarif &amp; Jejak Karbon
                    </h1>
                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 text-xs font-mono font-bold border border-emerald-200">
                        GLEC &bull; Live Estimator
                    </span>
                </div>
                <p class="text-xs text-[#265D7A]">
                    Simulasikan biaya pengapalan internasional, estimasi durasi berlayar, dan kalkulasi jejak karbon $CO_2$ secara instan sebelum melakukan pengiriman.
                </p>
            </div>

            <Link
                href="/shipments"
                class="px-3.5 py-1.5 border border-slate-200 hover:border-slate-300 bg-slate-50 hover:bg-white text-xs font-semibold rounded-xl text-[#13293D] transition inline-flex items-center gap-1.5 shadow-2xs self-start sm:self-auto"
            >
                <i class="bi bi-box-seam"></i>
                <span>Lihat Kargo Aktif</span>
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start">
            <!-- Form Card (Left Column) -->
            <div class="lg:col-span-5 bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-[#13293D] flex items-center gap-2">
                        <i class="bi bi-sliders text-[#265D7A]"></i>
                        <span>Parameter Pengiriman</span>
                    </h2>
                    <span class="text-[10px] font-mono text-slate-400">Database Tariffs V2</span>
                </div>

                <!-- Error Alert -->
                <div v-if="errorMessage" class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>{{ errorMessage }}</span>
                </div>

                <form @submit.prevent="calculate" class="space-y-4 text-xs">
                    <!-- Transport Mode Selector -->
                    <div>
                        <label class="block font-bold text-[#13293D] mb-1.5 uppercase font-mono text-[10px]">Moda Pengangkutan</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button
                                type="button"
                                @click="form.transport_mode = 'SEA'; calculate();"
                                :class="[
                                    'p-3 rounded-xl border text-center transition cursor-pointer flex flex-col items-center gap-1',
                                    form.transport_mode === 'SEA'
                                        ? 'bg-[#13293D] text-[#A7E4DB] border-[#13293D] font-bold shadow-xs'
                                        : 'bg-slate-50 hover:bg-slate-100 border-slate-200 text-slate-700'
                                ]"
                            >
                                <i class="bi bi-water text-lg"></i>
                                <span>Laut (SEA)</span>
                            </button>

                            <button
                                type="button"
                                @click="form.transport_mode = 'AIR'; calculate();"
                                :class="[
                                    'p-3 rounded-xl border text-center transition cursor-pointer flex flex-col items-center gap-1',
                                    form.transport_mode === 'AIR'
                                        ? 'bg-[#13293D] text-[#A7E4DB] border-[#13293D] font-bold shadow-xs'
                                        : 'bg-slate-50 hover:bg-slate-100 border-slate-200 text-slate-700'
                                ]"
                            >
                                <i class="bi bi-airplane text-lg"></i>
                                <span>Udara (AIR)</span>
                            </button>

                            <button
                                type="button"
                                @click="form.transport_mode = 'LAND'; calculate();"
                                :class="[
                                    'p-3 rounded-xl border text-center transition cursor-pointer flex flex-col items-center gap-1',
                                    form.transport_mode === 'LAND'
                                        ? 'bg-[#13293D] text-[#A7E4DB] border-[#13293D] font-bold shadow-xs'
                                        : 'bg-slate-50 hover:bg-slate-100 border-slate-200 text-slate-700'
                                ]"
                            >
                                <i class="bi bi-truck text-lg"></i>
                                <span>Darat (LAND)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Origin Location -->
                    <div>
                        <label class="block font-bold text-[#13293D] mb-1">Pelabuhan / Hub Asal (Origin)</label>
                        <select
                            v-model="form.origin_id"
                            @change="calculate"
                            required
                            class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-[#13293D] focus:outline-none focus:ring-1 focus:ring-[#67C7C6]"
                        >
                            <option v-for="l in locations" :key="l.id" :value="l.id">
                                {{ l.code }} &bull; {{ l.name }} ({{ l.city }}, {{ l.country }})
                            </option>
                        </select>
                    </div>

                    <!-- Destination Location -->
                    <div>
                        <label class="block font-bold text-[#13293D] mb-1">Pelabuhan / Hub Tujuan (Destination)</label>
                        <select
                            v-model="form.destination_id"
                            @change="calculate"
                            required
                            class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-[#13293D] focus:outline-none focus:ring-1 focus:ring-[#67C7C6]"
                        >
                            <option v-for="l in locations" :key="l.id" :value="l.id">
                                {{ l.code }} &bull; {{ l.name }} ({{ l.city }}, {{ l.country }})
                            </option>
                        </select>
                    </div>

                    <!-- Cargo Weight -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="font-bold text-[#13293D]">Estimasi Berat Muatan (kg)</label>
                            <span class="text-[11px] font-mono text-[#265D7A] font-bold">{{ Number(form.weight_kg).toLocaleString() }} kg</span>
                        </div>
                        <input
                            v-model.number="form.weight_kg"
                            @input="calculate"
                            type="number"
                            min="1"
                            max="500000"
                            step="50"
                            required
                            class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl font-mono text-xs text-[#13293D] focus:outline-none focus:ring-1 focus:ring-[#67C7C6]"
                        />
                        <div class="flex gap-2 mt-2">
                            <button
                                v-for="w in [500, 1500, 2500, 10000]"
                                :key="w"
                                type="button"
                                @click="form.weight_kg = w; calculate();"
                                class="flex-1 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-mono font-bold transition"
                            >
                                {{ w >= 1000 ? `${w/1000}t` : `${w}kg` }}
                            </button>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <button
                        type="submit"
                        :disabled="isCalculating"
                        class="w-full h-11 bg-[#265D7A] hover:bg-[#13293D] text-white font-bold rounded-xl transition flex items-center justify-center gap-2 cursor-pointer shadow-xs disabled:opacity-50"
                    >
                        <i :class="['bi', isCalculating ? 'bi-arrow-clockwise animate-spin' : 'bi-calculator']"></i>
                        <span>{{ isCalculating ? 'Menghitung Simulasi...' : 'Hitung Ulang Estimasi' }}</span>
                    </button>
                </form>
            </div>

            <!-- Results Card (Right Column) -->
            <div class="lg:col-span-7 space-y-5">
                <!-- Vehicle Graphic Card -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs overflow-hidden">
                    <div class="flex items-center justify-between pb-3 mb-2 border-b border-slate-100">
                        <div>
                            <span class="text-[10px] font-mono uppercase text-[#265D7A] font-bold">Armada Moda Terpilih</span>
                            <div class="font-extrabold text-[#13293D] text-sm">
                                {{ form.transport_mode === 'AIR' ? 'Pesawat Kargo Freighter (Express Air)' : (form.transport_mode === 'LAND' ? 'Truk Kontainer Logistik (Road Transport)' : 'Kapal Kontainer Samudera (Ocean Freight)') }}
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-full bg-[#13293D] text-[#A7E4DB] text-[10px] font-mono font-bold uppercase">
                            {{ form.transport_mode }} FREIGHT
                        </span>
                    </div>

                    <div class="bg-gradient-to-b from-slate-50/80 to-white rounded-xl p-4 border border-slate-100 flex items-center justify-center my-2">
                        <VehicleGraphic
                            :mode="form.transport_mode"
                            class-name="w-full max-w-sm h-36 sm:h-44"
                        />
                    </div>

                    <!-- Live Calculated Results Breakdown -->
                    <div v-if="result" class="space-y-4 pt-3 border-t border-slate-100">
                        <!-- Top 3 Metric Pills -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs font-mono">
                            <!-- Metric 1: Distance -->
                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                <span class="text-[10px] uppercase text-slate-400 font-bold block">Jarak Rute (Haversine)</span>
                                <div class="text-lg font-black text-[#13293D] mt-0.5">
                                    {{ Number(result.distance_km).toLocaleString() }} <span class="text-xs font-normal">km</span>
                                </div>
                                <div class="text-[10px] text-slate-500 font-sans">
                                    {{ result.origin.code }} &rarr; {{ result.destination.code }}
                                </div>
                            </div>

                            <!-- Metric 2: Transit Duration -->
                            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                <span class="text-[10px] uppercase text-slate-400 font-bold block">Estimasi Transit</span>
                                <div class="text-lg font-black text-[#13293D] mt-0.5">
                                    ~{{ result.estimated_transit_days }} <span class="text-xs font-normal">Hari</span>
                                </div>
                                <div class="text-[10px] text-slate-500 font-sans">
                                    {{ result.estimated_transit_hours }} jam operasional
                                </div>
                            </div>

                            <!-- Metric 3: Carbon Footprint -->
                            <div class="bg-emerald-50/70 p-3 rounded-xl border border-emerald-100">
                                <span class="text-[10px] uppercase text-emerald-800 font-bold block">Jejak Karbon (CO₂)</span>
                                <div class="text-lg font-black text-emerald-900 mt-0.5">
                                    {{ result.carbon_kg }} <span class="text-xs font-normal">kg</span>
                                </div>
                                <div class="text-[10px] text-emerald-700 font-sans font-medium">
                                    {{ result.carbon_rating }}
                                </div>
                            </div>
                        </div>

                        <!-- Price Estimation Hero -->
                        <div class="bg-gradient-to-br from-[#13293D] to-[#265D7A] rounded-xl p-5 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <span class="text-[10px] font-mono uppercase text-[#A7E4DB] tracking-wider font-bold block">
                                    Total Estimasi Biaya Muatan (All-in Tariff)
                                </span>
                                <div class="text-2xl sm:text-3xl font-black font-mono text-white mt-1">
                                    ${{ Number(result.total_cost_usd).toLocaleString() }}
                                </div>
                                <div class="text-xs text-[#A7E4DB] font-mono mt-0.5">
                                    &asymp; Rp {{ Number(result.total_cost_idr).toLocaleString() }} (Kurs Rp 15.800/USD)
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="copyEstimate"
                                class="px-4 py-2 bg-[#67C7C6] hover:bg-[#A7E4DB] text-[#13293D] font-bold rounded-xl text-xs transition inline-flex items-center gap-1.5 cursor-pointer shadow-xs self-start sm:self-auto"
                            >
                                <i :class="['bi', copiedEstimate ? 'bi-check2' : 'bi-share']"></i>
                                <span>{{ copiedEstimate ? 'Estimasi Tersalin!' : 'Salin Estimasi' }}</span>
                            </button>
                        </div>

                        <!-- Price Breakdown -->
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs space-y-1.5">
                            <div class="flex justify-between text-slate-500 font-mono">
                                <span>Biaya Dasar Dokumen &amp; Terminal (Base Fee):</span>
                                <span class="font-bold text-[#13293D]">${{ result.breakdown?.base_fee_usd }}</span>
                            </div>
                            <div class="flex justify-between text-slate-500 font-mono">
                                <span>Biaya Berat Muatan ({{ result.effective_weight_kg }} kg):</span>
                                <span class="font-bold text-[#13293D]">${{ result.breakdown?.weight_cost_usd }}</span>
                            </div>
                            <div class="flex justify-between text-slate-500 font-mono">
                                <span>Biaya Jarak Tempuh ({{ result.distance_km }} km):</span>
                                <span class="font-bold text-[#13293D]">${{ result.breakdown?.distance_cost_usd }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
