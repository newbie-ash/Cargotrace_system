<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
    shipment: {
        type: Object,
        required: true,
    },
    height: {
        type: String,
        default: '420px',
    },
    liveMode: {
        type: Boolean,
        default: false,
    },
    compact: {
        type: Boolean,
        default: false,
    },
});

const mapContainer = ref(null);
let map = null;
let layerGroup = null;

const hasValidCoordinates = ref(true);
const activeWaypoint = ref(null);

const initMap = () => {
    if (!mapContainer.value) return;

    if (map) {
        map.remove();
        map = null;
    }

    const origin = props.shipment.origin_location;
    const destination = props.shipment.destination_location;
    const events = (props.shipment.tracking_events || []).slice().sort((a, b) => new Date(a.occurred_at) - new Date(b.occurred_at));

    const waypoints = [];

    if (origin && origin.latitude && origin.longitude) {
        waypoints.push({
            type: 'ORIGIN',
            name: origin.name || 'Origin Port',
            city: origin.city,
            country: origin.country,
            lat: parseFloat(origin.latitude),
            lng: parseFloat(origin.longitude),
            time: props.shipment.expected_departure,
            status: 'ORIGIN',
            weather: null,
            description: 'Titik keberangkatan kargo',
        });
    }

    events.forEach((evt, idx) => {
        const lat = evt.latitude ? parseFloat(evt.latitude) : (evt.location?.latitude ? parseFloat(evt.location.latitude) : null);
        const lng = evt.longitude ? parseFloat(evt.longitude) : (evt.location?.longitude ? parseFloat(evt.location.longitude) : null);

        if (lat !== null && lng !== null && !isNaN(lat) && !isNaN(lng)) {
            waypoints.push({
                type: 'CHECKPOINT',
                sequence: idx + 1,
                name: evt.location?.name || `Checkpoint #${idx + 1}`,
                city: evt.location?.city,
                country: evt.location?.country,
                lat,
                lng,
                time: evt.occurred_at,
                status: evt.status,
                weather: evt.weather_snapshot,
                description: evt.description,
            });
        }
    });

    if (destination && destination.latitude && destination.longitude) {
        waypoints.push({
            type: 'DESTINATION',
            name: destination.name || 'Destination Port',
            city: destination.city,
            country: destination.country,
            lat: parseFloat(destination.latitude),
            lng: parseFloat(destination.longitude),
            time: props.shipment.estimated_arrival || props.shipment.expected_arrival,
            status: 'DESTINATION',
            weather: null,
            description: 'Pelabuhan destinasi akhir',
        });
    }

    if (waypoints.length === 0) {
        hasValidCoordinates.value = false;
        activeWaypoint.value = null;
        return;
    }

    hasValidCoordinates.value = true;

    // Determine active position index
    const status = props.shipment.current_status;
    let activeIdx = 0;
    if (status === 'DELIVERED') {
        activeIdx = waypoints.length - 1;
    } else if (events.length > 0) {
        // Last recorded checkpoint
        activeIdx = waypoints.filter(w => w.type !== 'DESTINATION').length - 1;
    } else {
        activeIdx = 0;
    }
    activeWaypoint.value = waypoints[activeIdx] || waypoints[0];

    map = L.map(mapContainer.value, {
        zoomControl: false,
        attributionControl: true,
        scrollWheelZoom: false,
    });

    L.control.zoom({ position: 'topright' }).addTo(map);

    // Esri World Dark Gray Canvas (No API Key Required)
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

    const latLngs = [];

    waypoints.forEach((wp, idx) => {
        const point = [wp.lat, wp.lng];
        latLngs.push(point);

        const isCurrentActive = idx === activeIdx && status !== 'DELIVERED' && status !== 'CANCELLED';

        let iconHtml = '';
        if (isCurrentActive) {
            iconHtml = `
                <div class="relative flex items-center justify-center w-9 h-9">
                    <span class="absolute inline-flex h-full w-full rounded-full bg-[#67C7C6] opacity-75 animate-ping"></span>
                    <div class="relative flex items-center justify-center w-8 h-8 rounded-full bg-[#13293D] text-[#67C7C6] border-2 border-[#67C7C6] shadow-[0_0_18px_#67C7C6]">
                        <i class="bi bi-broadcast text-xs font-bold"></i>
                    </div>
                </div>
            `;
        } else if (wp.type === 'ORIGIN') {
            iconHtml = `
                <div class="relative flex items-center justify-center w-8 h-8 rounded-full bg-emerald-500 text-slate-950 font-bold shadow-[0_0_14px_#10b981] border-2 border-[#13293D]">
                    <i class="bi bi-geo-alt-fill text-xs"></i>
                </div>
            `;
        } else if (wp.type === 'DESTINATION') {
            iconHtml = `
                <div class="relative flex items-center justify-center w-8 h-8 rounded-full bg-rose-500 text-white font-bold shadow-[0_0_14px_#f43f5e] border-2 border-[#13293D]">
                    <i class="bi bi-flag-fill text-xs"></i>
                </div>
            `;
        } else {
            iconHtml = `
                <div class="relative flex items-center justify-center w-7 h-7 rounded-full bg-[#67C7C6] text-[#13293D] font-black font-mono shadow-[0_0_12px_#67C7C6] border-2 border-[#13293D]">
                    <span class="text-[10px]">${wp.sequence || '•'}</span>
                </div>
            `;
        }

        const customIcon = L.divIcon({
            html: iconHtml,
            className: 'custom-leaflet-pin',
            iconSize: [36, 36],
            iconAnchor: [18, 18],
            popupAnchor: [0, -20],
        });

        let weatherHtml = '';
        if (wp.weather) {
            weatherHtml = `
                <div class="mt-2.5 p-2 bg-[#13293D]/90 rounded-xl border border-white/15 text-[11px]">
                    <div class="font-bold text-[#A7E4DB] flex items-center gap-1.5">
                        <i class="bi bi-cloud-sun text-amber-400"></i>
                        <span>${wp.weather.condition || 'Kondisi Cuaca'} &bull; ${wp.weather.temperature ?? 'N/A'}°C</span>
                    </div>
                    <div class="text-[#D9F2EE] text-[10px] mt-0.5 font-mono">
                        Angin: ${wp.weather.wind_speed ?? 'N/A'} km/h &bull; Kelembapan: ${wp.weather.humidity ?? 'N/A'}%
                    </div>
                </div>
            `;
        }

        const popupContent = `
            <div class="font-sans text-xs min-w-[210px] p-1.5 text-[#EAF6F4]">
                <div class="flex items-center justify-between gap-2 border-b border-white/15 pb-2 mb-2">
                    <span class="font-mono font-extrabold text-[10px] px-2 py-0.5 rounded ${
                        wp.type === 'ORIGIN' ? 'bg-emerald-500/25 text-[#A7E4DB] border border-emerald-400/40' :
                        wp.type === 'DESTINATION' ? 'bg-rose-500/25 text-rose-200 border border-rose-400/40' : 'bg-[#265D7A] text-[#A7E4DB] border border-[#67C7C6]/40'
                    }">${isCurrentActive ? 'POSISI AKTIF' : wp.type}</span>
                    <span class="text-[10px] text-[#D9F2EE] font-mono">${wp.lat.toFixed(4)}, ${wp.lng.toFixed(4)}</span>
                </div>
                <div class="font-extrabold text-white text-sm tracking-tight">${wp.name}</div>
                <div class="text-[11px] text-[#D9F2EE] mt-0.5">${wp.city ? wp.city + ', ' : ''}${wp.country || ''}</div>
                ${wp.description ? `<p class="mt-1.5 text-[#EAF6F4] font-light text-[11px] leading-relaxed">${wp.description}</p>` : ''}
                ${weatherHtml}
                <div class="mt-2.5 pt-1.5 border-t border-white/15 text-[10px] text-[#A7E4DB] font-mono">
                    ${wp.time ? new Date(wp.time).toLocaleString() : '-'}
                </div>
            </div>
        `;

        const marker = L.marker(point, { icon: customIcon }).bindPopup(popupContent);
        layerGroup.addLayer(marker);
    });

    if (latLngs.length > 1) {
        // Split Completed vs Projected Path
        const completedSlice = latLngs.slice(0, activeIdx + 1);
        const projectedSlice = latLngs.slice(activeIdx);

        // Completed segment (Solid Glowing Aqua)
        if (completedSlice.length > 1) {
            L.polyline(completedSlice, {
                color: '#265D7A',
                weight: 6,
                opacity: 0.45,
                smoothFactor: 1,
            }).addTo(layerGroup);

            L.polyline(completedSlice, {
                color: '#67C7C6',
                weight: 3.5,
                opacity: 0.95,
                smoothFactor: 1,
            }).addTo(layerGroup);
        }

        // Projected segment to destination (Dashed Light Teal)
        if (projectedSlice.length > 1 && status !== 'DELIVERED') {
            L.polyline(projectedSlice, {
                color: '#A7E4DB',
                weight: 2.5,
                opacity: 0.85,
                dashArray: '6, 10',
                smoothFactor: 1,
            }).addTo(layerGroup);
        } else if (completedSlice.length <= 1 && projectedSlice.length <= 1) {
            // Fallback direct line
            L.polyline(latLngs, {
                color: '#67C7C6',
                weight: 3,
                opacity: 0.9,
                dashArray: '6, 12',
            }).addTo(layerGroup);
        }

        map.fitBounds(L.polyline(latLngs).getBounds(), {
            padding: [45, 45],
            maxZoom: 12,
        });
    } else if (latLngs.length === 1) {
        map.setView(latLngs[0], 6);
    }
};

