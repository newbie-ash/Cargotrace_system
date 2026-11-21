<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { feature } from 'topojson-client';
import countries110m from 'world-atlas/countries-110m.json';

const props = defineProps({
    carriers: {
        type: Array,
        default: () => [],
    },
    locations: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({ on_time_rate: 100 }),
    },
});

const featuredHub = computed(() =>
    props.locations.find((l) => l.code === 'SGSIN') || props.locations[0] || null
);

// Dynamic SVG Circular Progress for On-Time SLA Rate
const gaugeCircumference = 2 * Math.PI * 44; // r = 44 -> ~276.46
const gaugeOffset = computed(() => {
    const rate = Math.max(0, Math.min(100, Number(props.stats.on_time_rate ?? 0)));
    return gaugeCircumference * (1 - rate / 100);
});

const worldMapRef = ref(null);
let map = null;
let layerGroup = null;

const escapeHtml = (value) =>
    String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');

const hasCoords = (loc) =>
    loc &&
    loc.latitude !== null &&
    loc.latitude !== undefined &&
    loc.longitude !== null &&
    loc.longitude !== undefined &&
    Number.isFinite(Number(loc.latitude)) &&
    Number.isFinite(Number(loc.longitude));

const createArcPoints = (start, end, offsetRatio = 0.08, segments = 16) => {
    const [lat1, lng1] = start;
    const [lat2, lng2] = end;
    const midLat = (lat1 + lat2) / 2;
    const midLng = (lng1 + lng2) / 2;
    const dLat = lat2 - lat1;
    const dLng = lng2 - lng1;
    const dist = Math.hypot(dLat, dLng);

    const normalLat = -dLng / (dist || 1);
    const normalLng = dLat / (dist || 1);
    const ctrlLat = midLat + normalLat * dist * offsetRatio;
    const ctrlLng = midLng + normalLng * dist * offsetRatio;

    const points = [];
    for (let i = 0; i <= segments; i++) {
        const t = i / segments;
        const lat = (1 - t) * (1 - t) * lat1 + 2 * (1 - t) * t * ctrlLat + t * t * lat2;
        const lng = (1 - t) * (1 - t) * lng1 + 2 * (1 - t) * t * ctrlLng + t * t * lng2;
        points.push([lat, lng]);
    }
    return points;
};

const drawRouteCorridor = (codes, styleOptions = {}, curveOffset = 0.08) => {
    if (!layerGroup || !props.locations.length) return;

    const matched = codes
        .map((c) => props.locations.find((l) => l.code === c && hasCoords(l)))
        .filter(Boolean);

    if (matched.length < 2) return;

    const fullPath = [];
    for (let i = 0; i < matched.length - 1; i++) {
        const p1 = [parseFloat(matched[i].latitude), parseFloat(matched[i].longitude)];
        const p2 = [parseFloat(matched[i + 1].latitude), parseFloat(matched[i + 1].longitude)];
        const seg = createArcPoints(p1, p2, curveOffset);
        fullPath.push(...(i === 0 ? seg : seg.slice(1)));
    }

    if (styleOptions.glow) {
        L.polyline(fullPath, {
            color: styleOptions.glow,
            weight: 6,
            opacity: 0.35,
            smoothFactor: 1,
        }).addTo(layerGroup);
    }

    L.polyline(fullPath, {
        color: styleOptions.color,
        weight: styleOptions.weight || 2.5,
        opacity: styleOptions.opacity ?? 0.9,
        dashArray: styleOptions.dashArray || null,
        smoothFactor: 1,
    }).addTo(layerGroup);
};

