<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
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
});

const mapContainer = ref(null);
let map = null;
let layerGroup = null;

const hasValidCoordinates = ref(true);

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
        return;
    }

    hasValidCoordinates.value = true;

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

    waypoints.forEach((wp) => {
        const point = [wp.lat, wp.lng];
        latLngs.push(point);

        let iconHtml = '';
        if (wp.type === 'ORIGIN') {
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
            iconSize: [32, 32],
            iconAnchor: [16, 16],
            popupAnchor: [0, -18],
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
                    }">${wp.type}</span>
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
        L.polyline(latLngs, {
            color: '#265D7A',
            weight: 6,
            opacity: 0.45,
            smoothFactor: 1,
        }).addTo(layerGroup);

        L.polyline(latLngs, {
            color: '#67C7C6',
            weight: 3,
            opacity: 0.95,
            smoothFactor: 1,
        }).addTo(layerGroup);

        L.polyline(latLngs, {
            color: '#A7E4DB',
            weight: 1.5,
            opacity: 0.85,
            dashArray: '6, 12',
        }).addTo(layerGroup);

        map.fitBounds(L.polyline(latLngs).getBounds(), {
            padding: [40, 40],
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
            map.fitBounds(group.getBounds(), { padding: [40, 40], maxZoom: 12 });
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
    <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl overflow-hidden shadow-[0_16px_40px_rgba(19,41,61,0.08)] mb-6">
        <!-- Map Header Toolbar -->
        <div class="p-4 border-b border-[#265D7A]/15 flex flex-wrap items-center justify-between gap-3 bg-white/50">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-[#EAF6F4] border border-[#265D7A]/20 flex items-center justify-center text-[#265D7A]">
                    <i class="bi bi-map-fill text-xs"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-[#13293D] uppercase tracking-wider">Visualisasi Rute &amp; Geospasial (Dark Canvas)</h3>
                    <p class="text-[11px] font-normal text-[#265D7A]">Pelacakan interaktif posisi kapal dan checkpoint cuaca</p>
                </div>
            </div>

            <!-- Legend & Reset Button -->
            <div class="flex items-center gap-2 flex-wrap">
                <div class="hidden sm:flex items-center gap-3 text-[11px] text-[#13293D] px-3.5 py-1.5 bg-[#EAF6F4] rounded-xl border border-[#265D7A]/15 font-mono">
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
