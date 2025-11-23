<template>
    <div class="mx-auto my-2 flex max-w-[768px] flex-col gap-3">
        <UiBox class="flex flex-col">
            <ModalsExercises
                v-model="exercisesModal"
                :only-one-muscle-group="0"
                @select="(exerciseID: number) => addExercise(exerciseID)"
            />

            <div class="mb-2 flex items-center justify-between">
                <div class="flex flex-col">
                    <p class="text-sm text-secondary">{{ mesocycle.name }}</p>
                    <h3>WEEK {{ day.week }} DAY {{ day.day_order }}: {{ day.label }}</h3>
                </div>

                <div class="align-end flex items-center gap-3">
                    <div class="flex items-center gap-1 rounded bg-green px-2" v-if="isDayFinished">
                        <p>Completed</p>
                        <Icon icon="ep:success-filled" />
                    </div>
                    <Icon
                        icon="quill:calendar"
                        width="18px"
                        @click="showCalendar = !showCalendar"
                        class="cursor-pointer transition hover:text-secondary"
                    />

                    <UiDropdownMenu idx="1" left="-50px">
                        <template #header>
                            <div class="hover:cursor-pointer">
                                <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                            </div>
                        </template>

                        <template v-for="(section, index) in dropdownItems" :key="index" v-slot="slotProps">
                            <li class="flex !justify-center bg-layer">{{ section.section }}</li>
                            <li
                                v-for="(item, i) in section.items"
                                :key="i"
                                @click="
                                    item?.action ? item.action() : '';
                                    slotProps.toggle;
                                "
                            >
                                <Icon :icon="item.icon" />
                                {{ item.label }}
                            </li>
                        </template>
                    </UiDropdownMenu>
                </div>
            </div>

            <!-- ===== Calendar ===== -->
            <div class="flex justify-between gap-1" v-if="showCalendar">
                <div class="flex flex-1 flex-col items-center gap-1" v-for="(week, idx) in mesocycle.calendar">
                    <p>W {{ idx }}</p>
                    <Link
                        :href="route('days.show', { mesocycle: mesocycle.id, day: weekDay.id })"
                        class="flex w-full flex-col items-center justify-center rounded-sm border border-transparent bg-main px-2 py-1 text-center"
                        v-for="(weekDay, dayIdx) in week"
                        :class="[
                            { 'bg-orange-700': isActiveDay(weekDay.id) },
                            { 'border !border-border-green opacity-50': weekDay.finished_at },
                        ]"
                    >
                        <p>D{{ dayIdx + 1 }}</p>
                    </Link>
                </div>
            </div>
        </UiBox>

        <!-- ===== Errors ===== -->
        <UiErrors :errors="errors" />

        <!-- ===== Exercises ===== -->
        <UiBox class="flex flex-col gap-2" v-for="(dayExercise, exercise_idx) in day.day_exercises">
            <div class="flex items-center justify-between">
                <div class="rounded bg-orange-700 px-2">
                    {{ dayExercise.exercise.muscle_group.name }}
                </div>

                <div class="align-end flex items-center gap-4">
                    <a target="_blank" :href="`https://www.youtube.com/watch?v=${dayExercise.exercise.youtube_id}`">
                        <Icon icon="line-md:youtube" width="20px" />
                    </a>

                    <UiDropdownMenu :idx="exercise_idx" left="-50px">
                        <template #header>
                            <div class="hover:cursor-pointer">
                                <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                            </div>
                        </template>

                        <template v-slot="slotProps">
                            <li
                                v-for="(item, i) in exerciseDropdownItems"
                                :key="i"
                                :class="item.class"
                                @click="
                                    item.action(dayExercise.id);
                                    slotProps.toggle();
                                "
                            >
                                <Icon :icon="item.icon" />
                                {{ item.label }}
                            </li>
                        </template>
                    </UiDropdownMenu>
                </div>
            </div>

            <div class="flex flex-col">
                <h4>{{ dayExercise.exercise.name }}</h4>
                <p class="text-sm text-secondary">{{ dayExercise.exercise.exercise_type }}</p>
            </div>

            <div class="grid grid-cols-6 gap-5">
                <div
                    class="flex h-fit w-fit cursor-pointer items-center self-center rounded-full bg-input p-1"
                    @click="addSet(dayExercise.id)"
                >
                    <Icon icon="material-symbols:add" size="1rem" />
                </div>
                <div class="col-span-2 text-center">WEIGHT</div>
                <div class="col-span-2 text-center">REPS</div>
                <div class="justify-self-end">LOG</div>
            </div>

            <div class="grid grid-cols-6 gap-5" v-for="(set, set_idx) in dayExercise.sets">
                <div class="flex items-center justify-start">
                    <UiDropdownMenu :idx="set_idx" left="50px">
                        <template #header>
                            <div class="hover:cursor-pointer">
                                <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                            </div>
                        </template>

                        <li
                            v-for="(item, i) in setDropdownItems"
                            :key="i"
                            :class="item.class"
                            @click="item.action(dayExercise.id, set.id)"
                        >
                            <Icon :icon="item.icon" />
                            {{ item.label }}
                        </li>
                    </UiDropdownMenu>
                </div>

                <div class="col-span-2">
                    <InputText
                        :placeholder="set?.target_weight ?? mesocycle.unit"
                        v-model="set.weight"
                        class="w-full"
                        :id="exercise_idx + '-' + set_idx + '-weight'"
                        :disabled="isDayFinished"
                    />
                </div>
                <div class="col-span-2">
                    <InputText
                        :placeholder="set?.target_reps != null ? `${set.target_reps}` : '3 RIR'"
                        v-model="set.reps"
                        class="w-full"
                        :id="exercise_idx + '-' + set_idx + '-reps'"
                        :disabled="isDayFinished"
                    />
                </div>
                <div class="flex items-center justify-end">
                    <Checkbox
                        :checked="set.finished_at"
                        :value="set.finished_at"
                        v-model="set.status"
                        true-value="1"
                        false-value="0"
                        @change="updateSet(set, dayExercise.id)"
                        class="mr-2"
                        :id="exercise_idx + '-' + set_idx + '-status'"
                        :disabled="isDayFinished"
                    />
                </div>
            </div>
        </UiBox>

        <ButtonPrimary @click="toggleDay(day.id)" v-if="!isDayFinished">Finish Workout</ButtonPrimary>
        <ButtonSecondary @click="toggleDay(day.id)" v-else>Reactivate</ButtonSecondary>
    </div>
