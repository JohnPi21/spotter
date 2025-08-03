<template>
    <div class="mx-auto my-2 flex max-w-[768px] flex-col gap-6">
        <!-- Profile Box -->
        <UiBox class="flex items-center justify-between">
            <div class="flex gap-2">
                <div class="rounded-full border-2 border-layer-border p-3">
                    <Icon icon="icon-park-outline:muscle" width="22px" />
                </div>

                <div class="flex flex-col justify-center">
                    <p>{{ $page.props.auth.user.name }}</p>
                    <span class="text-sm text-secondary">Joined: {{ info.memberFor }}</span>
                </div>
            </div>

            <UiTabs :tabs="tabs" :active="activeTab" @change="changeTab" />
        </UiBox>

        <!-- Best Lifts Section -->
        <div>
            <div class="mb-2 flex items-center justify-between">
                <h2 class="text-2xl">Best Lifts</h2>
                <div class="text-sm text-secondary">
                    <!-- By Weight | By Volume -->
                    <!-- <UiTabs :tabs="tabs" /> -->
                </div>
            </div>

            <div class="flex flex-wrap gap-5">
                <UiBox
                    v-for="(lift, idx) in bestLifts"
                    :key="lift.exercise"
                    class="flex flex-1 flex-col items-center rounded-xl p-5"
                >
                    <div class="mb-2 rounded-xl bg-accent p-2">
                        <img :src="exerciseImages[lift.type]" width="60px" />
                    </div>
                    <div class="text-xl font-extrabold text-accent">
                        <span>{{ lift.value }}</span>
                        <span>{{ lift.unit }}</span>
                    </div>
                    <p class="text-center text-xl font-normal">{{ lift.exercise }}</p>
                </UiBox>
            </div>
        </div>

        <!-- Activity Graph Placeholder -->
        <div>
            <h2 class="mb-2 text-2xl">Activity</h2>
            <UiBox class="flex h-48 items-center justify-center">
                <ChartLine :chart-data="chartData" />
            </UiBox>
        </div>

        <!-- Last Workouts Section -->
        <div>
            <h2 class="mb-2 text-2xl">Last Workouts</h2>
            <div class="flex flex-col gap-3">
                <UiBox class="flex items-center justify-between p-4" v-for="workout in lastWorkouts">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-accent text-xl font-bold text-white"
                        >
                            {{ workout.day }}
                        </div>
                        <div>
                            <p class="font-semibold">{{ workout.label }}</p>
                            <p class="text-sm text-secondary">{{ workout.finishedAt }}</p>
                        </div>
                    </div>
                    <div class="flex gap-1 text-sm text-secondary">
                        <div>
                            <span>{{ workout.exercisesCount }}</span>
                            exercises |
                        </div>
                        <div>
                            <span>{{ workout.setsCount }}</span>
                            sets |
                        </div>
                        <div>
                            <span>{{ workout.totalValue }} {{ workout.unit }}</span>
                            |
                        </div>
                        <div>
                            <span>{{ workout.muscleGroups.length }}</span>
                            Muscle Groups
                        </div>
                    </div>
                </UiBox>

                <!-- <UiBox class="flex items-center justify-between p-4">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-accent text-xl font-bold text-white"
                        >
                            22
                        </div>
                        <div>
                            <p class="font-semibold">Pull Day</p>
                            <p class="text-sm text-secondary">4 days ago</p>
                        </div>
                    </div>
                    <div class="text-sm text-secondary">7 exercises | 20 sets | 950kg | 3 Muscle Groups</div>
                </UiBox> -->
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import ChartLine from "@/Components/Chart/Line.vue";
import UiBox from "@/Components/Ui/Box.vue";
import UiTabs from "@/Components/Ui/tabs.vue";
import { useTailwindColors } from "@/Composables/useTailwindTheme";
import { Icon } from "@iconify/vue";
import { router, usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";

const tailwindColors = useTailwindColors();

const exerciseImages = {
    deadlift: new URL("@/assets/images/deadlift.png", import.meta.url).href,
    bench: new URL("@/assets/images/bench.png", import.meta.url).href,
    squat: new URL("@/assets/images/squat.png", import.meta.url).href,
};
type ExerciseKey = keyof typeof exerciseImages;

type BestLift = {
    exercise: string;
    value: number;
    unit: string;
    type: ExerciseKey;
};

type Activity = {
    labels: string[];
    data: number[];
};

type LastWorkout = {
    day: number;
    label: string;
    finishedAt: string;
    exercisesCount: number;
    setsCount: number;
    totalValue: number;
    unit: string;
    muscleGroups: MuscleGroup[];
};

type Info = {
    memberFor: Date;
};

const props = defineProps<{
    bestLifts: BestLift[];
    activity: Activity;
    lastWorkouts: LastWorkout[];
    displayBy: string;
    info: Info;
}>();

const tabs = ref([
    { name: "By Weight", value: "weight" },
    { name: "By Volume", value: "volume" },
]);

const activeTab = ref(usePage().url.includes("displayBy=volume") ? 1 : 0);

const changeTab = (index: number) => {
    activeTab.value = index;

    const query = index === 0 ? { displayBy: "weight" } : { displayBy: "volume" };

    router.visit(route("dashboard", query), { preserveState: true });
};

const chartData = ref({
    labels: props.activity.labels,
    datasets: [
        {
            label: props.displayBy === "weight" ? "Weight / Day" : "Volume / Day",
            data: props.activity.data,
            fill: true,
            borderColor: tailwindColors.accent,
            backgroundColor: tailwindColors.amber,
            tension: 0.1,
        },
    ],
});

watch(
    () => props.activity,
    (activity) => {
        chartData.value.labels = activity.labels;
        chartData.value.datasets[0].data = activity.data;
        chartData.value.datasets[0].label = props.displayBy === "weight" ? "Weight / Day" : "Volume / Day";
    },
    { deep: true }
);
</script>