const resetBounds = () => {
    if (map && layerGroup) {
        const layers = layerGroup.getLayers();
        const markers = layers.filter(l => l.getLatLng);
        if (markers.length > 0) {
            const group = L.featureGroup(markers);
            map.fitBounds(group.getBounds(), { padding: [45, 45], maxZoom: 12 });
        }
    }
};

onMounted(() => {
    nextTick(() => {
        initMap();
    });
});

watch(() => props.shipment, () => {
    nextTick(() => {
        initMap();
    });
}, { deep: true });

onUnmounted(() => {
    if (map) {
        map.remove();
        map = null;
    }
});
</script>

<template>
    <div class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden shadow-xs mb-6 relative">
        <!-- Map Header Toolbar -->
        <div class="p-3.5 sm:p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-white">
            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                <div class="w-8 h-8 rounded-xl bg-[#EAF6F4] border border-[#265D7A]/20 flex items-center justify-center text-[#265D7A] shrink-0">
                    <i :class="['bi text-xs', liveMode ? 'bi-broadcast-pin text-emerald-600' : 'bi-map-fill']"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="text-xs font-bold text-[#13293D] uppercase tracking-wider truncate">
                            {{ liveMode ? 'Pemantauan Trek Realtime' : 'Visualisasi Rute & Geospasial' }}
                        </h3>
                        <span v-if="liveMode" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-[10px] font-mono font-bold text-emerald-700 shrink-0">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>LIVE</span>
                        </span>
                    </div>
                    <p class="text-[11px] font-normal text-[#265D7A] truncate">
                        {{ shipment?.shipment_number ? `${shipment.shipment_number} &bull; ${shipment.cargo_type || 'General Cargo'}` : 'Pelacakan posisi kargo intercontinental' }}
                    </p>
                </div>
            </div>

            <!-- Legend & Reset Button -->
            <div class="flex items-center gap-2 flex-wrap">
                <div class="hidden md:flex items-center gap-3 text-[11px] text-[#13293D] px-3.5 py-1.5 bg-[#EAF6F4] rounded-xl border border-[#265D7A]/15 font-mono">
                    <span class="inline-flex items-center gap-1.5 font-semibold">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> Asal
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-semibold">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#67C7C6] inline-block"></span> Checkpoint
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-semibold">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span> Tujuan
                    </span>
                </div>

                <button
                    @click="resetBounds"
                    type="button"
                    title="Pusatkan Rute"
                    class="px-3.5 py-1.5 text-xs font-semibold bg-[#265D7A] hover:bg-[#13293D] rounded-full text-white inline-flex items-center gap-1.5 transition cursor-pointer shadow-sm"
                >
                    <i class="bi bi-arrows-fullscreen text-xs"></i>
                    <span>Pusatkan</span>
                </button>
            </div>
        </div>

        <!-- Map Container -->
        <div v-if="hasValidCoordinates" class="relative w-full ocean-map-container" :style="{ height }">
            <div ref="mapContainer" class="w-full h-full z-10"></div>

            <!-- Floating Telemetry HUD Overlay (Yellow Express & Biswas inspired) -->
            <div
                v-if="liveMode && shipment && activeWaypoint"
                class="absolute bottom-3 inset-x-3 sm:inset-x-5 z-20 pointer-events-auto bg-[#13293D]/85 backdrop-blur-xl border border-white/20 rounded-2xl p-3 sm:p-3.5 text-white shadow-xl flex flex-wrap items-center justify-between gap-3"
            >
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-[#67C7C6]/20 border border-[#67C7C6]/40 text-[#67C7C6] flex items-center justify-center shrink-0">
                        <i class="bi bi-geo-alt-fill text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] font-mono text-[#A7E4DB] uppercase tracking-wider font-bold flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span>
                            <span>Posisi Terkini &bull; {{ activeWaypoint.type }}</span>
                        </div>
                        <div class="text-xs sm:text-sm font-extrabold truncate text-white">
                            {{ activeWaypoint.name }}
                        </div>
                        <div class="text-[10px] text-[#D9F2EE] truncate font-mono">
                            Menuju: <span class="text-white font-semibold">{{ shipment.destination_location?.name || shipment.destination_location?.city }}</span> ({{ shipment.destination_location?.code }})
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 sm:gap-3 shrink-0 ml-auto">
                    <div class="text-right hidden sm:block">
                        <div class="text-[10px] font-mono text-[#A7E4DB] uppercase font-bold">Estimasi Tiba</div>
                        <div class="text-xs font-bold font-mono text-white">
                            {{ (shipment.estimated_arrival || shipment.expected_arrival) ? new Date(shipment.estimated_arrival || shipment.expected_arrival).toLocaleDateString([], { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' }) : '-' }}
                        </div>
                    </div>
                    <Link
                        :href="`/shipments/${shipment.id}`"
                        class="px-3.5 py-1.5 rounded-xl bg-gradient-to-r from-[#265D7A] to-[#67C7C6] hover:brightness-110 text-[#13293D] font-bold text-xs inline-flex items-center gap-1.5 transition shadow-sm cursor-pointer"
                    >
                        <span>Lihat Detail</span>
                        <i class="bi bi-arrow-right"></i>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Fallback if coordinates missing -->
        <div v-else class="p-8 text-center bg-white/50 text-xs text-[#265D7A]">
            <i class="bi bi-geo-alt-slash text-2xl text-[#67C7C6] mb-2 block"></i>
            <p class="font-bold text-[#13293D]">Koordinat Geospasial Belum Lengkap</p>
            <p class="text-[11px] mt-1 max-w-sm mx-auto">
                Lokasi asal atau tujuan belum memiliki titik koordinat (latitude &amp; longitude) yang valid. Perbarui data master lokasi untuk menampilkan peta rute.
            </p>
        </div>
    </div>
</template>

<style>
/* Leaflet Ocean Tint styling matching 3D Globe (#13293D ocean + #265D7A land) */
.ocean-map-container .leaflet-container {
    background: linear-gradient(180deg, #1A3A54 0%, #13293D 50%, #0C1B28 100%) !important;
}
.ocean-map-container .leaflet-tile-pane {
    filter: brightness(0.9) sepia(1) hue-rotate(170deg) saturate(3.2);
}

/* Leaflet Dark Canvas styling overrides */
.leaflet-popup-content-wrapper {
    background: #13293D !important;
    border: 1px solid rgba(167, 228, 219, 0.35) !important;
    border-radius: 18px !important;
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.5) !important;
    color: #F8FAFC !important;
}
.leaflet-popup-tip {
    background: #13293D !important;
    border: 1px solid rgba(167, 228, 219, 0.35) !important;
}
.custom-leaflet-pin {
    background: transparent !important;
    border: none !important;
}
.leaflet-bar a {
    background-color: #13293D !important;
    border-color: rgba(255, 255, 255, 0.2) !important;
    color: #F8FAFC !important;
}
.leaflet-bar a:hover {
    background-color: #265D7A !important;
    color: #FFFFFF !important;
}
</style>

<style>
/* Leaflet Ocean Tint styling matching 3D Globe (#13293D ocean + #265D7A land) */
.ocean-map-container .leaflet-container {
    background: linear-gradient(180deg, #1A3A54 0%, #13293D 50%, #0C1B28 100%) !important;
}
.ocean-map-container .leaflet-tile-pane {
    filter: brightness(0.9) sepia(1) hue-rotate(170deg) saturate(3.2);
}

/* Leaflet Dark Canvas styling overrides */
.leaflet-popup-content-wrapper {
    background: #13293D !important;
    border: 1px solid rgba(167, 228, 219, 0.35) !important;
    border-radius: 18px !important;
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.5) !important;
    color: #F8FAFC !important;
}
.leaflet-popup-tip {
    background: #13293D !important;
    border: 1px solid rgba(167, 228, 219, 0.35) !important;
}
.custom-leaflet-pin {
    background: transparent !important;
    border: none !important;
}
.leaflet-bar a {
    background-color: #13293D !important;
    border-color: rgba(255, 255, 255, 0.2) !important;
    color: #F8FAFC !important;
}
.leaflet-bar a:hover {
    background-color: #265D7A !important;
    color: #FFFFFF !important;
}
</style>
