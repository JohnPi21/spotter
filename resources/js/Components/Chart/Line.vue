<template>
    <Line id="line-chart" :options="chartOptions" :data="chartData" />
</template>

<script setup>
import {
    CategoryScale,
    Chart as ChartJS,
    Filler,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Title,
    Tooltip,
} from "chart.js";
import { ref } from "vue";
import { Line } from "vue-chartjs";

const props = defineProps({
    // chartOptions: {type: Object},
    chartData: { type: Object },
});

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

const chartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
});
</script>
