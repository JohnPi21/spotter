<template>
    <div class="mx-auto my-2 flex max-w-[768px] flex-col gap-6">
        <!-- Profile Box -->
        <UiBox class="flex items-center justify-between">
            <div class="flex gap-1 md:gap-2">
                <div class="h-fit rounded-full border-2 border-layer-border p-3">
                    <Icon icon="icon-park-outline:muscle" width="22px" />
                </div>

                <div class="flex flex-col justify-center">
                    <p>{{ $page.props.auth.user.name }}</p>
                    <span class="block text-sm text-secondary max-[440px]:hidden">Joined: {{ dashboardStats.joinedAgo }}</span>
                </div>
            </div>

            <UiTabs :tabs="tabs" :active="activeTab" @change="changeTab" />
        </UiBox>

        <!-- Activity Graph Placeholder -->
        <div class="relative">
            <h2 class="mb-2 text-2xl">Activity</h2>
            <UiBox class="flex h-48 items-center justify-center">
                <div
                    class="absolute z-10 flex h-[95%] w-full flex-col items-center justify-center gap-3 rounded-xl bg-main/85 text-center"
                    v-if="!dashboardStats.activity"
                >
                    <Icon icon="mdi:chart-line" class="h-10 w-10 text-accent" />
                    <h3 class="text-xl font-semibold">Your Progress Awaits</h3>
                    <p class="max-w-[280px] text-sm text-secondary">After a few workouts, this chart will show your activity trends.</p>
                    <Link :href="placeholderUrl" class="rounded-lg bg-accent px-4 py-2 text-sm font-medium text-black transition hover:opacity-90">
                        Log a Workout
                    </Link>
                </div>

                <ChartLine :chart-data="chartData" :class="{ 'blur-sm': !dashboardStats.activity }" />
            </UiBox>
        </div>

        <!-- Last Workouts Section -->
        <div>
            <h2 class="mb-2 text-2xl">Last Workouts</h2>
            <div class="relative">
                <div
                    class="absolute z-10 flex h-full w-full flex-col items-center justify-center gap-3 rounded-xl bg-main/85 text-center"
                    v-if="!dashboardStats.lastWorkouts"
                >
                    <Icon icon="mdi:dumbbell" class="h-10 w-10 text-accent" />
                    <h3 class="text-xl font-semibold">No Workouts Yet</h3>
                    <p class="max-w-[280px] text-sm text-secondary">Your recent workouts will appear here once you start training.</p>
                    <Link :href="placeholderUrl" class="rounded-lg bg-accent px-4 py-2 text-sm font-medium text-black transition hover:opacity-90">
                        Begin Today
                    </Link>
                </div>
                <div class="flex flex-col gap-3" :class="{ 'p-2 blur-sm': !dashboardStats.lastWorkouts }">
                    <UiBox v-for="workout in lastWorkouts" class="group hover:bg-layer-light">
                        <Link
                            :href="route('days.show', { mesocycle: workout.mesocycle, day: workout.dayID })"
                            class="flex cursor-pointer flex-col items-center justify-between gap-4 p-4 min-[450px]:flex-row"
                        >
                            <div class="flex min-w-32 items-center gap-2">
                                <div class="flex aspect-square h-12 w-12 items-center justify-center rounded-lg bg-accent text-xl font-bold text-white">
                                    {{ workout.day }}
                                </div>
                                <div>
                                    <p class="font-semibold">{{ workout.label }}</p>
                                    <p class="text-sm text-secondary">{{ workout.finishedAt }}</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1 whitespace-nowrap text-sm text-secondary">
                                <div class="border-border-layer flex-1 rounded-md border border-layer-light px-2 py-1 group-hover:bg-layer">
                                    <span>{{ workout.exercisesCount }}</span>
                                    exercises
                                </div>
                                <div class="border-border-layer flex-1 rounded-md border border-layer-light px-2 py-1 group-hover:bg-layer">
                                    <span>{{ workout.setsCount }}</span>
                                    sets
                                </div>
                                <div class="border-border-layer flex-1 rounded-md border border-layer-light px-2 py-1 group-hover:bg-layer">
                                    <span>{{ workout.totalValue }} {{ workout.unit }}</span>
                                </div>
                                <div class="border-border-layer flex-1 rounded-md border border-layer-light px-2 py-1 group-hover:bg-layer">
                                    <span>{{ workout.muscleGroups.length }}</span>
                                    Muscle Groups
                                </div>
                            </div>
                        </Link>
                    </UiBox>
                </div>
            </div>
        </div>

        <!-- Best Lifts Section -->
        <div>
            <div class="mb-2 flex items-center justify-between">
                <h2 class="text-2xl">Best Lifts</h2>
            </div>

            <div class="relative">
                <div
                    class="absolute z-10 flex h-full w-full flex-col items-center justify-center gap-3 rounded-xl bg-main/85 text-center"
                    v-if="!dashboardStats.bestLifts"
                >
                    <Icon icon="mdi:trophy-outline" class="h-10 w-10 text-accent" />
                    <h3 class="text-xl font-semibold">Unlock Your Big 3</h3>
                    <p class="max-w-[280px] text-sm text-secondary">Record your workouts to see your best Deadlift, Bench Press, and Squat.</p>
                    <Link :href="placeholderUrl" class="rounded-lg bg-accent px-4 py-2 text-sm font-medium text-black transition hover:opacity-90">
                        Start Training
                    </Link>
                </div>
                <div class="flex flex-wrap gap-5" :class="{ 'p-2 blur-sm': !dashboardStats.bestLifts }">
                    <UiBox v-for="(lift, idx) in bestLifts" :key="lift.exercise" class="min-w-25 flex flex-1 flex-col items-center rounded-xl p-5">
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
        </div>
    </div>
