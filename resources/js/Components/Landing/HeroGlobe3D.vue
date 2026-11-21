<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import Globe from 'globe.gl';
import { feature } from 'topojson-client';
import countries110m from 'world-atlas/countries-110m.json';

const props = defineProps({
    locations: {
        type: Array,
        default: () => [],
    },
});

const globeContainer = ref(null);
const isWebglSupported = ref(true);
const isReady = ref(false);

let globeInstance = null;
let resizeObserver = null;

const checkWebGL = () => {
    try {
        const canvas = document.createElement('canvas');
        return !!(
            window.WebGLRenderingContext &&
            (canvas.getContext('webgl') || canvas.getContext('experimental-webgl'))
        );
    } catch {
        return false;
    }
};

const hasCoords = (loc) =>
    loc &&
    loc.latitude !== null &&
    loc.latitude !== undefined &&
    loc.longitude !== null &&
    loc.longitude !== undefined &&
    Number.isFinite(Number(loc.latitude)) &&
    Number.isFinite(Number(loc.longitude));

// Generate dark ocean texture via offscreen canvas (Zero CORS / Zero external network dependency)
const createOceanTexture = () => {
    const canvas = document.createElement('canvas');
    canvas.width = 128;
    canvas.height = 64;
    const ctx = canvas.getContext('2d');
    if (!ctx) return '';

    const grad = ctx.createLinearGradient(0, 0, 0, 64);
    grad.addColorStop(0, '#1A3A54');
    grad.addColorStop(0.5, '#13293D');
    grad.addColorStop(1, '#0C1B28');
    ctx.fillStyle = grad;
    ctx.fillRect(0, 0, 128, 64);
    return canvas.toDataURL();
};

const buildPointsAndArcs = () => {
    const validLocs = props.locations.filter(hasCoords);

    // Format points
    const points = validLocs.map((loc) => {
        const isOrigin = loc.code === 'IDJKT' || loc.code === 'IDTPP';
        const isDest = loc.code === 'NLRTM';
        return {
            code: loc.code,
            name: loc.name,
            lat: parseFloat(loc.latitude),
            lng: parseFloat(loc.longitude),
            color: isOrigin ? '#10B981' : isDest ? '#F59E0B' : '#67C7C6',
        };
    });

    // Curate active flight/maritime arcs between real DB nodes
    const arcs = [];
    const findLoc = (code) => validLocs.find((l) => l.code === code);

    const corridor1 = ['IDJKT', 'SGSIN', 'AEDXB', 'NLRTM']
        .map(findLoc)
        .filter(Boolean);

    for (let i = 0; i < corridor1.length - 1; i++) {
        arcs.push({
            startLat: parseFloat(corridor1[i].latitude),
            startLng: parseFloat(corridor1[i].longitude),
            endLat: parseFloat(corridor1[i + 1].latitude),
            endLng: parseFloat(corridor1[i + 1].longitude),
            color: ['#10B981', '#67C7C6', '#F59E0B'],
        });
    }

    const corridor2 = ['KRPUS', 'CNSHA', 'SGSIN']
        .map(findLoc)
        .filter(Boolean);

    for (let i = 0; i < corridor2.length - 1; i++) {
        arcs.push({
            startLat: parseFloat(corridor2[i].latitude),
            startLng: parseFloat(corridor2[i].longitude),
            endLat: parseFloat(corridor2[i + 1].latitude),
            endLng: parseFloat(corridor2[i + 1].longitude),
            color: ['#A7E4DB', '#67C7C6'],
        });
    }

    // Dynamic fallback if custom seed lacks those codes
    if (!arcs.length && validLocs.length >= 2) {
        for (let i = 0; i < Math.min(validLocs.length - 1, 3); i++) {
            arcs.push({
                startLat: parseFloat(validLocs[i].latitude),
                startLng: parseFloat(validLocs[i].longitude),
                endLat: parseFloat(validLocs[i + 1].latitude),
                endLng: parseFloat(validLocs[i + 1].longitude),
                color: ['#10B981', '#67C7C6', '#F59E0B'],
            });
        }
    }

    // Pulsing radar rings at primary hubs
    const rings = [];
    const jkt = findLoc('IDJKT');
    const rtm = findLoc('NLRTM');
    if (jkt) rings.push({ lat: parseFloat(jkt.latitude), lng: parseFloat(jkt.longitude), color: '#10B981' });
    if (rtm) rings.push({ lat: parseFloat(rtm.latitude), lng: parseFloat(rtm.longitude), color: '#F59E0B' });

    return { points, arcs, rings };
};