</template>
<script setup lang="ts">
import ButtonPrimary from "@/Components/Button/Primary.vue";
import ButtonSecondary from "@/Components/Button/Secondary.vue";
import Checkbox from "@/Components/Input/Checkbox.vue";
import InputText from "@/Components/Input/text.vue";
import ModalsExercises from "@/Components/Modals/Exercises.vue";
import UiBox from "@/Components/Ui/Box.vue";
import UiDropdownMenu from "@/Components/Ui/DropdownMenu.vue";
import UiErrors from "@/Components/Ui/Errors.vue";
import { Icon } from "@iconify/vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import { computed, onMounted, ref } from "vue";

const props = defineProps<{
    mesocycle: Mesocycle;
    errors?: object;
}>();

const day = computed<Day>(() => props.mesocycle.day);
const exercisesModal = ref<boolean>();
const showCalendar = ref<boolean>(true);
const isDayFinished = computed<boolean>(() => {
    return !!day.value.finished_at;
});

function isActiveDay(dayID: number) {
    const url = usePage().url.split("/");
    const length = url.length - 1;

    return Number(url[length]) === dayID;
}

onMounted(() => {});

async function removeExercise(dayExerciseID: number) {
    router.delete(route("dayExercise.destroy", { day: day.value.id, dayExercise: dayExerciseID }), {
        preserveState: "errors",
    });
}

async function updateSet(set: ExerciseSet, dayExerciseID: number) {
    if (!set.status) return;

    router.patch(
        route("sets.update", { dayExercise: dayExerciseID, set: set.id }),
        { ...set },
        {
            preserveState: true,
            preserveScroll: true,
            onError: () => (set.status = false),
        }
    );
}