</template>

<script setup lang="ts">
import ChartLine from "@/Components/Chart/Line.vue";
import UiBox from "@/Components/Ui/Box.vue";
import UiTabs from "@/Components/Ui/tabs.vue";
import { useTailwindColors } from "@/Composables/useTailwindTheme";
import { Icon } from "@iconify/vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed, defineProps, ref, watch } from "vue";

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
    mesocycle: number;
    dayID: number;
};

type DashboardStats = {
    joinedAgo: Date;
    bestLifts: boolean;
    activity: boolean;
    lastWorkouts: boolean;
};

const props = withDefaults(
    defineProps<{
        bestLifts?: BestLift[];
        activity?: Activity;
        lastWorkouts?: LastWorkout[];
        displayBy: string;
        dashboardStats: DashboardStats;
    }>(),
    {
        bestLifts: () => [
            { exercise: "Deadlift", value: 100, unit: "kg", type: "deadlift" },
            { exercise: "Bench", value: 100, unit: "kg", type: "bench" },
            { exercise: "Squat", value: 100, unit: "kg", type: "squat" },
        ],
        activity: () => ({
            labels: ["Day 1", "Day 2", "Day 3", "Day 4", "Day 5"],
            data: [1, 2, 3, 4, 2],
        }),
        lastWorkouts: () => [
            {
                day: 1,
                label: "Day 1",
                finishedAt: "5 days ago",
                exercisesCount: 5,
                setsCount: 20,
                totalValue: 430,
                unit: "kg",
                muscleGroups: [],
                mesocycle: 1,
                dayID: 22,
            },
            {
                day: 2,
                label: "Day 2",
                finishedAt: "5 days ago",
                exercisesCount: 5,
                setsCount: 20,
                totalValue: 430,
                unit: "kg",
                muscleGroups: [],
                mesocycle: 1,
                dayID: 22,
            },
            {
                day: 3,
                label: "Day 3",
                finishedAt: "5 days ago",
                exercisesCount: 5,
                setsCount: 20,
                totalValue: 430,
                unit: "kg",
                muscleGroups: [],
                mesocycle: 1,
                dayID: 22,
            },
        ],
    }
);

const page = usePage();

const placeholderUrl = computed(() => {
    return route(page.props.auth.flags.hasActiveMeso ? "mesocycles.current" : "mesocycles");
});

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