const initGlobe = () => {
    if (!globeContainer.value || !isWebglSupported.value) return;

    if (globeInstance) {
        if (typeof globeInstance._destructor === 'function') {
            globeInstance._destructor();
        }
        globeInstance = null;
    }

    const width = globeContainer.value.clientWidth || 400;
    const height = globeContainer.value.clientHeight || 400;

    const countries = feature(countries110m, countries110m.objects.countries);
    const { points, arcs, rings } = buildPointsAndArcs();
    const oceanTexture = createOceanTexture();

    globeInstance = new Globe(globeContainer.value)
        .width(width)
        .height(height)
        .backgroundColor('rgba(0,0,0,0)')
        .globeImageUrl(oceanTexture)
        .showAtmosphere(true)
        .atmosphereColor('#67C7C6')
        .atmosphereAltitude(0.18)
        .polygonsData(countries.features)
        .polygonCapColor(() => 'rgba(38, 93, 122, 0.92)')
        .polygonSideColor(() => 'rgba(103, 199, 198, 0.22)')
        .polygonStrokeColor(() => '#67C7C6')
        .polygonAltitude(0.012)
        .pointsData(points)
        .pointColor('color')
        .pointAltitude(0.028)
        .pointRadius(0.85)
        .labelsData(points.slice(0, 6))
        .labelText('code')
        .labelSize(1.3)
        .labelDotRadius(0.35)
        .labelColor(() => '#A7E4DB')
        .labelAltitude(0.038)
        .arcsData(arcs)
        .arcColor('color')
        .arcDashLength(0.42)
        .arcDashGap(0.18)
        .arcDashAnimateTime(2000)
        .arcStroke(1.2)
        .arcAltitude(0.24)
        .ringsData(rings)
        .ringColor('color')
        .ringMaxRadius(4.5)
        .ringPropagationSpeed(1.6)
        .ringRepeatPeriod(900);

    // Initial camera centered on Indian Ocean / Asia-Europe corridor
    globeInstance.pointOfView({ lat: 16, lng: 65, altitude: 2.15 });

    const controls = globeInstance.controls();
    if (controls) {
        controls.autoRotate = true;
        controls.autoRotateSpeed = 0.65;
        controls.enableZoom = false; // Prevent trapping page scroll
    }

    isReady.value = true;
};

onMounted(() => {
    isWebglSupported.value = checkWebGL();

    nextTick(() => {
        if (isWebglSupported.value) {
            initGlobe();

            if (window.ResizeObserver && globeContainer.value) {
                resizeObserver = new ResizeObserver((entries) => {
                    for (const entry of entries) {
                        const { width, height } = entry.contentRect;
                        if (globeInstance && width > 0 && height > 0) {
                            globeInstance.width(Math.round(width)).height(Math.round(height));
                        }
                    }
                });
                resizeObserver.observe(globeContainer.value);
            }
        }
    });
});

watch(
    () => props.locations,
    () => {
        if (globeInstance) {
            const { points, arcs, rings } = buildPointsAndArcs();
            globeInstance
                .pointsData(points)
                .labelsData(points.slice(0, 6))
                .arcsData(arcs)
                .ringsData(rings);
        }
    },
    { deep: true }
);

onBeforeUnmount(() => {
    if (resizeObserver) {
        resizeObserver.disconnect();
        resizeObserver = null;
    }
    if (globeInstance) {
        if (typeof globeInstance._destructor === 'function') {
            globeInstance._destructor();
        }
        globeInstance = null;
    }
});
</script>

<template>
    <div class="relative w-full aspect-square max-w-[430px] mx-auto flex items-center justify-center select-none">
        <!-- True 3D Three.js Globe Container -->
        <div
            v-if="isWebglSupported"
            ref="globeContainer"
            class="w-full h-full cursor-grab active:cursor-grabbing flex items-center justify-center drop-shadow-[0_28px_60px_rgba(19,41,61,0.28)]"
            aria-label="Globe 3D Interaktif Delixie"
        ></div>

        <!-- Graceful SVG Fallback if WebGL is disabled or during initial mount -->
        <svg
            v-if="!isWebglSupported || !isReady"
            :class="['w-full h-full drop-shadow-[0_28px_60px_rgba(19,41,61,0.22)]', isReady ? 'hidden' : 'absolute inset-0 pointer-events-none']"
            viewBox="0 0 420 420"
            xmlns="http://www.w3.org/2000/svg"
            aria-hidden="true"
        >
            <defs>
                <radialGradient id="fallbackSphere" cx="35%" cy="30%" r="70%">
                    <stop offset="0%" stop-color="#1A3A54" />
                    <stop offset="55%" stop-color="#13293D" />
                    <stop offset="100%" stop-color="#0C1B28" />
                </radialGradient>
            </defs>
            <circle cx="210" cy="210" r="178" fill="url(#fallbackSphere)" stroke="#67C7C6" stroke-width="2" />
            <path d="M105,140 Q145,105 190,128 T235,165 Q215,215 170,225 T110,195 Z" fill="#265D7A" fill-opacity="0.8" stroke="#67C7C6" stroke-width="1" />
            <path d="M245,105 Q295,95 330,138 T315,205 Q270,215 245,175 Z" fill="#265D7A" fill-opacity="0.8" stroke="#67C7C6" stroke-width="1" />
            <ellipse cx="210" cy="210" rx="95" ry="178" fill="none" stroke="rgba(103,199,198,0.25)" stroke-width="1.2" />
            <ellipse cx="210" cy="210" rx="178" ry="62" fill="none" stroke="rgba(103,199,198,0.25)" stroke-width="1.2" />
        </svg>
    </div>
</template>
