<template>
    <div class="chart-container">
        <canvas ref="canvas" :aria-label="label" role="img"></canvas>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from "vue";

const props = defineProps({
    labels: {
        type: Array,
        default: () => [],
    },
    values: {
        type: Array,
        default: () => [],
    },
    label: {
        type: String,
        default: "Valeur",
    },
});

const canvas = ref(null);
let chart;
let chartLibraryPromise;

function loadChartLibrary() {
    chartLibraryPromise ??= import("chart.js").then(({
        BarController,
        BarElement,
        CategoryScale,
        Chart,
        LinearScale,
        Tooltip,
    }) => {
        Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);
        return Chart;
    });

    return chartLibraryPromise;
}

async function renderChart() {
    if (!canvas.value) {
        return;
    }

    const Chart = await loadChartLibrary();
    if (!canvas.value) {
        return;
    }

    chart?.destroy();
    chart = new Chart(canvas.value, {
        type: "bar",
        data: {
            labels: props.labels,
            datasets: [{
                label: props.label,
                data: props.values,
                backgroundColor: "rgba(23, 105, 170, 0.72)",
                borderColor: "#1769aa",
                borderWidth: 1,
                borderRadius: 6,
                maxBarThickness: 64,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        label: (context) => `${Number(context.raw).toLocaleString("fr-FR")} km`,
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: "Kilomètres",
                    },
                    ticks: {
                        callback: (value) => Number(value).toLocaleString("fr-FR"),
                    },
                },
                x: {
                    grid: {
                        display: false,
                    },
                },
            },
        },
    });
}

onMounted(renderChart);
watch(() => [props.labels, props.values], renderChart, { deep: true });
onBeforeUnmount(() => chart?.destroy());
</script>

<style scoped>
.chart-container {
    position: relative;
    min-height: 280px;
}

@media (max-width: 575.98px) {
    .chart-container {
        min-height: 230px;
    }
}
</style>
