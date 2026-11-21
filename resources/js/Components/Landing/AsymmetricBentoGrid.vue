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
        default: () => ({ on_time_rate: 88 }),
    },
});

// Dynamic SVG Circular Progress for On-Time SLA Rate
const gaugeCircumference = 2 * Math.PI * 40; // r = 40 -> ~251.32
const gaugeOffset = computed(() => {
    const rate = Math.max(0, Math.min(100, Number(props.stats.on_time_rate ?? 88)));
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
            weight: 5,
            opacity: 0.35,
            smoothFactor: 1,
        }).addTo(layerGroup);
    }

    L.polyline(fullPath, {
        color: styleOptions.color,
        weight: styleOptions.weight || 2.2,
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
        center: [20, 25],
        zoom: 1.6,
        zoomSnap: 0.25,
        minZoom: 1,
        maxZoom: 12,
        zoomControl: false,
        scrollWheelZoom: false,
        worldCopyJump: true,
        attributionControl: false,
    });

    L.control.zoom({ position: 'topright' }).addTo(map);

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
            .map((f) => {
                if (f.geometry.type === 'Polygon') {
                    return {
                        ...f,
                        geometry: {
                            type: 'Polygon',
                            coordinates: f.geometry.coordinates.map(unwrapRing),
                        },
                    };
                }
                if (f.geometry.type === 'MultiPolygon') {
                    return {
                        ...f,
                        geometry: {
                            type: 'MultiPolygon',
                            coordinates: f.geometry.coordinates.map((poly) => poly.map(unwrapRing)),
                        },
                    };
                }
                return f;
            }),
    };

    L.geoJSON(cleanCountries, {
        style: () => ({
            fillColor: '#0A3D32',
            fillOpacity: 0.85,
            color: '#012B23',
            weight: 1,
            opacity: 0.95,
        }),
    }).addTo(map);

    layerGroup = L.layerGroup().addTo(map);

    const validLocs = props.locations.filter(hasCoords);

    // Primary Intercontinental Routes
    drawRouteCorridor(
        ['IDJKT', 'SGSIN', 'AEDXB', 'NLRTM'],
        { color: '#BE2388', glow: '#D42B8A', weight: 2.5, dashArray: '6, 8' },
        0.08
    );

    drawRouteCorridor(
        ['KRPUS', 'CNSHA', 'SGSIN'],
        { color: '#FAEFDB', weight: 1.8, opacity: 0.75, dashArray: '4, 8' },
        -0.06
    );

    drawRouteCorridor(
        ['NLRTM', 'USNYC'],
        { color: '#BE2388', glow: '#D42B8A', weight: 2.2, dashArray: '6, 8' },
        0.09
    );

    const bounds = [];
    validLocs.forEach((loc) => {
        const lat = parseFloat(loc.latitude);
        const lng = parseFloat(loc.longitude);

        bounds.push([lat, lng]);

        const isOrigin = loc.code === 'IDJKT' || loc.code === 'IDTPP';
        const isDest = loc.code === 'NLRTM' || loc.code === 'USNYC';

        const dotClass = isOrigin
            ? 'bg-[#BE2388] shadow-[0_0_12px_#BE2388] ring-4 ring-[#BE2388]/30'
            : isDest
              ? 'bg-[#FAEFDB] shadow-[0_0_12px_#FAEFDB] ring-4 ring-[#FAEFDB]/30'
              : 'bg-[#D42B8A] shadow-[0_0_10px_#D42B8A] ring-4 ring-[#D42B8A]/25';

        const icon = L.divIcon({
            html: `<div class="relative flex items-center justify-center w-6 h-6">
                <span class="w-3 h-3 rounded-full ${dotClass} border border-[#001F18]"></span>
            </div>`,
            className: 'custom-leaflet-pin',
            iconSize: [24, 24],
            iconAnchor: [12, 12],
            popupAnchor: [0, -10],
        });

        const marker = L.marker([lat, lng], { icon });
        marker.bindPopup(`
            <div class="p-3 text-xs font-sans text-[#FAEFDB] min-w-[170px] bg-[#012B23] rounded-xl">
                <div class="flex items-center justify-between pb-1.5 border-b border-[#FAEFDB]/15 mb-2">
                    <strong class="font-mono text-sm text-[#BE2388] font-bold">${escapeHtml(loc.code)}</strong>
                    <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-[#0A3D32] text-[#FAEFDB] font-semibold">${escapeHtml(loc.type)}</span>
                </div>
                <div class="font-bold text-xs text-[#FAEFDB] mb-0.5">${escapeHtml(loc.name)}</div>
                <div class="text-[11px] text-[#FAEFDB]/70 mb-2">${escapeHtml(loc.city)}, ${escapeHtml(loc.country)}</div>
                <div class="text-[10px] font-mono text-[#FAEFDB]/60 flex justify-between">
                    <span>${parseFloat(lat).toFixed(2)}°, ${parseFloat(lng).toFixed(2)}°</span>
                    <span class="text-emerald-400 font-bold">Aktif</span>
                </div>
            </div>
        `);
        marker.addTo(layerGroup);
    });

    if (bounds.length > 1) {
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 4 });
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
    <div id="bento" class="relative text-[#FAEFDB]">
        <!-- ======================================================== -->
        <!-- SECTION 3: GLOBAL ROUTE SPOTLIGHT (2-COLUMN SPLIT 8/4)    -->
        <!-- ======================================================== -->
        <section aria-label="Visibilitas Rute Lintas Benua" class="max-w-6xl mx-auto px-6 py-20 sm:py-24 border-t border-[#FAEFDB]/10">
            <!-- Section Header -->
            <div class="max-w-2xl mb-12 sm:mb-14">
                <div class="inline-flex items-center gap-2.5 px-3 py-1 rounded-full bg-[#001F18]/85 border border-[#FAEFDB]/15 text-[11px] font-mono font-bold text-[#BE2388] mb-3">
                    <span class="w-2 h-2 rounded-full bg-[#BE2388]"></span>
                    <span>VISIBILITAS RUTE LINTAS BENUA</span>
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-[#FAEFDB] leading-tight">
                    Lintasan Maritim &amp; Koridor Intermodal.
                </h2>
                <p class="text-sm sm:text-base text-[#FAEFDB]/80 mt-3 leading-relaxed">
                    Pantau pergerakan armada lintas samudera dan transfer darat yang menghubungkan pelabuhan strategis di Asia, Eropa, dan Amerika secara terverifikasi.
                </p>
            </div>

            <!-- 2-Column Split: 8 cols World Map + 4 cols Carrier Integrations -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
                <!-- Left: Interactive World Map (col-span-8) -->
                <div class="lg:col-span-8 bg-[#02332A]/75 backdrop-blur-2xl border border-[#FAEFDB]/18 rounded-[32px] p-4 sm:p-6 shadow-[0_24px_60px_rgba(0,0,0,0.45)] relative overflow-hidden flex flex-col justify-between">
                    <!-- Map Card Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-[#FAEFDB]/15 mb-3 px-2">
                        <div class="flex items-center gap-2.5">
                            <i class="bi bi-geo-alt-fill text-[#BE2388] text-base" aria-hidden="true"></i>
                            <span class="text-xs sm:text-sm font-bold text-[#FAEFDB] font-mono uppercase tracking-wide">
                                Peta Navigasi Interkontinental
                            </span>
                        </div>
                        <span class="text-[11px] font-mono font-bold text-emerald-400 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Live Geospatial Feed
                        </span>
                    </div>

                    <!-- Leaflet Map Container -->
                    <div class="relative w-full rounded-2xl overflow-hidden bento-world-map border border-[#FAEFDB]/12 shadow-inner">
                        <div ref="worldMapRef" class="w-full h-[400px] sm:h-[480px]"></div>
                    </div>

                    <!-- Map Card Bottom Telemetry Legend -->
                    <div class="mt-4 pt-3 border-t border-[#FAEFDB]/12 flex flex-wrap items-center justify-between gap-3 text-xs px-2 font-mono">
                        <div class="flex items-center gap-4 text-[11px] text-[#FAEFDB]/70">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#BE2388]"></span>
                                Koridor Aktif (IDJKT &rarr; NLRTM)
                            </span>
                            <span class="flex items-center gap-1.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#FAEFDB]"></span>
                                Hub Transit
                            </span>
                        </div>
                        <span class="text-[11px] text-[#FAEFDB]/60">
                            {{ locations.length }} Pelabuhan Terpetakan
                        </span>
                    </div>
                </div>

                <!-- Right: Clean Minimal Carrier Integrations List (col-span-4) -->
                <div class="lg:col-span-4 bg-[#02332A]/75 backdrop-blur-2xl border border-[#FAEFDB]/18 rounded-[32px] p-6 sm:p-7 shadow-[0_24px_60px_rgba(0,0,0,0.45)] flex flex-col justify-between">
                    <div>
                        <!-- Header -->
                        <div class="flex items-center justify-between pb-3.5 border-b border-[#FAEFDB]/15 mb-4">
                            <div>
                                <span class="text-[10px] font-mono font-bold uppercase tracking-wider text-[#BE2388] block">
                                    MITRA EKSPEDISI GLOBAL
                                </span>
                                <h3 class="text-lg font-bold text-[#FAEFDB] mt-0.5">Integrasi Armada</h3>
                            </div>
                            <span class="text-xs font-mono font-bold px-2.5 py-1 rounded-full bg-[#001F18] border border-[#FAEFDB]/15 text-[#FAEFDB]">
                                {{ carriers.length }} Carrier
                            </span>
                        </div>

                        <p class="text-xs font-normal text-[#FAEFDB]/75 leading-relaxed mb-4">
                            Koneksi EDI &amp; REST API terverifikasi dengan operator pelayaran dan logistik kontinental terkemuka.
                        </p>

                        <!-- Minimal Carrier List without Heavy Borders -->
                        <div class="divide-y divide-[#FAEFDB]/10">
                            <div
                                v-for="carrier in carriers.slice(0, 6)"
                                :key="carrier.id"
                                class="py-3 flex items-center justify-between gap-3 hover:bg-white/5 px-2 rounded-xl transition group"
                            >
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg bg-[#001F18] border border-[#FAEFDB]/15 flex items-center justify-center font-mono font-extrabold text-xs text-[#FAEFDB] shrink-0 group-hover:border-[#BE2388] transition-colors">
                                        {{ (carrier.code || 'CT').slice(0, 2) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-mono font-bold text-xs text-[#FAEFDB] truncate">
                                            {{ carrier.code }}
                                        </div>
                                        <div class="text-[11px] text-[#FAEFDB]/65 truncate">
                                            {{ carrier.name }}
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="text-[10px] font-mono font-semibold text-[#FAEFDB]/70 px-2 py-0.5 rounded bg-[#001F18] border border-[#FAEFDB]/10">
                                        {{ carrier.transport_mode }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Summary -->
                    <div class="mt-4 pt-3.5 border-t border-[#FAEFDB]/15 flex items-center justify-between text-xs text-[#FAEFDB]/70 font-mono">
                        <span class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            API Telemetri Terhubung
                        </span>
                        <span class="text-[11px] font-bold text-[#FAEFDB]">100% Uptime</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ======================================================== -->
        <!-- SECTION 4: 3-COLUMN EQUAL CAPABILITY STRIP               -->
        <!-- ======================================================== -->
        <section aria-label="Keunggulan Operasional" class="max-w-6xl mx-auto px-6 py-20 sm:py-24 border-t border-[#FAEFDB]/10">
            <!-- Section Header -->
            <div class="max-w-2xl mb-12 sm:mb-14">
                <div class="inline-flex items-center gap-2.5 px-3 py-1 rounded-full bg-[#001F18]/85 border border-[#FAEFDB]/15 text-[11px] font-mono font-bold text-[#BE2388] mb-3">
                    <span class="w-2 h-2 rounded-full bg-[#BE2388]"></span>
                    <span>STANDAR OPERASIONAL BERINTEGRITAS</span>
                </div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-[#FAEFDB] leading-tight">
                    Keunggulan Operasional &amp; Presisi SLA.
                </h2>
                <p class="text-sm sm:text-base text-[#FAEFDB]/80 mt-3 leading-relaxed">
                    Sistem otomatis deterministik untuk deteksi deviasi jalur, manajemen kendala logistik, dan transparansi kepatuhan waktu kirim.
                </p>
            </div>

            <!-- 3 Equal-Height Minimalist Liquid Glass Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-stretch">
                <!-- CARD 1: Deteksi Keterlambatan ETA -->
                <article class="bg-[#02332A]/75 backdrop-blur-2xl border border-[#FAEFDB]/18 rounded-[28px] p-7 shadow-[0_20px_50px_rgba(0,0,0,0.40)] flex flex-col justify-between hover:border-[#BE2388] transition-all duration-300 hover:-translate-y-1">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-[#001F18] border border-[#FAEFDB]/15 flex items-center justify-center text-amber-400 mb-5 shadow-xs">
                            <i class="bi bi-hourglass-split text-xl" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-xl font-bold text-[#FAEFDB]">Deteksi Keterlambatan ETA</h3>
                        <p class="text-xs sm:text-sm font-normal text-[#FAEFDB]/75 mt-2.5 leading-relaxed">
                            Algoritma prediktif menganalisis cuaca maritim dan deviasi rute untuk mendeteksi potensi keterlambatan sebelum kapal merapat.
                        </p>
                    </div>

                    <div class="mt-8 pt-5 border-t border-[#FAEFDB]/12 flex items-center justify-between text-xs font-mono">
                        <span class="text-[#FAEFDB]/65">Deviasi Ambang</span>
                        <span class="font-bold text-amber-400 bg-amber-400/10 px-2.5 py-1 rounded-lg border border-amber-400/25">
                            &gt; 6 Jam (Auto Alert)
                        </span>
                    </div>
                </article>

                <!-- CARD 2: Penanganan Insiden Proaktif -->
                <article class="bg-[#02332A]/75 backdrop-blur-2xl border border-[#FAEFDB]/18 rounded-[28px] p-7 shadow-[0_20px_50px_rgba(0,0,0,0.40)] flex flex-col justify-between hover:border-[#BE2388] transition-all duration-300 hover:-translate-y-1">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-[#001F18] border border-[#FAEFDB]/15 flex items-center justify-center text-rose-400 mb-5 shadow-xs">
                            <i class="bi bi-shield-exclamation text-xl" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-xl font-bold text-[#FAEFDB]">Penanganan Insiden Proaktif</h3>
                        <p class="text-xs sm:text-sm font-normal text-[#FAEFDB]/75 mt-2.5 leading-relaxed">
                            Pencatatan insiden Customs Hold, Weather Alert, dan Port Congestion dengan siklus resolusi yang teraudit transparan.
                        </p>
                    </div>

                    <!-- Clean 3-Step Lifecycle Flow -->
                    <div class="mt-8 pt-5 border-t border-[#FAEFDB]/12 flex items-center justify-between text-[11px] font-mono font-bold text-[#FAEFDB]/80">
                        <span class="text-rose-400">OPEN</span>
                        <i class="bi bi-arrow-right text-[#FAEFDB]/40" aria-hidden="true"></i>
                        <span class="text-amber-400">INVESTIGATE</span>
                        <i class="bi bi-arrow-right text-[#FAEFDB]/40" aria-hidden="true"></i>
                        <span class="text-emerald-400">RESOLVED</span>
                    </div>
                </article>

                <!-- CARD 3: Ketepatan Jadwal Armada (SLA Rate) -->
                <article class="bg-[#02332A]/75 backdrop-blur-2xl border border-[#FAEFDB]/18 rounded-[28px] p-7 shadow-[0_20px_50px_rgba(0,0,0,0.40)] flex flex-col justify-between hover:border-[#BE2388] transition-all duration-300 hover:-translate-y-1">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-[#001F18] border border-[#FAEFDB]/15 flex items-center justify-center text-[#BE2388] mb-5 shadow-xs">
                            <i class="bi bi-shield-check text-xl" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-xl font-bold text-[#FAEFDB]">Ketepatan Jadwal Armada</h3>
                        <p class="text-xs sm:text-sm font-normal text-[#FAEFDB]/75 mt-2.5 leading-relaxed">
                            Persentase pengantaran kargo tepat waktu dihitung langsung dari histori database pengiriman aktual.
                        </p>
                    </div>

                    <!-- Minimalist Circular Metric Gauge -->
                    <div class="mt-6 flex items-center justify-between pt-4 border-t border-[#FAEFDB]/12">
                        <div class="flex items-center gap-3.5">
                            <div class="relative w-16 h-16 flex items-center justify-center shrink-0">
                                <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                                    <circle cx="50" cy="50" r="40" fill="none" stroke="rgba(250,239,219,0.12)" stroke-width="8" />
                                    <circle
                                        cx="50"
                                        cy="50"
                                        r="40"
                                        fill="none"
                                        stroke="#BE2388"
                                        stroke-width="8"
                                        stroke-linecap="round"
                                        :stroke-dasharray="gaugeCircumference"
                                        :stroke-dashoffset="gaugeOffset"
                                    />
                                </svg>
                                <span class="absolute font-mono text-xs font-extrabold text-[#FAEFDB]">
                                    {{ stats.on_time_rate }}%
                                </span>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-[#FAEFDB] block">SLA On-Time</span>
                                <span class="text-[10px] font-mono text-emerald-400 font-semibold">Memenuhi Standar</span>
                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </section>
    </div>
</template>

<style>
/* Leaflet Ocean styling matching 3D Globe (#001F18 ocean + #0A3D32 land) */
.bento-world-map .leaflet-container {
    background: linear-gradient(180deg, #02332A 0%, #001F18 100%) !important;
}
.bento-world-map .leaflet-tile-pane {
    filter: brightness(0.8) sepia(1) hue-rotate(120deg) saturate(2.5);
}
.bento-world-map .leaflet-bar a {
    background-color: #001F18 !important;
    border-color: rgba(250, 239, 219, 0.2) !important;
    color: #FAEFDB !important;
}
.bento-world-map .leaflet-bar a:hover {
    background-color: #BE2388 !important;
    color: #FFFFFF !important;
}
</style>
