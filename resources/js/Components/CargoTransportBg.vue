<template>
    <div class="absolute inset-0 pointer-events-none overflow-hidden select-none" aria-hidden="true">
        <!-- Deep Maritime Ocean Ambient Background -->
        <div class="absolute inset-0 bg-[#001F18]"></div>

        <!-- 100% Pure Procedural SVG Transport Cargo Scene (Mathematically Sharp Vector) -->
        <svg
            class="absolute inset-0 w-full h-full object-cover"
            viewBox="0 0 1920 1080"
            preserveAspectRatio="xMidYMid slice"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
        >
            <defs>
                <!-- Deep Ocean Radial Depth -->
                <radialGradient id="oceanDeep" cx="45%" cy="50%" r="65%">
                    <stop offset="0%" stop-color="#023B30" />
                    <stop offset="60%" stop-color="#01251E" />
                    <stop offset="100%" stop-color="#001410" />
                </radialGradient>

                <!-- Soft Brand Auras -->
                <radialGradient id="magentaGlow" cx="25%" cy="35%" r="45%">
                    <stop offset="0%" stop-color="#BE2388" stop-opacity="0.22" />
                    <stop offset="100%" stop-color="#BE2388" stop-opacity="0" />
                </radialGradient>

                <radialGradient id="tealGlow" cx="75%" cy="65%" r="50%">
                    <stop offset="0%" stop-color="#0D9488" stop-opacity="0.20" />
                    <stop offset="100%" stop-color="#0D9488" stop-opacity="0" />
                </radialGradient>

                <!-- Hull Shading Gradient -->
                <linearGradient id="hullDark" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#1E293B" />
                    <stop offset="30%" stop-color="#0F172A" />
                    <stop offset="70%" stop-color="#030712" />
                    <stop offset="100%" stop-color="#1E293B" />
                </linearGradient>

                <!-- Antifouling Keel / Waterline Strip -->
                <linearGradient id="keelRed" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#991B1B" />
                    <stop offset="50%" stop-color="#C2410C" />
                    <stop offset="100%" stop-color="#7F1D1D" />
                </linearGradient>

                <!-- Powerful Stern Wake & Propeller Foam (Trails to the Right) -->
                <linearGradient id="sternWake" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.85" />
                    <stop offset="15%" stop-color="#CCFBF1" stop-opacity="0.6" />
                    <stop offset="45%" stop-color="#5EEAD4" stop-opacity="0.3" />
                    <stop offset="85%" stop-color="#0D9488" stop-opacity="0.08" />
                    <stop offset="100%" stop-color="#01251E" stop-opacity="0" />
                </linearGradient>

                <!-- Bow Spray & Wave Foam (Slices Left) -->
                <linearGradient id="bowSpray" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#FFFFFF" stop-opacity="0.9" />
                    <stop offset="40%" stop-color="#BAE6FD" stop-opacity="0.5" />
                    <stop offset="100%" stop-color="#0284C7" stop-opacity="0" />
                </linearGradient>

                <!-- Water Wavelet Ripple Pattern -->
                <pattern id="oceanRipple" width="160" height="80" patternUnits="userSpaceOnUse">
                    <path d="M 0 20 Q 40 12, 80 20 T 160 20" fill="none" stroke="#5EEAD4" stroke-opacity="0.04" stroke-width="1.2" />
                    <path d="M 0 60 Q 40 52, 80 60 T 160 60" fill="none" stroke="#5EEAD4" stroke-opacity="0.03" stroke-width="1" />
                </pattern>
            </defs>

            <!-- Background Ocean Base -->
            <rect width="1920" height="1080" fill="url(#oceanDeep)" />
            <rect width="1920" height="1080" fill="url(#oceanRipple)" />
            <rect width="1920" height="1080" fill="url(#magentaGlow)" />
            <rect width="1920" height="1080" fill="url(#tealGlow)" />

            <!-- Hydrographic / Bathymetric Depth Sounding Curves -->
            <g stroke="#FAEFDB" stroke-opacity="0.05" stroke-width="1" fill="none">
                <path d="M-100 180 C 400 120, 800 240, 1300 160 C 1700 100, 1950 220, 2050 180" />
                <path d="M-100 380 C 350 320, 750 440, 1200 360 C 1650 300, 1900 420, 2050 360" />
                <path d="M-100 720 C 420 660, 850 780, 1320 700 C 1720 640, 1920 760, 2050 720" />
                <path d="M-100 920 C 380 860, 800 980, 1280 900 C 1680 840, 1950 960, 2050 920" />
            </g>

            <!-- Nautical Compass Rose & Geodetic Telemetry (Top Right & Left Background) -->
            <g transform="translate(1620, 200)" opacity="0.25">
                <circle cx="0" cy="0" r="90" stroke="#FAEFDB" stroke-width="0.75" stroke-dasharray="3 4" fill="none" />
                <circle cx="0" cy="0" r="60" stroke="#FAEFDB" stroke-width="0.5" fill="none" />
                <line x1="-100" y1="0" x2="100" y2="0" stroke="#FAEFDB" stroke-width="0.75" />
                <line x1="0" y1="-100" x2="0" y2="100" stroke="#FAEFDB" stroke-width="0.75" />
                <polygon points="0,-85 6,-20 0,-10 -6,-20" fill="#BE2388" />
                <polygon points="0,85 6,20 0,10 -6,20" fill="#FAEFDB" />
                <text x="-4" y="-95" fill="#FAEFDB" font-family="monospace" font-size="10" font-weight="bold">N</text>
            </g>

            <!-- Distant Aerial Cargo Route Arc (Top Air Freight Corridor) -->
            <g opacity="0.4">
                <path
                    d="M 100 160 Q 680 60, 1260 120 T 1940 220"
                    stroke="#BE2388"
                    stroke-opacity="0.3"
                    stroke-width="1.5"
                    stroke-dasharray="8 6"
                    fill="none"
                />
                <!-- Vector Cargo Aircraft -->
                <g transform="translate(1080, 108) rotate(8)">
                    <path d="M0 -2 L16 -1 L20 -7 L24 -7 L20 -1 L32 0 L36 -3 L38 -3 L36 1 L38 5 L36 5 L32 2 L20 3 L24 9 L20 9 L16 3 L0 4 Z" fill="#FAEFDB" fill-opacity="0.8" />
                    <circle cx="32" cy="1" r="2.5" fill="#BE2388" />
                </g>
            </g>

            <!-- ======================================================== -->
            <!-- MAIN VESSEL: TOP-DOWN AERIAL MEGA CONTAINER SHIP          -->
            <!-- HEADING WEST/LEFT (Bow at X=240, Stern at X=1500)        -->
            <!-- ======================================================== -->
            <g id="mega-container-vessel" transform="translate(0, 0)">
                <!-- 1. STERN WAKE FOAM TRAILS (Streaming to the RIGHT) -->
                <!-- Spreading Kelvin V-Wake Waves -->
                <path
                    d="M 1480 526 C 1600 490, 1740 420, 2020 360 L 2020 410 C 1760 460, 1620 515, 1480 534 Z"
                    fill="url(#sternWake)"
                />
                <path
                    d="M 1480 554 C 1600 590, 1740 660, 2020 720 L 2020 670 C 1760 620, 1620 565, 1480 546 Z"
                    fill="url(#sternWake)"
                />

                <!-- Central Hydro-Dynamic Propeller Turbulence Zone -->
                <ellipse cx="1550" cy="540" rx="80" ry="20" fill="#E2E8F0" fill-opacity="0.5" filter="blur(6px)" />
                <path
                    d="M 1480 532 C 1640 528, 1840 526, 2020 530 L 2020 550 C 1840 554, 1640 552, 1480 548 Z"
                    fill="#FFFFFF"
                    fill-opacity="0.4"
                />

                <!-- Transverse Wake Ripple Waves -->
                <g stroke="#CCFBF1" stroke-opacity="0.35" stroke-width="2" stroke-linecap="round" fill="none">
                    <path d="M 1530 512 Q 1550 540, 1530 568" />
                    <path d="M 1585 500 Q 1610 540, 1585 580" />
                    <path d="M 1650 488 Q 1680 540, 1650 592" />
                    <path d="M 1725 474 Q 1760 540, 1725 606" />
                    <path d="M 1810 460 Q 1850 540, 1810 620" />
                    <path d="M 1910 445 Q 1955 540, 1910 635" />
                </g>

                <!-- 2. BOW SPRAY & FOAM WAVES (Facing LEFT) -->
                <!-- Port (Top) Bow Wave Spray -->
                <path
                    d="M 270 526 C 220 500, 150 460, 60 410 L 72 398 C 170 445, 235 488, 280 520 Z"
                    fill="url(#bowSpray)"
                />
                <!-- Starboard (Bottom) Bow Wave Spray -->
                <path
                    d="M 270 554 C 220 580, 150 620, 60 670 L 72 682 C 170 635, 235 592, 280 560 Z"
                    fill="url(#bowSpray)"
                />
                <!-- Bulbous Bow Surface Boil / Foam -->
                <ellipse cx="250" cy="540" rx="14" ry="10" fill="#FFFFFF" fill-opacity="0.8" />
                <ellipse cx="240" cy="540" rx="8" ry="6" fill="#CCFBF1" fill-opacity="0.9" />

                <!-- 3. SHIP HULL (HEADING LEFT) -->
                <!-- Hull Deep Shadow on Sea -->
                <path
                    d="M 250 540
                       C 310 476, 430 460, 560 460
                       L 1440 460
                       C 1485 460, 1500 488, 1500 540
                       C 1500 592, 1485 620, 1440 620
                       L 560 620
                       C 430 620, 310 604, 250 540 Z"
                    fill="#000A08"
                    fill-opacity="0.6"
                    filter="blur(18px)"
                />

                <!-- Red/Rust Antifouling Keel Waterline Edge -->
                <path
                    d="M 244 540
                       C 306 472, 428 456, 560 456
                       L 1444 456
                       C 1490 456, 1508 486, 1508 540
                       C 1508 594, 1490 624, 1444 624
                       L 560 624
                       C 428 624, 306 608, 244 540 Z"
                    fill="url(#keelRed)"
                    stroke="#7F1D1D"
                    stroke-width="1.5"
                />

                <!-- Main Steel Hull (Deep Gunmetal Navy) -->
                <path
                    d="M 254 540
                       C 314 478, 434 462, 564 462
                       L 1438 462
                       C 1478 462, 1496 490, 1496 540
                       C 1496 590, 1478 618, 1438 618
                       L 564 618
                       C 434 618, 314 602, 254 540 Z"
                    fill="url(#hullDark)"
                    stroke="#334155"
                    stroke-width="2.5"
                />

                <!-- Outer Deck Walkway (Slate Gray) -->
                <path
                    d="M 276 540
                       C 328 488, 440 474, 564 474
                       L 1424 474
                       C 1458 474, 1474 496, 1474 540
                       C 1474 584, 1458 606, 1424 606
                       L 564 606
                       C 440 606, 328 592, 276 540 Z"
                    fill="#0F172A"
                    stroke="#475569"
                    stroke-width="1"
                />

                <!-- Forecastle Deck (Haluan Kiri) Detail -->
                <g fill="#1E293B" stroke="#64748B" stroke-width="0.8">
                    <!-- Anchor Winch Platforms & Capstans -->
                    <circle cx="324" cy="522" r="7" fill="#334155" />
                    <circle cx="324" cy="558" r="7" fill="#334155" />
                    <circle cx="298" cy="540" r="4" fill="#FAEFDB" />
                    <circle cx="346" cy="508" r="3" fill="#CBD5E1" />
                    <circle cx="346" cy="572" r="3" fill="#CBD5E1" />
                    <!-- Mooring Chains & Lines -->
                    <line x1="284" y1="540" x2="346" y2="540" stroke="#FAEFDB" stroke-opacity="0.35" stroke-dasharray="2 2" />
                </g>

                <!-- Navigation Sidelights (Left Bow Wings) -->
                <circle cx="334" cy="492" r="3.5" fill="#EF4444" /> <!-- Red Port Light -->
                <circle cx="334" cy="588" r="3.5" fill="#10B981" /> <!-- Green Starboard Light -->

                <!-- ======================================================== -->
                <!-- 4. CONTAINER BAYS (ORGANIZED CARGO STACKS)                -->
                <!-- 10 Bays: X=376 to X=1296                                 -->
                <!-- Palette: Magenta, Bone, Cyan, Teal, Orange, Navy         -->
                <!-- ======================================================== -->
                <!-- Transverse Cell Guides / Lashing Towers -->
                <g stroke="#334155" stroke-width="1.8">
                    <line x1="376" y1="478" x2="376" y2="602" />
                    <line x1="468" y1="478" x2="468" y2="602" />
                    <line x1="560" y1="478" x2="560" y2="602" />
                    <line x1="652" y1="478" x2="652" y2="602" />
                    <line x1="744" y1="478" x2="744" y2="602" />
                    <line x1="836" y1="478" x2="836" y2="602" />
                    <line x1="928" y1="478" x2="928" y2="602" />
                    <line x1="1020" y1="478" x2="1020" y2="602" />
                    <line x1="1112" y1="478" x2="1112" y2="602" />
                    <line x1="1204" y1="478" x2="1204" y2="602" />
                    <line x1="1296" y1="478" x2="1296" y2="602" />
                </g>

                <!-- Central Catwalk -->
                <rect x="372" y="537" width="928" height="6" fill="#1E293B" stroke="#475569" stroke-width="0.5" />

                <!-- BAY 1 (X: 382) -->
                <g stroke="#091E1A" stroke-width="0.8">
                    <rect x="382" y="482" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="424" y="482" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="382" y="500" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="424" y="500" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="382" y="518" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="424" y="518" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="382" y="547" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="424" y="547" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="382" y="565" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="424" y="565" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="382" y="583" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="424" y="583" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                </g>

                <!-- BAY 2 (X: 474) -->
                <g stroke="#091E1A" stroke-width="0.8">
                    <rect x="474" y="482" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="516" y="482" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="474" y="500" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="516" y="500" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="474" y="518" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="516" y="518" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="474" y="547" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="516" y="547" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="474" y="565" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="516" y="565" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="474" y="583" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="516" y="583" width="38" height="15" rx="1.5" fill="#BE2388" />
                </g>

                <!-- BAY 3 (X: 566) -->
                <g stroke="#091E1A" stroke-width="0.8">
                    <rect x="566" y="482" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="608" y="482" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="566" y="500" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="608" y="500" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="566" y="518" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="608" y="518" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="566" y="547" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="608" y="547" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="566" y="565" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="608" y="565" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="566" y="583" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="608" y="583" width="38" height="15" rx="1.5" fill="#BE2388" />
                </g>

                <!-- BAY 4 (X: 658) -->
                <g stroke="#091E1A" stroke-width="0.8">
                    <rect x="658" y="482" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="700" y="482" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="658" y="500" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="700" y="500" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="658" y="518" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="700" y="518" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="658" y="547" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="700" y="547" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="658" y="565" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="700" y="565" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="658" y="583" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="700" y="583" width="38" height="15" rx="1.5" fill="#BE2388" />
                </g>

                <!-- BAY 5 (X: 750) -->
                <g stroke="#091E1A" stroke-width="0.8">
                    <rect x="750" y="482" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="792" y="482" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="750" y="500" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="792" y="500" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="750" y="518" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="792" y="518" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="750" y="547" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="792" y="547" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="750" y="565" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="792" y="565" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="750" y="583" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="792" y="583" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                </g>

                <!-- BAY 6 (X: 842) -->
                <g stroke="#091E1A" stroke-width="0.8">
                    <rect x="842" y="482" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="884" y="482" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="842" y="500" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="884" y="500" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="842" y="518" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="884" y="518" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="842" y="547" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="884" y="547" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="842" y="565" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="884" y="565" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="842" y="583" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="884" y="583" width="38" height="15" rx="1.5" fill="#BE2388" />
                </g>

                <!-- BAY 7 (X: 934) -->
                <g stroke="#091E1A" stroke-width="0.8">
                    <rect x="934" y="482" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="976" y="482" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="934" y="500" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="976" y="500" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="934" y="518" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="976" y="518" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="934" y="547" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="976" y="547" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="934" y="565" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="976" y="565" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="934" y="583" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="976" y="583" width="38" height="15" rx="1.5" fill="#BE2388" />
                </g>

                <!-- BAY 8 (X: 1026) -->
                <g stroke="#091E1A" stroke-width="0.8">
                    <rect x="1026" y="482" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="1068" y="482" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="1026" y="500" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="1068" y="500" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="1026" y="518" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="1068" y="518" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="1026" y="547" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="1068" y="547" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="1026" y="565" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="1068" y="565" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="1026" y="583" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="1068" y="583" width="38" height="15" rx="1.5" fill="#0284C7" />
                </g>

                <!-- BAY 9 (X: 1118) -->
                <g stroke="#091E1A" stroke-width="0.8">
                    <rect x="1118" y="482" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="1160" y="482" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="1118" y="500" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="1160" y="500" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="1118" y="518" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="1160" y="518" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="1118" y="547" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="1160" y="547" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="1118" y="565" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="1160" y="565" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="1118" y="583" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="1160" y="583" width="38" height="15" rx="1.5" fill="#0D9488" />
                </g>

                <!-- BAY 10 (X: 1210) -->
                <g stroke="#091E1A" stroke-width="0.8">
                    <rect x="1210" y="482" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="1252" y="482" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="1210" y="500" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="1252" y="500" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="1210" y="518" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="1252" y="518" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="1210" y="547" width="38" height="15" rx="1.5" fill="#0284C7" />
                    <rect x="1252" y="547" width="38" height="15" rx="1.5" fill="#BE2388" />
                    <rect x="1210" y="565" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                    <rect x="1252" y="565" width="38" height="15" rx="1.5" fill="#EA580C" />
                    <rect x="1210" y="583" width="38" height="15" rx="1.5" fill="#0D9488" />
                    <rect x="1252" y="583" width="38" height="15" rx="1.5" fill="#FAEFDB" />
                </g>

                <!-- Container Roof Specular Highlights -->
                <g stroke="#FFFFFF" stroke-opacity="0.22" stroke-width="0.8">
                    <line x1="382" y1="484" x2="1290" y2="484" />
                    <line x1="382" y1="549" x2="1290" y2="549" />
                </g>

                <!-- ======================================================== -->
                <!-- 5. AFT SUPERSTRUCTURE, BRIDGE & FUNNEL (BURITAN KANAN)    -->
                <!-- X: 1316 to 1440                                          -->
                <!-- ======================================================== -->
                <!-- Main Deckhouse Tower -->
                <rect x="1316" y="472" width="76" height="136" rx="4" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1.5" />
                <!-- Nav Bridge Wings (Top observation deck) -->
                <rect x="1330" y="460" width="48" height="160" rx="3" fill="#FFFFFF" stroke="#94A3B8" stroke-width="1.5" />
                <!-- Panoramic Bridge Windows -->
                <line x1="1330" y1="464" x2="1330" y2="616" stroke="#0F172A" stroke-width="3" stroke-linecap="round" />

                <!-- Radar Mast & Satellite Communication Domes -->
                <rect x="1350" y="525" width="18" height="30" rx="2" fill="#E2E8F0" stroke="#64748B" stroke-width="1" />
                <circle cx="1358" cy="508" r="7.5" fill="#FFFFFF" stroke="#94A3B8" stroke-width="1" />
                <circle cx="1358" cy="572" r="7.5" fill="#FFFFFF" stroke="#94A3B8" stroke-width="1" />
                <circle cx="1358" cy="508" r="3" fill="#0284C7" />
                <circle cx="1358" cy="572" r="3" fill="#0284C7" />

                <!-- Twin Orange Free-fall Lifeboats on Stern Davits -->
                <rect x="1362" y="468" width="20" height="9" rx="3" fill="#EA580C" stroke="#C2410C" stroke-width="1" />
                <rect x="1362" y="603" width="20" height="9" rx="3" fill="#EA580C" stroke="#C2410C" stroke-width="1" />

                <!-- Main Engine Funnel (Cerobong) with Brand Livery -->
                <rect x="1402" y="520" width="36" height="40" rx="4" fill="#0F172A" stroke="#334155" stroke-width="1.5" />
                <rect x="1402" y="534" width="36" height="12" fill="#BE2388" />
                <ellipse cx="1434" cy="540" rx="3.5" ry="9" fill="#1E293B" />

                <!-- Stern Mooring & Helipad Deck (X=1446 to 1494) -->
                <g fill="#1E293B" stroke="#475569" stroke-width="0.8">
                    <circle cx="1464" cy="540" r="17" fill="none" stroke="#FAEFDB" stroke-opacity="0.4" stroke-width="1.5" stroke-dasharray="3 3" />
                    <text x="1459" y="546" fill="#FAEFDB" fill-opacity="0.5" font-family="monospace" font-size="14" font-weight="bold">H</text>
                    <circle cx="1478" cy="514" r="3" fill="#94A3B8" />
                    <circle cx="1478" cy="566" r="3" fill="#94A3B8" />
                </g>
            </g>
        </svg>

        <!-- Multi-Stop CSS Atmospheric Gradient Overlays for Maximum Contrast & Text Legibility -->
        <div class="absolute inset-0 bg-gradient-to-r from-[#001F18]/82 via-[#001F18]/30 to-[#001F18]/80"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-[#001F18]/70 via-transparent to-[#001F18]/85"></div>

        <!-- Ambient Backdrop Lights -->
        <div class="absolute -top-32 -left-28 w-[640px] h-[640px] rounded-full bg-[#BE2388]/15 blur-[120px]"></div>
        <div class="absolute bottom-10 right-10 w-[520px] h-[520px] rounded-full bg-[#0D9488]/15 blur-[130px]"></div>
    </div>
</template>
