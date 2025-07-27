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
                    <span class="text-sm text-secondary">Spotted for: 2 weeks</span>
                </div>
            </div>
        </UiBox>

        <!-- Best Lifts Section -->
        <div>
            <div class="mb-2 flex items-center justify-between">
                <h2 class="text-2xl">Best Lifts</h2>
                <div class="text-sm text-secondary">By Weight | By Volume</div>
            </div>

            <div class="flex flex-wrap gap-5">
                <UiBox
                    v-for="lift in bestLifts"
                    :key="lift.name"
                    class="flex flex-1 flex-col items-center rounded-xl p-5"
                >
                    <div class="mb-2 rounded-xl bg-slate-200 p-2">
                        <img :src="exerciseImages[lift.image]" width="60px" />
                    </div>
                    <div class="text-xl font-extrabold text-accent">
                        <span>{{ lift.weight }}</span>
                        <span>{{ lift.unit }}</span>
                    </div>
                    <p class="text-xl font-normal">Best {{ lift.name }}</p>
                </UiBox>
            </div>
        </div>

        <!-- Activity Graph Placeholder -->
        <div>
            <h2 class="mb-2 text-2xl">Activity</h2>
            <UiBox class="flex h-48 items-center justify-center">
                <p class="text-secondary">[ Graph will be displayed here ]</p>
            </UiBox>
        </div>

        <!-- Last Workouts Section -->
        <div>
            <h2 class="mb-2 text-2xl">Last Workouts</h2>
            <div class="flex flex-col gap-3">
                <UiBox class="flex items-center justify-between p-4" v-for="box in 3">
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-lg bg-accent text-xl font-bold text-white"
                        >
                            25
                        </div>
                        <div>
                            <p class="font-semibold">Push Day</p>
                            <p class="text-sm text-secondary">2 days ago</p>
                        </div>
                    </div>
                    <div class="flex gap-1 text-sm text-secondary">
                        <div>
                            <span>8</span>
                            exercises |
                        </div>
                        <div>
                            <span>25</span>
                            sets |
                        </div>
                        <div>
                            <span>1200kg</span>
                            |
                        </div>
                        <div>
                            <span>4</span>
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
import UiBox from "@/Components/Ui/Box.vue";
import { Icon } from "@iconify/vue";
import { ref } from "vue";

const exerciseImages = {
    deadlift: new URL("@/assets/images/deadlift.png", import.meta.url).href,
    bench: new URL("@/assets/images/bench.png", import.meta.url).href,
    squat: new URL("@/assets/images/squat.png", import.meta.url).href,
};

type ExerciseKey = keyof typeof exerciseImages;

type BestLift = {
    name: string;
    weight: number;
    unit: string;
    image: ExerciseKey;
};

type Activity = {};

const props = defineProps<{
    bestLifts: BestLift[];
    activity: Activity;
}>();

const bestLifts = ref<BestLift[]>([
    { name: "Deadlift", weight: 120, unit: "kg", image: "deadlift" },
    { name: "Squat", weight: 100, unit: "kg", image: "squat" },
    { name: "Bench", weight: 80, unit: "kg", image: "bench" },
]);
</script>
