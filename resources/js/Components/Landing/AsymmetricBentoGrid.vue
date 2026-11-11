<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

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

// Curve helper for realistic maritime route arcs
const createArcPoints = (start, end, offsetRatio = 0.08, segments = 16) => {
    const [lat1, lng1] = start;
    const [lat2, lng2] = end;
    const midLat = (lat1 + lat2) / 2;
    const midLng = (lng1 + lng2) / 2;
    const dLat = lat2 - lat1;
    const dLng = lng2 - lng1;
    const ctrlLat = midLat - dLng * offsetRatio;
    const ctrlLng = midLng + dLat * offsetRatio;

    const pts = [];
    for (let i = 0; i <= segments; i++) {
        const t = i / segments;
        const inv = 1 - t;
        const lat = inv * inv * lat1 + 2 * inv * t * ctrlLat + t * t * lat2;
        const lng = inv * inv * lng1 + 2 * inv * t * ctrlLng + t * t * lng2;
        pts.push([lat, lng]);
    }
    return pts;
};

const drawRouteCorridor = (codes, styleOptions, curveOffset = 0.07) => {
    const matched = codes
        .map((code) => props.locations.find((l) => l.code === code && hasCoords(l)))
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
            opacity: 0.28,
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
        attributionControl: true,
    });

    L.control.zoom({ position: 'topright' }).addTo(map);

    // Real Global Dark Map Tiles (Esri World Dark Gray Canvas - No API Key Required)
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri',
        maxZoom: 16,
        maxNativeZoom: 10,
    }).addTo(map);

    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Reference/MapServer/tile/{z}/{y}/{x}', {
        attribution: '',
        maxZoom: 16,
        maxNativeZoom: 10,
        opacity: 0.75,
    }).addTo(map);

    layerGroup = L.layerGroup().addTo(map);

    const validLocs = props.locations.filter(hasCoords);
    if (!validLocs.length) return;

    // 1. Draw Main Indonesia-Suez-Europe Maritime Corridor & East Asia Feeder
    drawRouteCorridor(
        ['IDJKT', 'SGSIN', 'AEDXB', 'NLRTM'],
        { color: '#38BDF8', glow: '#2563EB', weight: 2.5, dashArray: '6, 8' },
        0.08
    );

    drawRouteCorridor(
        ['KRPUS', 'CNSHA', 'SGSIN'],
        { color: '#60A5FA', weight: 1.8, opacity: 0.65, dashArray: '4, 8' },
        -0.06
    );

    // 2. Plot Real Global Hub Markers
    const bounds = [];
    validLocs.forEach((loc) => {
        const lat = parseFloat(loc.latitude);
        const lng = parseFloat(loc.longitude);

        bounds.push([lat, lng]);

        const isOrigin = loc.code === 'IDJKT' || loc.code === 'IDTPP';
        const isDest = loc.code === 'NLRTM';

        const dotClass = isOrigin
            ? 'bg-emerald-400 shadow-[0_0_12px_#10b981] ring-4 ring-emerald-500/25'
            : isDest
              ? 'bg-amber-400 shadow-[0_0_12px_#f59e0b] ring-4 ring-amber-500/25'
              : 'bg-blue-500 shadow-[0_0_10px_#3b82f6] ring-4 ring-blue-500/20';

        const icon = L.divIcon({
            html: `<div class="relative flex items-center justify-center w-6 h-6">
                <span class="w-3 h-3 rounded-full ${dotClass} border border-slate-950"></span>
            </div>`,
            className: 'custom-leaflet-pin',
            iconSize: [24, 24],
            iconAnchor: [12, 12],
            popupAnchor: [0, -10],
        });

        const popupHtml = `
            <div class="font-sans text-xs min-w-[180px] p-1 text-slate-200">
                <div class="flex items-center justify-between gap-2 pb-1.5 mb-1.5 border-b border-white/10">
                    <span class="font-mono font-extrabold text-[11px] text-blue-400">${escapeHtml(loc.code)}</span>
                    <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-white/10 text-slate-300">${escapeHtml(loc.type)}</span>
                </div>
                <div class="font-bold text-white">${escapeHtml(loc.name)}</div>
                <div class="text-[11px] text-slate-400">${escapeHtml(loc.city)}, ${escapeHtml(loc.country)}</div>
                <div class="mt-1.5 text-[10px] font-mono text-slate-500">${lat.toFixed(4)}, ${lng.toFixed(4)}</div>
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
    <section id="bento" aria-label="Arsitektur Ketahanan Rantai Pasok" class="relative py-24 px-4 sm:px-8">
        <div class="max-w-7xl mx-auto relative z-10">
            <!-- Section Header -->
            <div class="max-w-2xl mb-12">
                <span class="text-[11px] font-mono font-bold uppercase tracking-widest text-blue-400 block mb-2">
                    DELIXIE SYSTEM ARCHITECTURE
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-white">
                    Built for Supply Chain Resilience.
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-2.5 leading-relaxed">
                    Dirancang menggunakan mesin state deterministik dengan validasi jalur geospasial, kalkulasi risiko cuaca maritim, serta penegakan otoritas akses per peran.
                </p>
            </div>

            <!-- Asymmetric 12-Column Bento Grid -->
            <div class="grid grid-cols-12 gap-6">
                <!-- CARD 1: Live Intercontinental Route Visibility (8 cols x 2 rows) -->
                <article class="col-span-12 md:col-span-8 md:row-span-2 bg-slate-800/40 backdrop-blur-xl border border-white/10 rounded-3xl p-6 sm:p-8 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-blue-500/40 hover:shadow-[0_0_25px_rgba(37,99,235,0.3)] flex flex-col justify-between">
                    <div class="flex items-start justify-between gap-4 mb-5">
                        <div>
                            <span class="px-2.5 py-1 rounded-md bg-blue-600/20 text-blue-300 border border-blue-500/30 text-[10px] font-bold uppercase tracking-wider font-mono">
                                Leaflet Dark Canvas + Maritime Polyline
                            </span>
                            <h3 class="text-xl sm:text-2xl font-bold text-white mt-3">
                                Live Intercontinental Route Visibility
                            </h3>
                            <p class="text-xs text-slate-300 mt-1.5 max-w-lg leading-relaxed">
                                Peta dunia interaktif menghubungkan seluruh pelabuhan dan hub global dari database dengan jalur pelayaran real-time tanpa blind spot.
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="resetGlobalView"
                            title="Reset tampilan peta dunia"
                            class="w-11 h-11 rounded-2xl bg-blue-600/20 hover:bg-blue-600/35 border border-blue-500/30 text-blue-400 flex items-center justify-center shrink-0 transition cursor-pointer"
                        >
                            <i class="bi bi-globe-asia-australia text-lg" aria-hidden="true"></i>
                        </button>
                    </div>

                    <!-- Real Interactive Leaflet Global World Map -->
                    <div class="relative rounded-2xl bg-[#070B14] border border-white/10 overflow-hidden flex-1 min-h-[300px] flex flex-col">
                        <div ref="worldMapRef" class="w-full flex-1 min-h-[260px] sm:min-h-[310px] z-10"></div>

                        <!-- Telemetry Legend & Corridor Footer -->
                        <div class="px-4 py-3 bg-slate-950/95 border-t border-white/10 flex flex-wrap items-center justify-between gap-3 font-mono text-[11px] relative z-20">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center gap-1.5 text-emerald-400 font-bold">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span> IDJKT Origin
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-blue-400 font-bold">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span> Global Hubs ({{ locations.length }})
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-amber-400 font-bold">
                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span> NLRTM Hub
                                </span>
                            </div>
                            <button
                                type="button"
                                @click="resetGlobalView"
                                class="px-2.5 py-1 rounded-lg bg-white/5 hover:bg-white/15 border border-white/10 text-slate-300 hover:text-white transition text-[10px] cursor-pointer"
                            >
                                <i class="bi bi-arrows-fullscreen mr-1" aria-hidden="true"></i>Reset Peta Dunia
                            </button>
                        </div>
                    </div>
                </article>

                <!-- CARD 2: ETA & Delay Threshold Rule (4 cols) -->
                <article class="col-span-12 md:col-span-4 bg-slate-800/40 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-amber-500/40 hover:shadow-[0_0_20px_rgba(245,158,11,0.25)] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-1 rounded-md bg-amber-500/15 text-amber-300 border border-amber-500/30 text-[10px] font-bold uppercase font-mono">
                                Deterministic Rule
                            </span>
                            <i class="bi bi-clock-history text-amber-400 text-lg" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white">ETA & Delay Threshold Rule</h3>
                        <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                            Klasifikasi otomatis keterlambatan kapal berdasarkan aturan SLA pelayaran global.
                        </p>
                    </div>

                    <div class="space-y-2 mt-4 font-mono text-xs">
                        <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-300">
                            <span class="font-bold">LOW DELAY</span>
                            <span>1 - 12 Jam</span>
                        </div>
                        <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-amber-500/15 border border-amber-500/30 text-amber-300">
                            <span class="font-bold">MEDIUM DELAY</span>
                            <span>13 - 48 Jam</span>
                        </div>
                        <div class="flex items-center justify-between px-3 py-2 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300">
                            <span class="font-bold">HIGH / CRITICAL</span>
                            <span>&gt; 48 Jam</span>
                        </div>
                    </div>
                </article>

                <!-- CARD 3: Ekspedisi Udara, Laut & Darat (4 cols) -->
                <article class="col-span-12 md:col-span-4 bg-slate-800/40 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-blue-500/40 hover:shadow-[0_0_20px_rgba(37,99,235,0.3)] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-1 rounded-md bg-blue-600/20 text-blue-300 border border-blue-500/30 text-[10px] font-bold uppercase font-mono">
                                {{ carriers.length }} Global Carriers
                            </span>
                            <i class="bi bi-truck text-blue-400 text-lg" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white">Ekspedisi Udara, Laut & Darat</h3>
                        <p class="text-xs text-slate-300 mt-1 mb-4 leading-relaxed">
                            Integrasi armada pelayaran kargo, maskapai udara, serta angkutan darat lintas negara.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-1.5">
                        <span
                            v-for="carrier in carriers.slice(0, 6)"
                            :key="carrier.id"
                            class="px-2.5 py-1 rounded-lg bg-slate-900/90 border border-white/10 text-[11px] font-mono text-slate-200"
                        >
                            {{ carrier.code }} &bull; {{ carrier.transport_mode }}
                        </span>
                        <span
                            v-if="!carriers.length"
                            class="text-xs text-slate-400 italic"
                        >
                            Belum ada mitra carrier aktif terdaftar.
                        </span>
                    </div>
                </article>

                <!-- CARD 4: Cuaca Pelabuhan & Geocoding (4 cols) -->
                <article class="col-span-12 md:col-span-4 bg-slate-800/40 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-blue-500/40 hover:shadow-[0_0_20px_rgba(37,99,235,0.3)] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-1 rounded-md bg-blue-500/15 text-blue-300 border border-blue-500/30 text-[10px] font-bold uppercase font-mono">
                                Open-Meteo & OSM
                            </span>
                            <i class="bi bi-cloud-sun-fill text-amber-400 text-lg" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white">Cuaca Pelabuhan & Geocoding</h3>
                        <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                            Sinkronisasi koordinat pelabuhan via Nominatim OSM serta snapshot cuaca Open-Meteo di tiap titik bongkar muat.
                        </p>
                    </div>

                    <div class="mt-4 p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 flex items-center justify-between text-xs">
                        <div>
                            <div class="font-bold text-white">Port of Singapore (SGSIN)</div>
                            <div class="text-[11px] text-slate-400">Angin 12.8 km/h &bull; Visibilitas Jelas</div>
                        </div>
                        <span class="text-xl font-black font-mono text-amber-400">29°C</span>
                    </div>
                </article>

                <!-- CARD 5: Proactive Exception Handling (4 cols) -->
                <article class="col-span-12 md:col-span-4 bg-slate-800/40 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-amber-500/40 hover:shadow-[0_0_20px_rgba(245,158,11,0.25)] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-1 rounded-md bg-rose-500/15 text-rose-300 border border-rose-500/30 text-[10px] font-bold uppercase font-mono">
                                Incident Governance
                            </span>
                            <i class="bi bi-shield-exclamation text-rose-400 text-lg" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white">Proactive Exception Handling</h3>
                        <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                            Pencatatan insiden Customs Hold, Weather Alert, dan Port Congestion dengan siklus resolusi teraudit.
                        </p>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-1.5 text-[10px] font-mono font-bold">
                        <span class="px-2.5 py-1.5 rounded-lg bg-rose-500/20 text-rose-300 border border-rose-500/30">OPEN</span>
                        <i class="bi bi-arrow-right text-slate-500" aria-hidden="true"></i>
                        <span class="px-2.5 py-1.5 rounded-lg bg-amber-500/20 text-amber-300 border border-amber-500/30">INVESTIGATING</span>
                        <i class="bi bi-arrow-right text-slate-500" aria-hidden="true"></i>
                        <span class="px-2.5 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">RESOLVED</span>
                    </div>
                </article>

                <!-- CARD 6: SLA Compliance & Audit Log (4 cols - Real Database On-Time Metric) -->
                <article class="col-span-12 md:col-span-4 bg-slate-800/40 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl transition-all duration-300 hover:-translate-y-1 hover:border-emerald-500/40 hover:shadow-[0_0_20px_rgba(16,185,129,0.25)] flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-1 rounded-md bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 text-[10px] font-bold uppercase font-mono">
                                SLA Live Performance
                            </span>
                            <i class="bi bi-shield-check text-emerald-400 text-lg" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white">Ketepatan Jadwal Armada</h3>
                        <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                            Persentase pengantaran kargo tepat waktu dihitung langsung dari histori database aktual.
                        </p>
                    </div>

                    <div class="mt-4 p-3.5 rounded-2xl bg-slate-900/90 border border-white/10 flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-300">On-Time Delivery Rate</span>
                        <span class="text-2xl font-black font-mono text-emerald-400">{{ stats.on_time_rate }}%</span>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>