const initWorldMap = () => {
    if (!worldMapRef.value) return;

    if (map) {
        map.remove();
        map = null;
    }

    map = L.map(worldMapRef.value, {
        center: [20, 30],
        zoom: 1.5,
        zoomSnap: 0.25,
        minZoom: 1,
        maxZoom: 12,
        zoomControl: false,
        scrollWheelZoom: false,
        worldCopyJump: true,
        attributionControl: false,
    });

    L.control.zoom({ position: 'topright' }).addTo(map);

    // Vector Countries layer styled identical to 3D Globe (#362117 land with warm oat milk ocean)
    const unwrapRing = (ring) => {
        if (!ring.length) return ring;
        const result = [[ring[0][0], ring[0][1]]];
        for (let i = 1; i < ring.length; i++) {
            let [lng, lat] = ring[i];
            const prevLng = result[i - 1][0];
            while (lng - prevLng > 180) lng -= 360;
            while (lng - prevLng < -180) lng += 360;
            result.push([lng, lat]);
        }
        return result;
    };

    const rawCountries = feature(countries110m, countries110m.objects.countries);
    const cleanCountries = {
        ...rawCountries,
        features: rawCountries.features
            .filter((f) => String(f.id) !== '010')
            .map((f) => ({
                ...f,
                geometry: {
                    ...f.geometry,
                    coordinates: f.geometry.type === 'Polygon'
                        ? f.geometry.coordinates.map(unwrapRing)
                        : f.geometry.coordinates.map((poly) => poly.map(unwrapRing)),
                },
            })),
    };

    L.geoJSON(cleanCountries, {
        style: {
            fillColor: '#362117',
            fillOpacity: 0.94,
            color: 'rgba(54, 33, 23, 0.45)',
            weight: 1.1,
            opacity: 0.85,
        },
    }).addTo(map);

    layerGroup = L.layerGroup().addTo(map);

    const validLocs = props.locations.filter(hasCoords);
    if (!validLocs.length) return;

    drawRouteCorridor(
        ['IDJKT', 'SGSIN', 'AEDXB', 'NLRTM'],
        { color: '#2563EB', glow: '#93C5FD', weight: 2.5, dashArray: '6, 8' },
        0.08
    );

    drawRouteCorridor(
        ['KRPUS', 'CNSHA', 'SGSIN'],
        { color: '#3B82F6', weight: 1.8, opacity: 0.85, dashArray: '4, 8' },
        -0.06
    );

    const bounds = [];
    validLocs.forEach((loc) => {
        const lat = parseFloat(loc.latitude);
        const lng = parseFloat(loc.longitude);

        bounds.push([lat, lng]);

        const isOrigin = loc.code === 'IDJKT' || loc.code === 'IDTPP';
        const isDest = loc.code === 'NLRTM';

        const dotClass = isOrigin
            ? 'bg-[#2563EB] shadow-[0_0_12px_#2563EB] ring-4 ring-blue-500/25'
            : isDest
              ? 'bg-[#362117] shadow-[0_0_12px_#362117] ring-4 ring-black/25'
              : 'bg-[#60A5FA] shadow-[0_0_10px_#60A5FA] ring-4 ring-blue-400/25';

        const icon = L.divIcon({
            html: `<div class="relative flex items-center justify-center w-6 h-6">
                <span class="w-3 h-3 rounded-full ${dotClass} border border-white"></span>
            </div>`,
            className: 'custom-leaflet-pin',
            iconSize: [24, 24],
            iconAnchor: [12, 12],
            popupAnchor: [0, -10],
        });

        const popupHtml = `
            <div class="font-sans text-xs min-w-[180px] p-1 text-[#FAF7F2]">
                <div class="flex items-center justify-between gap-2 pb-1.5 mb-1.5 border-b border-white/15">
                    <span class="font-mono font-extrabold text-[11px] text-[#93C5FD]">${escapeHtml(loc.code)}</span>
                    <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-white/10 text-white">${escapeHtml(loc.type)}</span>
                </div>
                <div class="font-bold text-white">${escapeHtml(loc.name)}</div>
                <div class="text-[11px] text-slate-300">${escapeHtml(loc.city)}, ${escapeHtml(loc.country)}</div>
                <div class="mt-1.5 text-[10px] font-mono text-[#93C5FD]">${lat.toFixed(4)}, ${lng.toFixed(4)}</div>
            </div>
        `;

        L.marker([lat, lng], { icon }).bindPopup(popupHtml).addTo(layerGroup);
    });

    if (bounds.length > 1) {
        map.fitBounds(bounds, { padding: [32, 32], maxZoom: 4 });
    }
};

