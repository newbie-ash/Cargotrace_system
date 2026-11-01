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
                        '#9CA3AF', // CREATED (gray)
                        '#60A5FA', // BOOKED (blue)
                        '#818CF8', // PICKED_UP (indigo)
                        '#F59E0B', // IN_TRANSIT (amber)
                        '#10B981', // DELIVERED (emerald)
                        '#EF4444', // CANCELLED (rose)
                    ],
                    borderWidth: 2,
                    borderColor: '#FFFFFF',
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
                            font: {
                                family: 'Plus Jakarta Sans, sans-serif',
                                size: 11,
                                weight: '500',
                            },
                        },
                    },
                },
                cutout: '68%',
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
                    backgroundColor: '#1A1A1C',
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
                            font: { family: 'JetBrains Mono, monospace', size: 10 },
                        },
                        grid: {
                            color: '#E5E0D5',
                        },
                    },
                    x: {
                        ticks: {
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
        <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 shadow-xs">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#E5E0D5]">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                        <i class="bi bi-pie-chart-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-[#1A1A1C] uppercase tracking-wider">Distribusi Status Kargo</h3>
                        <p class="text-[11px] text-[#706E6B]">Proporsi pengiriman menurut fase siklus hidup</p>
                    </div>
                </div>
            </div>
            <div class="relative h-[260px] w-full flex items-center justify-center">
                <canvas ref="statusCanvas"></canvas>
            </div>
        </div>

        <!-- Carrier Bar Chart Card -->
        <div class="bg-white/70 backdrop-blur-md border border-[#E5E0D5] rounded-2xl p-6 shadow-xs">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-[#E5E0D5]">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                        <i class="bi bi-bar-chart-fill"></i>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold text-[#1A1A1C] uppercase tracking-wider">Volume Muatan per Carrier</h3>
                        <p class="text-[11px] text-[#706E6B]">Jumlah kargo aktif yang diangkut per armada</p>
                    </div>
                </div>
            </div>
            <div class="relative h-[260px] w-full">
                <canvas ref="carrierCanvas"></canvas>
            </div>
        </div>
    </div>
</template>
