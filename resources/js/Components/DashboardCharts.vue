<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    statusDistribution: {
        type: Object,
        required: true,
    },
    carrierDistribution: {
        type: Object,
        default: () => ({}),
    },
});

const statusCanvas = ref(null);
const carrierCanvas = ref(null);

let statusChartInstance = null;
let carrierChartInstance = null;

const renderCharts = () => {
    // 1. Status Distribution Doughnut Chart
    if (statusCanvas.value) {
        if (statusChartInstance) {
            statusChartInstance.destroy();
        }

        const labels = Object.keys(props.statusDistribution);
        const data = Object.values(props.statusDistribution);

        statusChartInstance = new Chart(statusCanvas.value, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: [
                        '#64748B', // CREATED (slate)
                        '#06B6D4', // BOOKED (cyan)
                        '#6366F1', // PICKED_UP (indigo)
                        '#F59E0B', // IN_TRANSIT (amber)
                        '#10B981', // DELIVERED (emerald)
                        '#F43F5E', // CANCELLED (rose)
                    ],
                    borderWidth: 3,
                    borderColor: '#0B132B',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 10,
                            padding: 12,
                            color: '#94A3B8',
                            font: {
                                family: 'Plus Jakarta Sans, sans-serif',
                                size: 11,
                                weight: '600',
                            },
                        },
                    },
                },
                cutout: '70%',
            },
        });
    }

    // 2. Carrier Distribution Bar Chart
    if (carrierCanvas.value) {
        if (carrierChartInstance) {
            carrierChartInstance.destroy();
        }

        const labels = Object.keys(props.carrierDistribution);
        const data = Object.values(props.carrierDistribution);

        carrierChartInstance = new Chart(carrierCanvas.value, {
            type: 'bar',
            data: {
                labels: labels.length ? labels : ['Belum Ada Kargo'],
                datasets: [{
                    label: 'Jumlah Kargo',
                    data: labels.length ? data : [0],
                    backgroundColor: '#22D3EE',
                    hoverBackgroundColor: '#67E8F9',
                    borderRadius: 8,
                    maxBarThickness: 32,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#94A3B8',
                            font: { family: 'JetBrains Mono, monospace', size: 10 },
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.08)',
                        },
                    },
                    x: {
                        ticks: {
                            color: '#CBD5E1',
                            font: { family: 'Plus Jakarta Sans, sans-serif', size: 11 },
                        },
                        grid: {
                            display: false,
                        },
                    },
                },
            },
        });
    }
};

onMounted(() => {
    renderCharts();
});

watch(() => [props.statusDistribution, props.carrierDistribution], () => {
    renderCharts();
}, { deep: true });

onUnmounted(() => {
    if (statusChartInstance) statusChartInstance.destroy();
    if (carrierChartInstance) carrierChartInstance.destroy();
});
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Status Doughnut Chart Card -->
        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-cyan-500/15 border border-cyan-500/30 text-cyan-400 flex items-center justify-center text-xs">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Distribusi Status Kargo</h3>
                        <p class="text-[11px] text-slate-400">Proporsi pengiriman menurut fase siklus hidup</p>
                    </div>
                </div>
            </div>
            <div class="relative h-[260px] w-full flex items-center justify-center">
                <canvas ref="statusCanvas"></canvas>
            </div>
        </div>

        <!-- Carrier Bar Chart Card -->
        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-3xl p-6 shadow-xl">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/15 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-xs">
                        <i class="bi bi-bar-chart-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-white uppercase tracking-wider">Volume Muatan per Carrier</h3>
                        <p class="text-[11px] text-slate-400">Jumlah kargo aktif yang diangkut per armada</p>
                    </div>
                </div>
            </div>
            <div class="relative h-[260px] w-full">
                <canvas ref="carrierCanvas"></canvas>
            </div>
        </div>
    </div>
</template>