const resetGlobalView = () => {
    if (!map) return;
    const bounds = props.locations
        .filter(hasCoords)
        .map((l) => [parseFloat(l.latitude), parseFloat(l.longitude)]);
    if (bounds.length > 1) {
        map.fitBounds(bounds, { padding: [32, 32], maxZoom: 4 });
    } else {
        map.setView([20, 30], 1.5);
    }
};

onMounted(() => {
    nextTick(() => {
        initWorldMap();
    });
});

watch(
    () => props.locations,
    () => {
        nextTick(() => {
            initWorldMap();
        });
    },
    { deep: true }
);

onUnmounted(() => {
    if (map) {
        map.remove();
        map = null;
    }
});
</script>

<template>
    <section
        id="bento"
        aria-label="Arsitektur Delixie Bento Grid"
        class="py-24 px-6 sm:px-12 lg:px-16 relative overflow-hidden bg-transparent"
    >
        <div class="max-w-7xl mx-auto relative z-10">
            <!-- Section Header -->
            <div class="max-w-2xl mb-14">
                <div class="flex items-center gap-3 mb-3">
                    <span class="w-8 h-[2px] bg-[#2563EB]" aria-hidden="true"></span>
                    <span class="text-[11px] font-mono font-bold uppercase tracking-widest text-[#2E1E16]">
                        ARSITEKTUR SISTEM DELIXIE
                    </span>
                </div>
                <h2 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-[#2E1E16] leading-tight">
                    Ketahanan Rantai Pasok Terintegrasi.
                </h2>
                <p class="text-sm sm:text-base font-normal text-[#5A4A40] mt-3 leading-relaxed">
                    Dirancang menggunakan mesin state deterministik dengan validasi jalur geospasial, kalkulasi risiko cuaca maritim, serta penegakan otoritas akses per peran.
                </p>
            </div>

            <!-- Asymmetric 12-Column Frosted Glass Bento Grid -->
            <div class="grid grid-cols-12 gap-6 sm:gap-7">
                <!-- CARD 1: Live Intercontinental Route Visibility (8 cols x 2 rows) -->
                <article class="col-span-12 md:col-span-8 md:row-span-2 bg-white/95 backdrop-blur-2xl border border-white/95 rounded-3xl p-6 sm:p-8 shadow-[0_20px_50px_rgba(46,30,22,0.06)] transition-all duration-300 hover:-translate-y-1 hover:border-[#2563EB]/40 flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-4 mb-6">
                        <div>
                            <span class="px-2.5 py-1 rounded-lg bg-[#FAF7F2] text-[#2E1E16] border border-[#2E1E16]/10 text-[10px] font-bold uppercase tracking-wider font-mono">
                                Peta Dunia Interaktif + Koridor Maritim
                            </span>
                            <h3 class="text-xl sm:text-2xl font-bold text-[#2E1E16] mt-3.5">
                                Visibilitas Rute Lintas Benua
                            </h3>
                            <p class="text-xs sm:text-sm font-normal text-[#5A4A40] mt-1.5 max-w-lg leading-relaxed">
                                Peta dunia interaktif menghubungkan seluruh pelabuhan dan hub global dari database dengan jalur pelayaran real-time tanpa blind spot.
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="resetGlobalView"
                            title="Reset tampilan peta dunia"
                            aria-label="Reset tampilan peta dunia"
                            class="w-11 h-11 rounded-2xl bg-[#2E1E16] hover:bg-[#362117] border border-white/20 text-[#FAF7F2] flex items-center justify-center shrink-0 transition cursor-pointer shadow-md"
                        >
                            <i class="bi bi-globe-asia-australia text-lg" aria-hidden="true"></i>
                        </button>
                    </div>

                    <!-- Real Interactive World Map Framed Inside Light Frosted Card -->
                    <div class="relative rounded-2xl bg-[#EDE5DB] border border-[#2E1E16]/15 overflow-hidden flex-1 min-h-[300px] flex flex-col shadow-inner bento-world-map">
                        <div ref="worldMapRef" class="w-full flex-1 min-h-[260px] sm:min-h-[310px] z-10"></div>

                        <!-- Telemetry Legend & Corridor Footer -->
                        <div class="px-4 py-3 bg-[#FAF7F2] border-t border-[#2E1E16]/10 flex flex-wrap items-center justify-between gap-3 font-mono text-[11px] relative z-20 text-[#2E1E16]">
                            <div class="flex flex-wrap items-center gap-4">
                                <span class="inline-flex items-center gap-1.5 text-[#2563EB] font-bold">
                                    <span class="w-2 h-2 rounded-full bg-[#2563EB]"></span> IDJKT Origin
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-[#362117] font-bold">
                                    <span class="w-2 h-2 rounded-full bg-[#60A5FA]"></span> Hub Global ({{ locations.length }})
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-[#362117] font-bold">
                                    <span class="w-2 h-2 rounded-full bg-[#362117]"></span> NLRTM Hub
                                </span>
                            </div>
                            <button
                                type="button"
                                @click="resetGlobalView"
                                class="px-3.5 py-1.5 rounded-full bg-[#2563EB] hover:bg-[#1D4ED8] text-white transition text-[10px] font-bold cursor-pointer"
                            >
                                <i class="bi bi-arrows-fullscreen mr-1" aria-hidden="true"></i>Reset Peta
                            </button>
                        </div>
                    </div>
                </article>

                <!-- CARD 2: ETA & Delay Threshold Rule (4 cols) -->
                <article class="col-span-12 md:col-span-4 bg-white/95 backdrop-blur-2xl border border-white/95 rounded-3xl p-6 sm:p-7 shadow-[0_20px_50px_rgba(46,30,22,0.06)] transition-all duration-300 hover:-translate-y-1 hover:border-[#2563EB]/40 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3.5">
                            <span class="px-2.5 py-1 rounded-lg bg-[#FAF7F2] text-[#2E1E16] border border-[#2E1E16]/10 text-[10px] font-bold uppercase font-mono">
                                Aturan SLA Deterministik
                            </span>
                            <i class="bi bi-clock-history text-[#2563EB] text-lg" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#2E1E16]">Deteksi Keterlambatan ETA</h3>
                        <p class="text-xs font-normal text-[#5A4A40] mt-1.5 leading-relaxed">
                            Klasifikasi otomatis durasi keterlambatan pengiriman mengacu ambang batas operasional global.
                        </p>
                    </div>

                    <div class="space-y-2 mt-5 font-mono text-xs">
                        <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-emerald-500/15 border border-emerald-600/25 text-emerald-950 font-semibold">
                            <span class="font-bold">LOW DELAY</span>
                            <span>1 - 12 Jam</span>
                        </div>
                        <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-amber-500/15 border border-amber-600/25 text-amber-950 font-semibold">
                            <span class="font-bold">MEDIUM DELAY</span>
                            <span>13 - 48 Jam</span>
                        </div>
                        <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-rose-500/15 border border-rose-600/25 text-rose-950 font-semibold">
                            <span class="font-bold">HIGH / CRITICAL</span>
                            <span>&gt; 48 Jam</span>
                        </div>
                    </div>
                </article>

                <!-- CARD 3: Ekspedisi Udara, Laut & Darat (4 cols) -->
                <article class="col-span-12 md:col-span-4 bg-white/95 backdrop-blur-2xl border border-white/95 rounded-3xl p-6 sm:p-7 shadow-[0_20px_50px_rgba(46,30,22,0.06)] transition-all duration-300 hover:-translate-y-1 hover:border-[#2563EB]/40 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3.5">
                            <span class="px-2.5 py-1 rounded-lg bg-[#FAF7F2] text-[#2E1E16] border border-[#2E1E16]/10 text-[10px] font-bold uppercase font-mono">
                                {{ carriers.length }} Mitra Ekspedisi
                            </span>
                            <i class="bi bi-truck text-[#2563EB] text-lg" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#2E1E16]">Ekspedisi Udara, Laut &amp; Darat</h3>
                        <p class="text-xs font-normal text-[#5A4A40] mt-1.5 mb-5 leading-relaxed">
                            Integrasi armada pelayaran kargo, maskapai udara, serta angkutan darat lintas negara.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="carrier in carriers.slice(0, 6)"
                            :key="carrier.id"
                            class="px-2.5 py-1.5 rounded-lg bg-[#FAF7F2] border border-[#2E1E16]/15 text-[11px] font-mono font-bold text-[#2E1E16] shadow-xs"
                        >
                            {{ carrier.code }} &bull; {{ carrier.transport_mode }}
                        </span>
                        <span
                            v-if="!carriers.length"
                            class="text-xs text-[#5A4A40] italic"
                        >
                            Belum ada mitra carrier aktif terdaftar.
                        </span>
                    </div>
                </article>

                <!-- CARD 4: Cuaca Pelabuhan & Geocoding (4 cols) -->
                <article class="col-span-12 md:col-span-4 bg-white/95 backdrop-blur-2xl border border-white/95 rounded-3xl p-6 sm:p-7 shadow-[0_20px_50px_rgba(46,30,22,0.06)] transition-all duration-300 hover:-translate-y-1 hover:border-[#2563EB]/40 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3.5">
                            <span class="px-2.5 py-1 rounded-lg bg-[#FAF7F2] text-[#2E1E16] border border-[#2E1E16]/10 text-[10px] font-bold uppercase font-mono">
                                Open-Meteo &amp; OSM
                            </span>
                            <i class="bi bi-cloud-sun text-[#2563EB] text-lg" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#2E1E16]">Cuaca Pelabuhan &amp; Geocoding</h3>
                        <p class="text-xs font-normal text-[#5A4A40] mt-1.5 leading-relaxed">
                            Sinkronisasi koordinat pelabuhan via Nominatim OSM serta perekaman cuaca Open-Meteo pada setiap checkpoint transit.
                        </p>
                    </div>

                    <div class="mt-5 p-3.5 rounded-2xl bg-[#FAF7F2] border border-[#2E1E16]/10 flex items-center justify-between gap-3 text-xs shadow-xs">
                        <div v-if="featuredHub" class="min-w-0">
                            <div class="font-bold text-[#2E1E16] truncate">{{ featuredHub.name }} ({{ featuredHub.code }})</div>
                            <div class="text-[11px] font-mono text-[#5A4A40] mt-0.5">
                                {{ parseFloat(featuredHub.latitude).toFixed(2) }}, {{ parseFloat(featuredHub.longitude).toFixed(2) }}
                            </div>
                        </div>
                        <div v-else class="text-[#5A4A40] text-xs">
                            Koordinat Hub Tersinkronisasi
                        </div>
                        <span class="px-2.5 py-1 rounded-lg bg-[#2E1E16] text-[#FAF7F2] font-mono text-[10px] font-bold shrink-0">
                            {{ locations.length }} Titik
                        </span>
                    </div>
                </article>

                <!-- CARD 5: Proactive Exception Handling (4 cols) -->
                <article class="col-span-12 md:col-span-4 bg-white/95 backdrop-blur-2xl border border-white/95 rounded-3xl p-6 sm:p-7 shadow-[0_20px_50px_rgba(46,30,22,0.06)] transition-all duration-300 hover:-translate-y-1 hover:border-[#2563EB]/40 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3.5">
                            <span class="px-2.5 py-1 rounded-lg bg-[#FAF7F2] text-[#2E1E16] border border-[#2E1E16]/10 text-[10px] font-bold uppercase font-mono">
                                Tata Kelola Insiden
                            </span>
                            <i class="bi bi-shield-exclamation text-rose-700 text-lg" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#2E1E16]">Penanganan Insiden Proaktif</h3>
                        <p class="text-xs font-normal text-[#5A4A40] mt-1.5 leading-relaxed">
                            Pencatatan insiden Customs Hold, Weather Alert, dan Port Congestion dengan siklus resolusi teraudit.
                        </p>
                    </div>

                    <div class="mt-5 flex flex-wrap items-center justify-between gap-1.5 text-[10px] font-mono font-bold">
                        <span class="px-2.5 py-1.5 rounded-lg bg-rose-500/15 text-rose-900 border border-rose-600/25">OPEN</span>
                        <i class="bi bi-arrow-right text-[#5A4A40]" aria-hidden="true"></i>
                        <span class="px-2.5 py-1.5 rounded-lg bg-amber-500/15 text-amber-900 border border-amber-600/25">INVESTIGATING</span>
                        <i class="bi bi-arrow-right text-[#5A4A40]" aria-hidden="true"></i>
                        <span class="px-2.5 py-1.5 rounded-lg bg-emerald-500/15 text-emerald-900 border border-emerald-600/25">RESOLVED</span>
                    </div>
                </article>

                <!-- CARD 6: SLA Compliance & Real-Time Gauge Ring (4 cols) -->
                <article class="col-span-12 md:col-span-4 bg-white/95 backdrop-blur-2xl border border-white/95 rounded-3xl p-6 sm:p-7 shadow-[0_20px_50px_rgba(46,30,22,0.06)] transition-all duration-300 hover:-translate-y-1 hover:border-[#2563EB]/40 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3.5">
                            <span class="px-2.5 py-1 rounded-lg bg-[#FAF7F2] text-[#2E1E16] border border-[#2E1E16]/10 text-[10px] font-bold uppercase font-mono">
                                Performa SLA Aktual
                            </span>
                            <i class="bi bi-shield-check text-[#2563EB] text-lg" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-[#2E1E16]">Ketepatan Jadwal Armada</h3>
                        <p class="text-xs font-normal text-[#5A4A40] mt-1.5 leading-relaxed">
                            Persentase pengantaran kargo tepat waktu dihitung langsung dari histori database aktual.
                        </p>
                    </div>

                    <!-- Circular Ring Metric Gauge -->
                    <div class="my-5 flex items-center justify-center">
                        <div class="relative w-28 h-28 flex items-center justify-center">
                            <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="44" fill="none" stroke="rgba(46,30,22,0.10)" stroke-width="8" />
                                <circle
                                    cx="50"
                                    cy="50"
                                    r="44"
                                    fill="none"
                                    stroke="#2563EB"
                                    stroke-width="8"
                                    stroke-linecap="round"
                                    :stroke-dasharray="gaugeCircumference"
                                    :stroke-dashoffset="gaugeOffset"
                                />
                            </svg>
                            <span class="absolute font-mono text-2xl font-extrabold text-[#2E1E16]">
                                {{ stats.on_time_rate }}%
                            </span>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-[#FAF7F2] border border-[#2E1E16]/10 flex items-center justify-between">
                        <span class="text-xs font-bold text-[#2E1E16]">Tingkat Ketepatan Waktu</span>
                        <i class="bi bi-truck-flatbed text-[#2563EB] text-base" aria-hidden="true"></i>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>

<style>
/* Leaflet styling matching 3D Globe (#EDE5DB ocean + #362117 land) */
.bento-world-map .leaflet-container {
    background: #EDE5DB !important;
}
.bento-world-map .leaflet-bar a {
    background-color: #FAF7F2 !important;
    border-color: rgba(46, 30, 22, 0.15) !important;
    color: #2E1E16 !important;
}
.bento-world-map .leaflet-bar a:hover {
    background-color: #2563EB !important;
    color: #FFFFFF !important;
}
</style>
