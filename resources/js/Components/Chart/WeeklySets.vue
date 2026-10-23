<script setup lang="ts">
import type { TrainingMuscleGroup, TrainingWeek } from "@/types/dashboard";
import { BarElement, CategoryScale, Chart as ChartJS, LinearScale, Tooltip, type ChartOptions } from "chart.js";
import { computed } from "vue";
import { Bar } from "vue-chartjs";

ChartJS.register(BarElement, CategoryScale, LinearScale, Tooltip);

const props = defineProps<{
    weeks: TrainingWeek[];
    groups: TrainingMuscleGroup[];
    selectedGroup: number | null;
}>();

const colors = ["#FB923C", "#38BDF8", "#A78BFA", "#34D399", "#F472B6", "#FACC15", "#2DD4BF", "#818CF8"];
const data = computed(() => ({
    labels: props.weeks.map((week) => week.label),
    datasets: props.groups
        .filter((group) => props.selectedGroup === null || group.id === props.selectedGroup)
        .map((group) => ({
            label: group.name,
            data: group.sets,
            backgroundColor: colors[props.groups.indexOf(group) % colors.length],
            borderRadius: 3,
            borderSkipped: false as const,
            maxBarThickness: 42,
        })),
}));

const options: ChartOptions<"bar"> = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: "index", intersect: false },
    plugins: {
        legend: { display: false },
        tooltip: {
            filter: (item) => Number(item.raw) > 0,
            callbacks: { label: (item) => `${item.dataset.label}: ${item.formattedValue} sets` },
        },
    },
    scales: {
        x: { stacked: true, grid: { display: false }, ticks: { color: "#B0B0B0" }, border: { display: false } },
        y: {
            stacked: true,
            beginAtZero: true,
            ticks: { precision: 0, color: "#757575" },
            title: { display: true, text: "Completed sets", color: "#B0B0B0" },
            grid: { color: "#242424" },
            border: { display: false },
        },
    },
};
</script>

<template>
    <Bar
        :data="data"
        :options="options"
        role="img"
        aria-label="Completed sets per muscle group over eight calendar weeks"
    />
</template>