async function addSet(dayExerciseID: number) {
    router.post(
        route("sets.store", { dayExercise: dayExerciseID }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
        }
    );
}

async function removeSet(dayExerciseID: number, setID: number) {
    router.delete(route("sets.destroy", { dayExercise: dayExerciseID, set: setID }), {
        preserveState: true,
        preserveScroll: true,
    });
}

// async function addComment() {
//     console.log('addig comment');
// }

async function toggleDay(dayID: number) {
    router.patch(route("days.toggle", { day: dayID }));
}

async function addExercise(exerciseID: number) {
    router.post(
        route("dayExercises.store", { day: day.value.id }),
        { exercise_id: exerciseID },
        { preserveState: false }
    );
}

function getPosition(dayExerciseID: number): number {
    return day.value.day_exercises.findIndex((dayEx) => dayEx.id == dayExerciseID);
}

async function moveUp(dayExerciseID: number): Promise<void> {
    const position = getPosition(dayExerciseID);

    if (position === -1 || position === 0) return;

    swap(day.value.day_exercises, position, position - 1);
}

async function moveDown(dayExerciseID: number) {
    const position = getPosition(dayExerciseID);

    if (position === -1 || position === day.value.day_exercises.length - 1) return;

    swap(day.value.day_exercises, position, position + 1);

    updateOrder();
}

function swap<T>(arr: T[], from: number, to: number) {
    [arr[from], arr[to]] = [arr[to], arr[from]];
}

async function updateOrder() {
    const order = day.value.day_exercises.map((ex) => ex.id);

    router.patch(route("dayExercises.reorder", { day: day.value.id }), { order });
}

function addExerciseModal(): void {
    exercisesModal.value = true;
}

const dropdownItems = [
    {
        section: "Mesocycle",
        items: [
            { icon: "clarity:note-line", label: "View Notes" },
            { icon: "tabler:italic", label: "Rename" },
            { icon: "bi:graph-up-arrow", label: "Progressions" },
            { icon: "carbon:book", label: "Summary" },
            { icon: "carbon:stop-outline", label: "End" },
        ],
    },
    {
        section: "Workout",
        items: [
            { icon: "icon-park-outline:write", label: "New Note" },
            { icon: "tabler:italic", label: "Relabel" },
            {
                icon: "material-symbols:add",
                label: "Add Exercise",
                action: () => addExerciseModal(),
            },
            { icon: "hugeicons:weight-scale", label: "Bodyweight" },
            { icon: "carbon:reset", label: "Reset" },
            { icon: "carbon:stop-outline", label: "End" },
        ],
    },
];

const exerciseDropdownItems = [
    {
        icon: "icon-park-outline:write",
        label: "New Note",
        action: () => console.log("New Note clicked"),
    },
    {
        icon: "mdi:arrow-up",
        label: "Move Up",
        action: (dayExerciseID: number) => moveUp(dayExerciseID),
    },
    {
        icon: "mdi:arrow-down",
        label: "Move Down",
        action: (dayExerciseID: number) => moveDown(dayExerciseID),
    },
    {
        icon: "ph:swap",
        label: "Replace",
        action: () => console.log("Replace clicked"),
    },
    {
        icon: "material-symbols:add",
        label: "Add Set",
        action: (dayExerciseID: number) => addSet(dayExerciseID),
    },
    {
        icon: "ix:skip",
        label: "Skip",
        action: () => console.log("Skip clicked"),
    },
    {
        icon: "material-symbols:delete-outline",
        label: "Delete",
        class: "!text-red",
        action: (dayExerciseID: number) => removeExercise(dayExerciseID),
    },
];

const setDropdownItems = [
    {
        icon: "material-symbols:delete-outline",
        label: "Change Type",
        action: () => console.log("Should Open Modal"),
    },

    {
        icon: "material-symbols:delete-outline",
        label: "Delete",
        class: "!text-red",
        action: (dayExerciseID: number, setID: number) => removeSet(dayExerciseID, setID),
    },
];
</script>
