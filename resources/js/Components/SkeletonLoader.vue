<script setup>
defineProps({
    variant: {
        type: String,
        default: 'cards', // 'cards' | 'table' | 'detail'
    },
    rows: {
        type: Number,
        default: 6,
    },
    columns: {
        type: Number,
        default: 6,
    },
    cards: {
        type: Number,
        default: 4,
    },
});
</script>

<template>
    <div class="space-y-6 animate-pulse" aria-busy="true" aria-label="Memuat data...">
        <!-- Page Header Skeleton -->
        <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 shadow-[0_16px_40px_rgba(19,41,61,0.06)] flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-2.5 flex-1">
                <div class="h-6 w-56 sm:w-72 rounded-xl bg-[#265D7A]/15"></div>
                <div class="h-3.5 w-80 max-w-full rounded-lg bg-[#67C7C6]/25"></div>
            </div>
            <div class="h-9 w-36 rounded-full bg-[#265D7A]/15 shrink-0"></div>
        </div>

        <!-- Variant: Cards (Dashboard KPIs & Charts) -->
        <template v-if="variant === 'cards'">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    v-for="i in cards"
                    :key="`kpi-${i}`"
                    class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-5 shadow-[0_12px_32px_rgba(19,41,61,0.06)] space-y-4"
                >
                    <div class="flex items-center justify-between">
                        <div class="h-3 w-24 rounded-md bg-[#265D7A]/15"></div>
                        <div class="w-9 h-9 rounded-2xl bg-[#67C7C6]/20"></div>
                    </div>
                    <div class="h-8 w-16 rounded-xl bg-[#13293D]/15"></div>
                    <div class="h-3 w-32 rounded-md bg-[#265D7A]/10"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div
                    v-for="j in 2"
                    :key="`panel-${j}`"
                    class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 shadow-[0_16px_40px_rgba(19,41,61,0.06)] space-y-4"
                >
                    <div class="flex items-center justify-between">
                        <div class="h-4 w-44 rounded-lg bg-[#265D7A]/15"></div>
                        <div class="h-5 w-20 rounded-full bg-[#67C7C6]/20"></div>
                    </div>
                    <div class="h-52 w-full rounded-2xl bg-[#EAF6F4]/80 border border-[#265D7A]/10"></div>
                </div>
            </div>
        </template>

        <!-- Variant: Table (Shipments, Carriers, Locations, Users) -->
        <template v-else-if="variant === 'table'">
            <!-- Filter Bar Skeleton -->
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-4 shadow-[0_16px_40px_rgba(19,41,61,0.06)] grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div class="sm:col-span-2 h-10 rounded-xl bg-[#EAF6F4]/90"></div>
                <div class="h-10 rounded-xl bg-[#EAF6F4]/90"></div>
                <div class="h-10 rounded-xl bg-[#265D7A]/15"></div>
            </div>

            <!-- Table Container Skeleton -->
            <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl overflow-hidden shadow-[0_16px_40px_rgba(19,41,61,0.06)]">
                <div class="bg-[#EAF6F4] border-b border-[#265D7A]/15 px-4 py-3.5 grid gap-4" :style="{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }">
                    <div v-for="c in columns" :key="`th-${c}`" class="h-3 rounded bg-[#265D7A]/20"></div>
                </div>
                <div class="divide-y divide-[#265D7A]/10">
                    <div
                        v-for="r in rows"
                        :key="`tr-${r}`"
                        class="px-4 py-4 grid gap-4 items-center"
                        :style="{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }"
                    >
                        <div
                            v-for="c in columns"
                            :key="`td-${r}-${c}`"
                            class="h-4 rounded-lg"
                            :class="c === 1 ? 'bg-[#13293D]/15 w-3/4' : 'bg-[#67C7C6]/20 w-5/6'"
                        ></div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Variant: Detail (Shipment Show & Map View) -->
        <template v-else-if="variant === 'detail'">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 h-80 shadow-[0_16px_40px_rgba(19,41,61,0.06)] flex flex-col justify-between">
                        <div class="h-4 w-48 rounded-lg bg-[#265D7A]/15"></div>
                        <div class="flex-1 mt-4 rounded-2xl bg-[#EAF6F4]/80"></div>
                    </div>
                    <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 space-y-4 shadow-[0_16px_40px_rgba(19,41,61,0.06)]">
                        <div class="h-4 w-52 rounded-lg bg-[#265D7A]/15"></div>
                        <div v-for="i in 3" :key="`tl-${i}`" class="h-14 rounded-2xl bg-[#EAF6F4]/70"></div>
                    </div>
                </div>
                <div class="bg-white/65 backdrop-blur-2xl border border-white/90 rounded-3xl p-6 h-96 space-y-4 shadow-[0_16px_40px_rgba(19,41,61,0.06)]">
                    <div class="h-4 w-40 rounded-lg bg-[#265D7A]/15"></div>
                    <div v-for="k in 5" :key="`meta-${k}`" class="h-8 rounded-xl bg-[#EAF6F4]/80"></div>
                </div>
            </div>
        </template>
    </div>
</template>
