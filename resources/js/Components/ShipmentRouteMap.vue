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
                <div class="relative flex items-center justify-center w-8 h-8 rounded-full bg-emerald-500 text-slate-950 font-bold shadow-[0_0_14px_#10b981] border-2 border-slate-950">
                    <i class="bi bi-geo-alt-fill text-xs"></i>
                </div>
            `;
        } else if (wp.type === 'DESTINATION') {
            iconHtml = `
                <div class="relative flex items-center justify-center w-8 h-8 rounded-full bg-rose-500 text-white font-bold shadow-[0_0_14px_#f43f5e] border-2 border-slate-950">
                    <i class="bi bi-flag-fill text-xs"></i>
                </div>
            `;
        } else {
            iconHtml = `
                <div class="relative flex items-center justify-center w-7 h-7 rounded-full bg-cyan-400 text-slate-950 font-black font-mono shadow-[0_0_12px_#22d3ee] border-2 border-slate-950">
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

        // Weather section in popup
        let weatherHtml = '';
        if (wp.weather) {
            weatherHtml = `
                <div class="mt-2.5 p-2 bg-slate-950/70 rounded-xl border border-white/10 text-[11px]">
                    <div class="font-bold text-cyan-300 flex items-center gap-1.5">
                        <i class="bi bi-cloud-sun text-amber-400"></i>
                        <span>${wp.weather.condition || 'Kondisi Cuaca'} &bull; ${wp.weather.temperature ?? 'N/A'}°C</span>
                    </div>
                    <div class="text-slate-400 text-[10px] mt-0.5 font-mono">
                        Angin: ${wp.weather.wind_speed ?? 'N/A'} km/h &bull; Kelembapan: ${wp.weather.humidity ?? 'N/A'}%
                    </div>
                </div>
            `;
        }

        const popupContent = `
            <div class="font-sans text-xs min-w-[210px] p-1.5 text-slate-200">
                <div class="flex items-center justify-between gap-2 border-b border-white/10 pb-2 mb-2">
                    <span class="font-mono font-extrabold text-[10px] px-2 py-0.5 rounded ${
                        wp.type === 'ORIGIN' ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' :
                        wp.type === 'DESTINATION' ? 'bg-rose-500/20 text-rose-300 border border-rose-500/40' : 'bg-cyan-500/20 text-cyan-300 border border-cyan-500/40'
                    }">${wp.type}</span>
                    <span class="text-[10px] text-slate-400 font-mono">${wp.lat.toFixed(4)}, ${wp.lng.toFixed(4)}</span>
                </div>
                <div class="font-extrabold text-white text-sm tracking-tight">${wp.name}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">${wp.city ? wp.city + ', ' : ''}${wp.country || ''}</div>
                ${wp.description ? `<p class="mt-1.5 text-slate-300 italic text-[11px] leading-relaxed">${wp.description}</p>` : ''}
                ${weatherHtml}
                <div class="mt-2.5 pt-1.5 border-t border-white/10 text-[10px] text-slate-400 font-mono">
                    ${wp.time ? new Date(wp.time).toLocaleString() : '-'}
                </div>
            </div>
        `;

        const marker = L.marker(point, { icon: customIcon }).bindPopup(popupContent);
        layerGroup.addLayer(marker);
    });

    // Draw route polyline with Glowing Cyber Maritime style
    if (latLngs.length > 1) {
        // Glowing cyan base underlay
        L.polyline(latLngs, {
            color: '#22D3EE',
            weight: 5,
            opacity: 0.35,
            smoothFactor: 1,
        }).addTo(layerGroup);

        // Sharp solid cyan route line
        L.polyline(latLngs, {
            color: '#06B6D4',
            weight: 3,
            opacity: 0.95,
            smoothFactor: 1,
        }).addTo(layerGroup);

        // Dashed animated pulse effect
        L.polyline(latLngs, {
            color: '#FFFFFF',
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
    <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl overflow-hidden shadow-xl mb-6">
        <!-- Map Header Toolbar -->
        <div class="p-4 border-b border-white/10 flex flex-wrap items-center justify-between gap-3 bg-slate-950/60">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-cyan-500/15 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
                    <i class="bi bi-map-fill text-xs"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider">Visualisasi Rute & Geospasial (Dark Map)</h3>
                    <p class="text-[11px] text-slate-400">Pelacakan interaktif posisi kapal dan checkpoint cuaca</p>
                </div>
            </div>

            <!-- Legend & Reset Button -->
            <div class="flex items-center gap-2 flex-wrap">
                <div class="hidden sm:flex items-center gap-3 text-[11px] text-slate-300 px-3.5 py-1.5 bg-slate-900/80 rounded-xl border border-white/10">
                    <span class="inline-flex items-center gap-1.5 font-semibold">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-[0_0_8px_#34d399] inline-block"></span> Asal (Origin)
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-semibold">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 shadow-[0_0_8px_#22d3ee] inline-block"></span> Checkpoint
                    </span>
                    <span class="inline-flex items-center gap-1.5 font-semibold">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-400 shadow-[0_0_8px_#fb7185] inline-block"></span> Tujuan (Destination)
                    </span>
                </div>

                <button
                    @click="resetBounds"
                    type="button"
                    title="Pusatkan Rute"
                    class="px-3 py-1.5 text-xs font-bold border border-white/15 bg-white/5 hover:bg-white/10 rounded-xl text-white inline-flex items-center gap-1.5 transition cursor-pointer"
                >
                    <i class="bi bi-arrows-fullscreen text-xs"></i>
                    <span>Pusatkan</span>
                </button>
            </div>
        </div>

        <!-- Map Container -->
        <div v-if="hasValidCoordinates" class="relative w-full" :style="{ height }">
            <div ref="mapContainer" class="w-full h-full z-10"></div>
        </div>

        <!-- Fallback if coordinates missing -->
        <div v-else class="p-8 text-center bg-slate-950/60 text-xs text-slate-400">
            <i class="bi bi-geo-alt-slash text-2xl text-slate-500 mb-2 block"></i>
            <p class="font-bold text-white">Koordinat Geospasial Belum Lengkap</p>
            <p class="text-[11px] mt-1 max-w-sm mx-auto">
                Lokasi asal atau tujuan belum memiliki titik koordinat (latitude & longitude) yang valid. Perbarui data master lokasi untuk menampilkan peta rute.
            </p>
        </div>
    </div>
</template>

<style>
/* Leaflet Dark Matter styling overrides */
.leaflet-popup-content-wrapper {
    background: #0B132B !important;
    border: 1px solid rgba(255, 255, 255, 0.16) !important;
    border-radius: 18px !important;
    box-shadow: 0 16px 36px rgba(0, 0, 0, 0.6) !important;
    color: #F8FAFC !important;
}
.leaflet-popup-tip {
    background: #0B132B !important;
    border: 1px solid rgba(255, 255, 255, 0.16) !important;
}
.custom-leaflet-pin {
    background: transparent !important;
    border: none !important;
}
.leaflet-bar a {
    background-color: #0F172A !important;
    border-color: rgba(255, 255, 255, 0.15) !important;
    color: #F8FAFC !important;
}
.leaflet-bar a:hover {
    background-color: #1E293B !important;
    color: #22D3EE !important;
}
</style>
