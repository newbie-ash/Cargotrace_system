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

    // Origin
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

    // Checkpoints
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

    // Destination
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

    // Initialize Leaflet Map
    map = L.map(mapContainer.value, {
        zoomControl: false,
        attributionControl: true,
        scrollWheelZoom: false,
    });

    // Custom Zoom Control top-right
    L.control.zoom({ position: 'topright' }).addTo(map);

    // CartoDB Positron / OSM Tiles
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19,
    }).addTo(map);

    layerGroup = L.layerGroup().addTo(map);

    const latLngs = [];

    waypoints.forEach((wp) => {
        const point = [wp.lat, wp.lng];
        latLngs.push(point);

        let iconHtml = '';
        if (wp.type === 'ORIGIN') {
            iconHtml = `
                <div class="relative flex items-center justify-center w-8 h-8 rounded-full bg-emerald-600 text-white shadow-lg border-2 border-white ring-2 ring-emerald-300">
                    <i class="bi bi-geo-alt-fill text-xs"></i>
                </div>
            `;
        } else if (wp.type === 'DESTINATION') {
            iconHtml = `
                <div class="relative flex items-center justify-center w-8 h-8 rounded-full bg-rose-600 text-white shadow-lg border-2 border-white ring-2 ring-rose-300">
                    <i class="bi bi-flag-fill text-xs"></i>
                </div>
            `;
        } else {
            iconHtml = `
                <div class="relative flex items-center justify-center w-7 h-7 rounded-full bg-blue-600 text-white shadow-md border-2 border-white ring-2 ring-blue-300">
                    <span class="text-[10px] font-bold font-mono">${wp.sequence || '•'}</span>
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

        // Weather section in popup
        let weatherHtml = '';
        if (wp.weather) {
            weatherHtml = `
                <div class="mt-2 p-2 bg-[#F6F3EC] rounded-lg border border-[#E5E0D5] text-[11px]">
                    <div class="font-bold text-[#1A1A1C] flex items-center gap-1.5">
                        <i class="bi bi-cloud-sun text-amber-600"></i>
                        <span>${wp.weather.condition || 'Kondisi Cuaca'} &bull; ${wp.weather.temperature ?? 'N/A'}°C</span>
                    </div>
                    <div class="text-[#706E6B] text-[10px] mt-0.5">
                        Angin: ${wp.weather.wind_speed ?? 'N/A'} km/h &bull; Kelembapan: ${wp.weather.humidity ?? 'N/A'}%
                    </div>
                </div>
            `;
        }

        const popupContent = `
            <div class="font-sans text-xs min-w-[200px] p-1">
                <div class="flex items-center justify-between gap-2 border-b border-[#E5E0D5] pb-1.5 mb-1.5">
                    <span class="font-bold font-mono text-[10px] px-1.5 py-0.5 rounded ${
                        wp.type === 'ORIGIN' ? 'bg-emerald-100 text-emerald-800' :
                        wp.type === 'DESTINATION' ? 'bg-rose-100 text-rose-800' : 'bg-blue-100 text-blue-800'
                    }">${wp.type}</span>
                    <span class="text-[10px] text-[#706E6B] font-mono">${wp.lat.toFixed(4)}, ${wp.lng.toFixed(4)}</span>
                </div>
                <div class="font-bold text-[#1A1A1C] text-sm">${wp.name}</div>
                <div class="text-[11px] text-[#706E6B]">${wp.city ? wp.city + ', ' : ''}${wp.country || ''}</div>
                ${wp.description ? `<p class="mt-1 text-[#2C2C2E] italic text-[11px]">${wp.description}</p>` : ''}
                ${weatherHtml}
                <div class="mt-2 pt-1 border-t border-[#E5E0D5] text-[10px] text-[#706E6B]">
                    ${wp.time ? new Date(wp.time).toLocaleString() : '-'}
                </div>
            </div>
        `;

        const marker = L.marker(point, { icon: customIcon }).bindPopup(popupContent);
        layerGroup.addLayer(marker);
    });

    // Draw route polyline
    if (latLngs.length > 1) {
        // Main route line
        const polyline = L.polyline(latLngs, {
            color: '#1A1A1C',
            weight: 3.5,
            opacity: 0.85,
            smoothFactor: 1,
        }).addTo(layerGroup);

        // Dashed glowing underlay
        L.polyline(latLngs, {
            color: '#2563EB',
            weight: 2,
            opacity: 0.9,
            dashArray: '8, 8',
        }).addTo(layerGroup);

        map.fitBounds(polyline.getBounds(), {
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
    <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl overflow-hidden shadow-xs mb-6">
        <!-- Map Header Toolbar -->
        <div class="p-4 border-b border-[#E5E0D5] flex flex-wrap items-center justify-between gap-3 bg-white/90">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-[#F6F3EC] border border-[#D8D2C4] flex items-center justify-center text-[#1A1A1C]">
                    <i class="bi bi-map-fill text-xs"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-[#1A1A1C] uppercase tracking-wider">Visualisasi Rute & Geospasial</h3>
                    <p class="text-[11px] text-[#706E6B]">Pelacakan interaktif posisi kapal dan checkpoint cuaca</p>
                </div>
            </div>

            <!-- Legend & Reset Button -->
            <div class="flex items-center gap-2 flex-wrap">
                <div class="hidden sm:flex items-center gap-3 text-[11px] text-[#706E6B] px-3 py-1 bg-[#F6F3EC] rounded-xl border border-[#E5E0D5]">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 inline-block"></span> Asal (Origin)
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600 inline-block"></span> Checkpoint
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-600 inline-block"></span> Tujuan (Destination)
                    </span>
                </div>

                <button
                    @click="resetBounds"
                    type="button"
                    title="Pusatkan Rute"
                    class="px-2.5 py-1 text-xs font-medium border border-[#D8D2C4] rounded-xl hover:bg-[#F2EFE9] text-[#1A1A1C] inline-flex items-center gap-1 transition"
                >
                    <i class="bi bi-arrows-fullscreen"></i>
                    <span>Pusatkan</span>
                </button>
            </div>
        </div>

        <!-- Map Container -->
        <div v-if="hasValidCoordinates" class="relative w-full" :style="{ height }">
            <div ref="mapContainer" class="w-full h-full z-10"></div>
        </div>

        <!-- Fallback if coordinates missing -->
        <div v-else class="p-8 text-center bg-[#F6F3EC]/50 text-xs text-[#706E6B]">
            <i class="bi bi-geo-alt-slash text-2xl text-[#8E8B85] mb-2 block"></i>
            <p class="font-semibold text-[#1A1A1C]">Koordinat Geospasial Belum Lengkap</p>
            <p class="text-[11px] mt-1 max-w-sm mx-auto">
                Lokasi asal atau tujuan belum memiliki titik koordinat (latitude & longitude) yang valid. Perbarui data master lokasi untuk menampilkan peta rute.
            </p>
        </div>
    </div>
</template>

<style>
/* Leaflet Popup Warm Paper styling overrides */
.leaflet-popup-content-wrapper {
    background: #FFFFFF !important;
    border: 1px solid #E5E0D5 !important;
    border-radius: 14px !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
}
.leaflet-popup-tip {
    background: #FFFFFF !important;
    border: 1px solid #E5E0D5 !important;
}
.custom-leaflet-pin {
    background: transparent !important;
    border: none !important;
}
</style>
