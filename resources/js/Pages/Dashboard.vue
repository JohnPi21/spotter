<template>
    <div class="mx-auto my-2 flex max-w-[768px] flex-col gap-6">
        <!-- Profile Box -->
        <UiBox class="flex items-center justify-between rounded-xl bg-layer p-2">
            <div class="flex items-center gap-2">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-accent/10">
                    <div class="relative">
                        <div class="absolute inset-0 rounded-xl bg-accent/20 blur-xl"></div>

                        <div class="relative flex h-12 w-12 items-center justify-center rounded-xl bg-layer">
                            <Icon icon="mdi:weight-lifter" width="30" class="text-accent" />
                        </div>
                    </div>
                </div>

                <div class="flex flex-col">
                    <p class="text-lg font-semibold">{{ $page.props.auth.user.name }}</p>
                    <span class="text-xs text-secondary">Joined: {{ dashboardStats.joinedAgo }}</span>
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
                    <p class="max-w-[280px] text-sm text-secondary">
                        After a few workouts, this chart will show your activity trends.
                    </p>
                    <Link
                        :href="placeholderUrl"
                        class="rounded-lg bg-accent px-4 py-2 text-sm font-medium text-black transition hover:opacity-90"
                    >
                        Log a Workout
                    </Link>
                </div>

                <ChartLine :chart-data="chartData" :class="{ 'blur-sm': !dashboardStats.activity }" />
            </UiBox>
        </div>

        <!-- Last Workouts Section -->
        <div>
            <h2 class="mb-2 text-2xl">Last Workouts</h2>

            <!-- MOBILE: phone-style cards -->
            <div class="space-y-4 md:hidden">
                <UiBox
                    v-for="workout in lastWorkouts"
                    :key="workout.dayID"
                    class="relative overflow-hidden rounded-lg bg-layer/70 p-5 shadow-[0_24px_70px_rgba(0,0,0,.75)]"
                >
                    <Link
                        :href="route('days.show', { mesocycle: workout.mesocycle, day: workout.dayID })"
                        class="block"
                    >
                        <!-- Top row: today / label / finishedAt -->
                        <div
                            class="mb-4 flex items-center justify-between text-[11px] font-medium uppercase tracking-[0.15em] text-secondary"
                        >
                            <span>Day {{ workout.day }} • {{ workout.label }}</span>
                            <span>{{ workout.finishedAt }}</span>
                        </div>

                        <!-- Middle cards -->
                        <div class="space-y-2">
                            <div class="rounded-2xl bg-layer px-3 py-2 text-sm">
                                <p class="font-semibold">Exercises</p>
                                <p class="text-secondary">{{ workout.exercisesCount }} total</p>
                            </div>
                            <div class="rounded-2xl bg-layer px-3 py-2 text-sm">
                                <p class="font-semibold">Sets</p>
                                <p class="text-secondary">{{ workout.setsCount }} sets</p>
                            </div>
                            <div class="rounded-2xl bg-layer px-3 py-2 text-sm">
                                <p class="font-semibold">Muscle groups</p>
                                <p class="text-secondary">{{ workout.muscleGroups.length }} groups</p>
                            </div>
                        </div>

                        <!-- Highlight block -->
                        <div class="mt-4 rounded-2xl border border-layer-light bg-main/70 px-4 py-3">
                            <p class="text-[11px] font-medium uppercase tracking-[0.18em] text-secondary">
                                Total volume
                            </p>
                            <p class="text-2xl font-semibold text-accent">
                                {{ workout.totalValue }}
                                <span class="ml-1 text-sm font-normal text-secondary">{{ workout.unit }}</span>
                            </p>
                        </div>

                        <!-- Bottom row: small text + pill button -->
                        <div class="mt-4 flex items-center justify-between text-[11px] text-secondary">
                            <span>Tap to view full workout</span>
                            <span class="rounded-full bg-accent/15 px-3 py-1 text-[11px] font-semibold text-accent">
                                Open
                            </span>
                        </div>
                    </Link>
                </UiBox>
            </div>

            <!-- Desktop -->
            <div class="relative hidden md:block">
                <div
                    class="absolute z-10 flex h-full w-full flex-col items-center justify-center gap-3 rounded-xl bg-main/85 text-center"
                    v-if="!dashboardStats.lastWorkouts"
                >
                    <Icon icon="mdi:dumbbell" class="h-10 w-10 text-accent" />
                    <h3 class="text-xl font-semibold">No Workouts Yet</h3>
                    <p class="max-w-[280px] text-sm text-secondary">
                        Your recent workouts will appear here once you start training.
                    </p>
                    <Link
                        :href="placeholderUrl"
                        class="rounded-lg bg-accent px-4 py-2 text-sm font-medium text-black transition hover:opacity-90"
                    >
                        Begin Today
                    </Link>
                </div>

                <div class="flex flex-col gap-3" :class="{ 'p-2 blur-sm': !dashboardStats.lastWorkouts }">
                    <UiBox v-for="workout in lastWorkouts" :key="workout.dayID" class="group hover:bg-layer-light">
                        <Link
                            :href="route('days.show', { mesocycle: workout.mesocycle, day: workout.dayID })"
                            class="flex cursor-pointer flex-col items-center justify-between gap-4 p-4 min-[450px]:flex-row"
                        >
                            <div class="flex min-w-32 items-center gap-2">
                                <div
                                    class="flex aspect-square h-12 w-12 items-center justify-center rounded-lg bg-accent text-xl font-bold text-white"
                                >
                                    {{ workout.day }}
                                </div>
                                <div>
                                    <p class="font-semibold">{{ workout.label }}</p>
                                    <p class="text-sm text-secondary">{{ workout.finishedAt }}</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-1 whitespace-nowrap text-sm text-secondary">
                                <div
                                    class="border-border-layer flex-1 rounded-md border border-layer-light bg-main px-2 py-1 group-hover:bg-layer"
                                >
                                    <span>{{ workout.exercisesCount }}</span>
                                    exercises
                                </div>
                                <div
                                    class="border-border-layer flex-1 rounded-md border border-layer-light bg-main px-2 py-1 group-hover:bg-layer"
                                >
                                    <span>{{ workout.setsCount }}</span>
                                    sets
                                </div>
                                <div
                                    class="border-border-layer flex-1 rounded-md border border-layer-light bg-main px-2 py-1 group-hover:bg-layer"
                                >
                                    <span>{{ workout.totalValue }} {{ workout.unit }}</span>
                                </div>
                                <div
                                    class="border-border-layer flex-1 rounded-md border border-layer-light bg-main px-2 py-1 group-hover:bg-layer"
                                >
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
                    <p class="max-w-[280px] text-sm text-secondary">
                        Record your workouts to see your best Deadlift, Bench Press, and Squat.
                    </p>
                    <Link
                        :href="placeholderUrl"
                        class="rounded-lg bg-accent px-4 py-2 text-sm font-medium text-black transition hover:opacity-90"
                    >
                        Start Training
                    </Link>
                </div>
                <div class="flex flex-wrap gap-5" :class="{ 'p-2 blur-sm': !dashboardStats.bestLifts }">
                    <UiBox
                        v-for="(lift, idx) in bestLifts"
                        :key="lift.exercise"
                        class="flex min-w-[200px] flex-1 flex-col items-center rounded-xl p-5"
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
import { computed, ref, watch } from "vue";

const tailwindColors = useTailwindColors();

const exerciseImages = {
    deadlift: new URL("@/assets/images/dashboard/deadlift.png", import.meta.url).href,
    bench: new URL("@/assets/images/dashboard/bench.png", import.meta.url).href,
    squat: new URL("@/assets/images/dashboard/squat.png", import.meta.url).href,
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
