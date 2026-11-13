<script setup>
import { computed } from 'vue';

const props = defineProps({
    mode: {
        type: String,
        default: 'SEA', // 'SEA' | 'AIR' | 'LAND' | 'ROAD'
    },
    className: {
        type: String,
        default: 'w-full h-auto',
    },
});

const normalizedMode = computed(() => {
    const m = (props.mode || '').toUpperCase();
    if (m.includes('AIR') || m.includes('PLANE')) return 'AIR';
    if (m.includes('LAND') || m.includes('TRUCK') || m.includes('ROAD')) return 'LAND';
    return 'SEA';
});
</script>

<template>
    <div :class="['relative flex items-center justify-center select-none', className]">
        <!-- 1. LAND FREIGHT: Modern Logistics Semi-Truck (Haulboard Style) -->
        <svg
            v-if="normalizedMode === 'LAND'"
            viewBox="0 0 560 260"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            class="w-full h-auto drop-shadow-xl"
        >
            <defs>
                <linearGradient id="truckBody" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#FFFFFF" />
                    <stop offset="60%" stop-color="#EDF2F7" />
                    <stop offset="100%" stop-color="#CBD5E1" />
                </linearGradient>
                <linearGradient id="truckTrailer" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#FFFFFF" />
                    <stop offset="45%" stop-color="#F1F5F9" />
                    <stop offset="100%" stop-color="#CBD5E1" />
                </linearGradient>
                <linearGradient id="windshield" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#1E293B" />
                    <stop offset="100%" stop-color="#0F172A" />
                </linearGradient>
                <linearGradient id="cyanAccent" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#265D7A" />
                    <stop offset="100%" stop-color="#67C7C6" />
                </linearGradient>
                <filter id="truckShadow" x="-10%" y="80%" width="120%" height="40%" filterUnits="userSpaceOnUse">
                    <feGaussianBlur stdDeviation="8" />
                </filter>
            </defs>

            <!-- Ground Contact Shadow -->
            <ellipse cx="270" cy="235" rx="250" ry="14" fill="#0F172A" fill-opacity="0.28" filter="url(#truckShadow)" />

            <!-- Trailer Body (40ft Box) -->
            <rect x="25" y="48" width="340" height="152" rx="8" fill="url(#truckTrailer)" stroke="#94A3B8" stroke-width="2" />
            <!-- Trailer Corrugation Lines -->
            <line x1="60" y1="52" x2="60" y2="196" stroke="#CBD5E1" stroke-width="2" />
            <line x1="95" y1="52" x2="95" y2="196" stroke="#CBD5E1" stroke-width="2" />
            <line x1="130" y1="52" x2="130" y2="196" stroke="#CBD5E1" stroke-width="2" />
            <line x1="165" y1="52" x2="165" y2="196" stroke="#CBD5E1" stroke-width="2" />
            <line x1="200" y1="52" x2="200" y2="196" stroke="#CBD5E1" stroke-width="2" />
            <line x1="235" y1="52" x2="235" y2="196" stroke="#CBD5E1" stroke-width="2" />
            <line x1="270" y1="52" x2="270" y2="196" stroke="#CBD5E1" stroke-width="2" />
            <line x1="305" y1="52" x2="305" y2="196" stroke="#CBD5E1" stroke-width="2" />
            <line x1="340" y1="52" x2="340" y2="196" stroke="#CBD5E1" stroke-width="2" />

            <!-- Trailer Branding Strip (Delixie Teal) -->
            <rect x="25" y="115" width="340" height="26" fill="url(#cyanAccent)" />
            <text x="45" y="132" fill="#FFFFFF" font-family="system-ui, sans-serif" font-size="11" font-weight="900" letter-spacing="3">DELIXIE CARGO EXPRESS</text>
            <text x="300" y="132" fill="#A7E4DB" font-family="monospace" font-size="10" font-weight="bold">#40HQ</text>

            <!-- Trailer Underside Frame & Battery/Fuel Box -->
            <rect x="50" y="196" width="300" height="12" fill="#334155" />
            <rect x="170" y="175" width="80" height="26" rx="3" fill="#1E293B" stroke="#475569" stroke-width="1.5" />
            <circle cx="210" cy="188" r="4" fill="#94A3B8" />

            <!-- Cab Over Tractor Unit (Cab Cabin) -->
            <path
                d="M 360 196 
                   L 360 85 
                   C 360 70, 375 58, 395 54 
                   L 460 48 
                   C 478 47, 492 58, 498 75 
                   L 522 145 
                   C 528 160, 532 178, 532 196 
                   L 528 215 
                   L 360 215 Z"
                fill="url(#truckBody)"
                stroke="#64748B"
                stroke-width="2"
            />

            <!-- Aerodynamic Roof Fairing / Deflector -->
            <path d="M 370 54 L 460 48 L 452 70 L 370 70 Z" fill="#E2E8F0" stroke="#94A3B8" stroke-width="1.5" />

            <!-- Large Panoramic Windshield & Sun Visor -->
            <path d="M 445 68 L 488 74 L 512 128 L 435 125 Z" fill="url(#windshield)" stroke="#0F172A" stroke-width="2" />
            <!-- Windshield Reflection -->
            <path d="M 450 72 L 475 75 L 455 124 L 438 123 Z" fill="#38BDF8" fill-opacity="0.3" />
            <rect x="440" y="65" width="55" height="5" rx="1.5" fill="#1E293B" />

            <!-- Side Driver Window -->
            <path d="M 382 76 L 430 76 L 430 125 L 382 122 Z" fill="url(#windshield)" stroke="#334155" stroke-width="1.5" />
            <line x1="430" y1="76" x2="430" y2="125" stroke="#94A3B8" stroke-width="2" />

            <!-- Large Aerodynamic Side Mirror -->
            <rect x="424" y="80" width="8" height="34" rx="3" fill="#0F172A" stroke="#64748B" stroke-width="1" />
            <line x1="418" y1="88" x2="424" y2="92" stroke="#475569" stroke-width="2" />
            <line x1="418" y1="106" x2="424" y2="104" stroke="#475569" stroke-width="2" />

            <!-- Front Bumper & High-Tech Grille -->
            <path d="M 505 150 L 526 150 L 530 196 L 498 196 Z" fill="#1E293B" />
            <line x1="504" y1="160" x2="528" y2="160" stroke="#67C7C6" stroke-width="2" />
            <line x1="503" y1="168" x2="529" y2="168" stroke="#67C7C6" stroke-width="2" />
            <line x1="501" y1="176" x2="530" y2="176" stroke="#67C7C6" stroke-width="2" />
            <line x1="500" y1="184" x2="530" y2="184" stroke="#67C7C6" stroke-width="2" />

            <!-- LED Headlamp (Glowing Cyan) -->
            <polygon points="518,188 532,188 530,202 516,200" fill="#38BDF8" stroke="#0284C7" stroke-width="1.5" />
            <circle cx="524" cy="194" r="3" fill="#FFFFFF" />

            <!-- Chrome Door Handle & Steps -->
            <rect x="390" y="140" width="14" height="4" rx="1.5" fill="#94A3B8" />
            <rect x="385" y="172" width="22" height="4" rx="1" fill="#334155" />
            <rect x="385" y="184" width="22" height="4" rx="1" fill="#334155" />

            <!-- Heavy Wheels & Dual Axles (Rear Trailer Wheels) -->
            <!-- Wheel 1 -->
            <g>
                <circle cx="95" cy="208" r="26" fill="#0F172A" />
                <circle cx="95" cy="208" r="18" fill="#334155" stroke="#94A3B8" stroke-width="3" />
                <circle cx="95" cy="208" r="7" fill="#E2E8F0" />
                <!-- Lug nuts -->
                <circle cx="95" cy="199" r="1.5" fill="#64748B" /><circle cx="95" cy="217" r="1.5" fill="#64748B" />
                <circle cx="86" cy="208" r="1.5" fill="#64748B" /><circle cx="104" cy="208" r="1.5" fill="#64748B" />
            </g>

            <!-- Wheel 2 -->
            <g>
                <circle cx="155" cy="208" r="26" fill="#0F172A" />
                <circle cx="155" cy="208" r="18" fill="#334155" stroke="#94A3B8" stroke-width="3" />
                <circle cx="155" cy="208" r="7" fill="#E2E8F0" />
                <circle cx="155" cy="199" r="1.5" fill="#64748B" /><circle cx="155" cy="217" r="1.5" fill="#64748B" />
                <circle cx="146" cy="208" r="1.5" fill="#64748B" /><circle cx="164" cy="208" r="1.5" fill="#64748B" />
            </g>

            <!-- Wheel 3 (Tractor Rear Tandem) -->
            <g>
                <circle cx="395" cy="208" r="26" fill="#0F172A" />
                <circle cx="395" cy="208" r="18" fill="#334155" stroke="#94A3B8" stroke-width="3" />
                <circle cx="395" cy="208" r="7" fill="#E2E8F0" />
            </g>

            <!-- Wheel 4 (Front Steer Wheel) -->
            <g>
                <circle cx="480" cy="208" r="26" fill="#0F172A" />
                <circle cx="480" cy="208" r="18" fill="#334155" stroke="#CBD5E1" stroke-width="3" />
                <circle cx="480" cy="208" r="7" fill="#F8FAFC" />
                <circle cx="480" cy="199" r="1.5" fill="#64748B" /><circle cx="480" cy="217" r="1.5" fill="#64748B" />
                <circle cx="471" cy="208" r="1.5" fill="#64748B" /><circle cx="489" cy="208" r="1.5" fill="#64748B" />
            </g>
        </svg>

        <!-- 2. AIR FREIGHT: Modern Cargo Freighter Jet (Boeing 777F Style) -->
        <svg
            v-else-if="normalizedMode === 'AIR'"
            viewBox="0 0 580 260"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            class="w-full h-auto drop-shadow-xl"
        >
            <defs>
                <linearGradient id="fuselageGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#FFFFFF" />
                    <stop offset="55%" stop-color="#F1F5F9" />
                    <stop offset="100%" stop-color="#94A3B8" />
                </linearGradient>
                <linearGradient id="turbineGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#1E293B" />
                    <stop offset="60%" stop-color="#334155" />
                    <stop offset="100%" stop-color="#0F172A" />
                </linearGradient>
                <linearGradient id="delixieFin" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#102232" />
                    <stop offset="60%" stop-color="#17384E" />
                    <stop offset="100%" stop-color="#265D7A" />
                </linearGradient>
            </defs>

            <!-- Shadow underneath -->
            <ellipse cx="290" cy="235" rx="230" ry="12" fill="#0F172A" fill-opacity="0.22" filter="url(#truckShadow)" />

            <!-- Main Wing (Swept) -->
            <path d="M 230 135 L 340 188 L 320 200 L 190 148 Z" fill="#CBD5E1" stroke="#94A3B8" stroke-width="1.5" />
            <!-- High-Bypass Turbofan Jet Engine -->
            <g>
                <ellipse cx="265" cy="180" rx="34" ry="18" fill="url(#turbineGrad)" stroke="#475569" stroke-width="1.5" />
                <ellipse cx="240" cy="180" rx="9" ry="16" fill="#0F172A" />
                <!-- Spinner cone -->
                <circle cx="240" cy="180" r="4" fill="#E2E8F0" />
                <!-- Engine Pylon -->
                <rect x="250" y="152" width="20" height="15" fill="#475569" />
            </g>

            <!-- Vertical Tail Fin (Stabilizer) -->
            <path d="M 60 120 L 110 32 L 155 32 L 138 120 Z" fill="url(#delixieFin)" stroke="#0F172A" stroke-width="1.5" />
            <!-- Delixie Logo on Tail -->
            <path d="M 108 55 Q 128 45 136 68 Q 118 78 108 55 Z" fill="#67C7C6" />
            <path d="M 112 62 Q 130 54 133 74 Q 118 80 112 62 Z" fill="#A7E4DB" opacity="0.8" />

            <!-- Horizontal Stabilizer -->
            <path d="M 40 118 L 88 102 L 98 108 L 48 126 Z" fill="#94A3B8" stroke="#64748B" stroke-width="1" />

            <!-- Aerodynamic Fuselage (Main Body) -->
            <path
                d="M 50 125 
                   L 460 125 
                   C 505 125, 545 136, 555 152 
                   C 545 168, 505 178, 460 178 
                   L 75 160 
                   L 50 125 Z"
                fill="url(#fuselageGrad)"
                stroke="#64748B"
                stroke-width="2"
            />

            <!-- Delixie Airline Cheatline Livery -->
            <path d="M 90 148 L 515 152 L 480 162 L 80 156 Z" fill="url(#cyanAccent)" />
            <text x="210" y="142" fill="#13293D" font-family="system-ui, sans-serif" font-size="14" font-weight="900" letter-spacing="4">DELIXIE CARGO</text>
            <text x="355" y="142" fill="#265D7A" font-family="monospace" font-size="10" font-weight="bold">B777-F</text>

            <!-- Main Deck Cargo Door Outline (Forward & Aft) -->
            <rect x="130" y="128" width="45" height="28" rx="2" fill="none" stroke="#94A3B8" stroke-dasharray="3, 2" stroke-width="1.5" />
            <rect x="410" y="132" width="35" height="24" rx="2" fill="none" stroke="#94A3B8" stroke-dasharray="3, 2" stroke-width="1.5" />

            <!-- Cockpit Windows -->
            <polygon points="522,143 538,145 544,152 526,150" fill="#0F172A" />
            <polygon points="508,141 520,143 524,150 512,149" fill="#0F172A" />
            <polygon points="496,140 506,141 510,149 500,148" fill="#0F172A" />

            <!-- Front Nose Landing Gear -->
            <g>
                <rect x="495" y="176" width="5" height="34" fill="#475569" />
                <circle cx="495" cy="214" r="10" fill="#0F172A" />
                <circle cx="495" cy="214" r="5" fill="#E2E8F0" />
            </g>

            <!-- Main Center Landing Gear -->
            <g>
                <rect x="295" y="180" width="8" height="32" fill="#475569" />
                <circle cx="290" cy="216" r="12" fill="#0F172A" />
                <circle cx="290" cy="216" r="6" fill="#CBD5E1" />
                <circle cx="312" cy="216" r="12" fill="#0F172A" />
                <circle cx="312" cy="216" r="6" fill="#CBD5E1" />
            </g>
        </svg>

        <!-- 3. SEA FREIGHT: Modern Ocean Container Ship (Default) -->
        <svg
            v-else
            viewBox="0 0 580 260"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            class="w-full h-auto drop-shadow-xl"
        >
            <defs>
                <linearGradient id="shipHull" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#1E293B" />
                    <stop offset="60%" stop-color="#0F172A" />
                    <stop offset="100%" stop-color="#030712" />
                </linearGradient>
                <linearGradient id="waterGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#265D7A" stop-opacity="0.3" />
                    <stop offset="50%" stop-color="#67C7C6" stop-opacity="0.6" />
                    <stop offset="100%" stop-color="#265D7A" stop-opacity="0.3" />
                </linearGradient>
            </defs>

            <!-- Ocean Water Surface Waves -->
            <path d="M 20 216 Q 80 210 140 216 T 260 216 T 380 216 T 500 216 T 560 216 L 560 236 L 20 236 Z" fill="url(#waterGrad)" />
            <line x1="20" y1="216" x2="560" y2="216" stroke="#67C7C6" stroke-width="2" stroke-linecap="round" opacity="0.8" />

            <!-- Ship Hull (Deep Sea Dark Navy with Red Antifouling Keel) -->
            <!-- Red Antifouling Base -->
            <path d="M 45 204 L 540 204 L 525 222 L 75 222 Z" fill="#991B1B" />
            <!-- Main Dark Hull -->
            <path
                d="M 35 152 
                   L 460 152 
                   C 505 152, 535 168, 552 204 
                   L 45 204 
                   C 38 190, 34 170, 35 152 Z"
                fill="url(#shipHull)"
                stroke="#334155"
                stroke-width="1.5"
            />
            <!-- Bulbous Bow Highlight -->
            <ellipse cx="538" cy="214" rx="14" ry="7" fill="#B91C1C" />

            <!-- Hull Top White Strip & Name -->
            <rect x="42" y="152" width="430" height="7" fill="#F8FAFC" />
            <text x="210" y="184" fill="#F8FAFC" font-family="system-ui, sans-serif" font-size="13" font-weight="900" letter-spacing="4">DELIXIE NAVIGATOR</text>
            <text x="430" y="184" fill="#67C7C6" font-family="monospace" font-size="9" font-weight="bold">IMO 9811002</text>

            <!-- Aft Superstructure (Bridge Tower & Navigation Deck) -->
            <rect x="75" y="68" width="65" height="84" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1.5" />
            <rect x="70" y="62" width="75" height="8" rx="2" fill="#E2E8F0" />
            <!-- Bridge Panoramic Windows -->
            <rect x="78" y="74" width="59" height="10" rx="1" fill="#0F172A" />
            <line x1="90" y1="74" x2="90" y2="84" stroke="#64748B" stroke-width="1.5" />
            <line x1="105" y1="74" x2="105" y2="84" stroke="#64748B" stroke-width="1.5" />
            <line x1="120" y1="74" x2="120" y2="84" stroke="#64748B" stroke-width="1.5" />
            <!-- Radar Mast & Antennas -->
            <line x1="107" y1="62" x2="107" y2="34" stroke="#475569" stroke-width="2.5" />
            <line x1="95" y1="42" x2="120" y2="42" stroke="#475569" stroke-width="2" />
            <circle cx="107" cy="32" r="3" fill="#67C7C6" />

            <!-- Exhaust Funnel (Smokestack) with Delixie Navy/Cyan Livery -->
            <path d="M 60 115 L 56 75 L 72 75 L 72 115 Z" fill="#13293D" stroke="#0F172A" stroke-width="1.5" />
            <rect x="57" y="85" width="14" height="6" fill="#67C7C6" />

            <!-- Stacked Intermodal Shipping Containers (Bays 1-5) -->
            <!-- Bay 1 (Near Bridge) -->
            <rect x="150" y="105" width="55" height="22" rx="2" fill="#0284C7" stroke="#0369A1" stroke-width="1" />
            <rect x="150" y="128" width="55" height="22" rx="2" fill="#0D9488" stroke="#0F766E" stroke-width="1" />

            <!-- Bay 2 -->
            <rect x="210" y="82" width="55" height="22" rx="2" fill="#059669" stroke="#047857" stroke-width="1" />
            <rect x="210" y="105" width="55" height="22" rx="2" fill="#D97706" stroke="#B45309" stroke-width="1" />
            <rect x="210" y="128" width="55" height="22" rx="2" fill="#2563EB" stroke="#1D4ED8" stroke-width="1" />

            <!-- Bay 3 (Center High Stack) -->
            <rect x="270" y="60" width="55" height="21" rx="2" fill="#67C7C6" stroke="#0D9488" stroke-width="1" />
            <rect x="270" y="82" width="55" height="22" rx="2" fill="#1E293B" stroke="#0F172A" stroke-width="1" />
            <rect x="270" y="105" width="55" height="22" rx="2" fill="#DC2626" stroke="#B91C1C" stroke-width="1" />
            <rect x="270" y="128" width="55" height="22" rx="2" fill="#0284C7" stroke="#0369A1" stroke-width="1" />

            <!-- Bay 4 -->
            <rect x="330" y="82" width="55" height="22" rx="2" fill="#0D9488" stroke="#0F766E" stroke-width="1" />
            <rect x="330" y="105" width="55" height="22" rx="2" fill="#2563EB" stroke="#1D4ED8" stroke-width="1" />
            <rect x="330" y="128" width="55" height="22" rx="2" fill="#D97706" stroke="#B45309" stroke-width="1" />

            <!-- Bay 5 (Bow Stack) -->
            <rect x="390" y="105" width="55" height="22" rx="2" fill="#059669" stroke="#047857" stroke-width="1" />
            <rect x="390" y="128" width="55" height="22" rx="2" fill="#DC2626" stroke="#B91C1C" stroke-width="1" />

            <!-- Container Details (Door Bars) -->
            <line x1="272" y1="70" x2="323" y2="70" stroke="#0F766E" stroke-width="1" stroke-dasharray="4, 3" />
            <line x1="212" y1="93" x2="263" y2="93" stroke="#047857" stroke-width="1" stroke-dasharray="4, 3" />
            <line x1="332" y1="116" x2="383" y2="116" stroke="#1D4ED8" stroke-width="1" stroke-dasharray="4, 3" />

            <!-- Forward Cargo Mooring & Anchor Crane -->
            <rect x="475" y="145" width="10" height="7" fill="#64748B" />
            <line x1="480" y1="145" x2="495" y2="135" stroke="#475569" stroke-width="2" />
        </svg>
    </div>
</template>
