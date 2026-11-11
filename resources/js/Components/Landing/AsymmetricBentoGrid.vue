<script setup>
defineProps({
    carriers: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({ on_time_rate: 100 }),
    },
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
                    <div class="flex items-start justify-between gap-4 mb-6">
                        <div>
                            <span class="px-2.5 py-1 rounded-md bg-blue-600/20 text-blue-300 border border-blue-500/30 text-[10px] font-bold uppercase tracking-wider font-mono">
                                Leaflet Dark Matter + Maritime Polyline
                            </span>
                            <h3 class="text-xl sm:text-2xl font-bold text-white mt-3">
                                Live Intercontinental Route Visibility
                            </h3>
                            <p class="text-xs text-slate-300 mt-1.5 max-w-lg leading-relaxed">
                                Pelacakan lintasan kargo lintas samudra secara kronologis menghubungkan Origin, titik checkpoint transit maritim, hingga Destination akhir tanpa blind spot.
                            </p>
                        </div>
                        <div class="w-11 h-11 rounded-2xl bg-blue-600/20 border border-blue-500/30 text-blue-400 flex items-center justify-center shrink-0">
                            <i class="bi bi-map-fill text-lg" aria-hidden="true"></i>
                        </div>
                    </div>

                    <!-- Map Route Visualization Space -->
                    <div class="relative rounded-2xl bg-[#070B14] border border-white/10 p-5 overflow-hidden flex-1 min-h-[260px] flex flex-col justify-between">
                        <!-- Subtle Vector Map Backdrop -->
                        <svg viewBox="0 0 800 320" class="w-full h-44 sm:h-52 opacity-85 select-none" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="routeArc" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#10B981" />
                                    <stop offset="50%" stop-color="#2563EB" />
                                    <stop offset="100%" stop-color="#F59E0B" />
                                </linearGradient>
                            </defs>

                            <!-- Minimalist Grid Lines -->
                            <path d="M 0,80 H 800 M 0,160 H 800 M 0,240 H 800" stroke="rgba(255,255,255,0.03)" stroke-width="1" />
                            <path d="M 160,0 V 320 M 320,0 V 320 M 480,0 V 320 M 640,0 V 320" stroke="rgba(255,255,255,0.03)" stroke-width="1" />

                            <!-- Continental Silhouette Shapes -->
                            <path d="M 90,70 L 180,60 L 210,120 L 140,140 L 110,110 Z" fill="rgba(255,255,255,0.04)" stroke="rgba(255,255,255,0.08)" />
                            <path d="M 280,65 L 380,60 L 400,130 L 310,145 Z" fill="rgba(255,255,255,0.04)" stroke="rgba(255,255,255,0.08)" />
                            <path d="M 430,85 L 620,75 L 660,175 L 520,185 L 450,150 Z" fill="rgba(255,255,255,0.04)" stroke="rgba(255,255,255,0.08)" />
                            <path d="M 600,200 L 680,210 L 690,250 L 620,255 Z" fill="rgba(37,99,235,0.12)" stroke="rgba(37,99,235,0.3)" />

                            <!-- Main Intercontinental Maritime Arc: IDJKT -> SGSIN -> AEDXB -> NLRTM -->
                            <path d="M 640,225 Q 520,185 450,145 T 310,95" fill="none" stroke="url(#routeArc)" stroke-width="2.5" />
                            <path d="M 640,225 Q 520,185 450,145 T 310,95" fill="none" stroke="#FFFFFF" stroke-width="1" stroke-dasharray="5 8" opacity="0.5" />

                            <!-- Node 1: IDJKT -->
                            <circle cx="640" cy="225" r="7" fill="#10B981" opacity="0.25" />
                            <circle cx="640" cy="225" r="3.5" fill="#10B981" />
                            <text x="650" y="229" fill="#10B981" font-size="11" font-family="monospace" font-weight="bold">IDJKT (Jakarta)</text>

                            <!-- Node 2: SGSIN -->
                            <circle cx="570" cy="192" r="4" fill="#38BDF8" />
                            <text x="530" y="182" fill="#94A3B8" font-size="10" font-family="monospace">SGSIN</text>

                            <!-- Node 3: AEDXB -->
                            <circle cx="450" cy="145" r="4" fill="#2563EB" />
                            <text x="458" y="148" fill="#60A5FA" font-size="10" font-family="monospace">AEDXB</text>

                            <!-- Node 4: NLRTM -->
                            <circle cx="310" cy="95" r="7" fill="#F59E0B" opacity="0.25" />
                            <circle cx="310" cy="95" r="3.5" fill="#F59E0B" />
                            <text x="200" y="99" fill="#FBBF24" font-size="11" font-family="monospace" font-weight="bold">NLRTM (Rotterdam)</text>
                        </svg>

                        <!-- Telemetry Corridor Progress Footer -->
                        <div class="pt-4 border-t border-white/10 grid grid-cols-3 items-center gap-2 font-mono text-xs">
                            <div>
                                <span class="text-[10px] text-slate-400 block">ORIGIN PORT</span>
                                <strong class="text-emerald-400">IDJKT &bull; Tanjung Priok</strong>
                            </div>
                            <div class="text-center">
                                <span class="px-2 py-0.5 rounded bg-blue-600/20 text-blue-300 border border-blue-500/30 text-[10px]">
                                    11,380 KM CORRIDOR
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-slate-400 block">FINAL HUB</span>
                                <strong class="text-amber-400">NLRTM &bull; Europort</strong>
                            </div>
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
