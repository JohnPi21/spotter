<template>
    <div class="mb-4 mt-2 flex items-center justify-between">
        <div class="flex flex-col">
            <InputText v-model="meso.name" placeholder="Untitled Meso" />
            <InputError :message="errors['name']" />
        </div>
        <ButtonPrimary :disabled="loading" @click="submit">Create Mesocycle</ButtonPrimary>
    </div>

    <!-- <UiErrors :errors="errors" /> -->
    <InputError :message="errors['days']" />

    <div class="flex flex-wrap gap-2" v-if="days.length > 0">
        <UiBox class="flex h-fit flex-1 flex-col gap-2 lg:max-w-[350px]" v-for="(day, dayIdx) in days" :key="dayIdx">
            <div class="flex items-center justify-between gap-3">
                <InputText v-model="day.label" />

                <Icon
                    icon="material-symbols-light:delete-outline"
                    width="22px"
                    class="cursor-pointer transition hover:text-red"
                    @click="removeDay(dayIdx)"
                />
            </div>
            <InputError :message="errors[`days.${dayIdx}.label`]" />

            <template v-if="day.exercises.length > 0">
                <UiBox
                    class="flex flex-col gap-3 bg-layer-light"
                    v-for="(exercise, exerciseIDx) in day.exercises"
                    :key="exerciseIDx"
                >
                    <!-- Muscle group + delete -->
                    <div class="flex items-center justify-between">
                        <span class="rounded bg-orange-700 px-2 text-xs">
                            {{ exerciseStore.muscleGroups[exercise.muscleGroup]?.name }}
                        </span>
                        <Icon
                            icon="material-symbols-light:delete-outline"
                            width="20px"
                            class="cursor-pointer text-gray-500 transition hover:text-red"
                            @click="removeExercise(dayIdx, exerciseIDx)"
                        />
                    </div>

                    <!-- Exercise selector -->
                    <div class="flex flex-col gap-1">
                        <ButtonSecondary
                            @click="openExerciseModal(exercise)"
                            :class="{ 'bg-layer !text-gray-400': exercise.exerciseID }"
                            class="w-full"
                        >
                            <span v-if="!exercise.exerciseID">Select Exercise</span>
                            <span v-else>{{ exerciseStore?.exercises[exercise.exerciseID]?.name }}</span>
                        </ButtonSecondary>
                        <InputError :message="errors[`days.${dayIdx}.exercises.${exerciseIDx}.exerciseID`]" />
                    </div>

                    <!-- Configure toggle -->
                    <button
                        type="button"
                        class="flex items-center gap-1 self-start text-xs text-gray-500 transition hover:text-gray-300"
                        @click="toggleConfig(exercise)"
                    >
                        <Icon
                            icon="ic:baseline-chevron-right"
                            width="14px"
                            class="transition-transform"
                            :class="{ 'rotate-90': openConfigs.has(exercise) }"
                        />
                        Configure
                    </button>

                    <!-- Collapsible config: 1RM + sets -->
                    <template v-if="openConfigs.has(exercise)">
                        <div class="flex flex-col gap-1" v-if="exercise.sets.length > 0">
                            <div class="grid grid-cols-[18px_1fr_1fr_18px] gap-x-2 text-center text-xs text-gray-500">
                                <span></span>
                                <span>Reps</span>
                                <span>RIR</span>
                                <span></span>
                            </div>
                            <div
                                v-for="(set, setIdx) in exercise.sets"
                                :key="setIdx"
                                class="grid grid-cols-[18px_1fr_1fr_18px] items-center gap-x-2"
                            >
                                <span class="text-center text-xs text-gray-500">{{ setIdx + 1 }}</span>
                                <div class="flex items-center gap-1">
                                    <InputText
                                        type="number"
                                        min="1"
                                        v-model="set.minReps"
                                        placeholder="1"
                                        class="w-full text-center text-sm"
                                    />
                                    <span class="text-xs text-gray-500">–</span>
                                    <InputText
                                        type="number"
                                        min="1"
                                        v-model="set.maxReps"
                                        placeholder="12"
                                        class="w-full text-center text-sm"
                                    />
                                </div>
                                <div class="flex items-center gap-1">
                                    <InputText
                                        type="number"
                                        min="0"
                                        v-model="set.minRir"
                                        placeholder="1"
                                        class="w-full text-center text-sm"
                                    />
                                    <span class="text-xs text-gray-500">–</span>
                                    <InputText
                                        type="number"
                                        min="0"
                                        v-model="set.maxRir"
                                        placeholder="3"
                                        class="w-full text-center text-sm"
                                    />
                                </div>
                                <Icon
                                    icon="material-symbols-light:delete-outline"
                                    width="16px"
                                    class="cursor-pointer text-gray-500 transition hover:text-red"
                                    @click="removeSet(dayIdx, exerciseIDx, setIdx)"
                                />
                            </div>
                        </div>

                        <ButtonSecondary class="text-xs" @click="addSet(dayIdx, exerciseIDx)">
                            <Icon icon="ic:baseline-plus" width="16px" />
                            Add Set
                        </ButtonSecondary>

                        <div class="flex flex-col items-center gap-1">
                            <div class="flex items-center gap-2">
                                <Icon icon="mdi:arm-flex" width="18px" class="text-accent opacity-75" />
                                <InputText
                                    type="number"
                                    min="0"
                                    step="0.001"
                                    v-model="exercise.oneRepMax"
                                    :placeholder="`1RM`"
                                    class="w-20 text-center text-sm"
                                />
                                <Icon icon="mdi:arm-flex" width="18px" class="scale-x-[-1] text-accent opacity-75" />
                            </div>
                            <InputError :message="errors[`days.${dayIdx}.exercises.${exerciseIDx}.oneRepMax`]" />
                        </div>
                    </template>
                </UiBox>
            </template>

            <ModalExercise
                v-model="exerciseModal.show"
                :only-one-muscle-group="exerciseModal?.exercise?.muscleGroup"
                :pre-selected="exerciseModal?.exercise?.exerciseID"
                @select="(exerciseID: number) => setExerciseID(exerciseID)"
            />

            <ButtonSecondary @click="selectMuscleGroupDay(dayIdx)">
                <Icon icon="ic:baseline-plus" width="21px" />
                Add Muscle Group
            </ButtonSecondary>
            <InputError :message="errors[`days.${dayIdx}.exercises`]" />
        </UiBox>

        <ModalMuscleGroups
            v-model="showMuscleModal"
            :muscle-groups="exerciseStore.muscleGroups"
            @select="addMuscleGroup"
        />
    </div>

    <div class="flex items-center justify-center" v-else>
        <ButtonSecondary class="h-fit min-w-40" @click="addDay()" v-if="days.length < 7">
            <Icon icon="ic:baseline-plus" width="21px" />
            Add Day
        </ButtonSecondary>
    </div>

    <UiBox class="mt-5 flex flex-col-reverse items-center justify-between !gap-4 md:flex-row" v-if="days.length > 0">
        <div class="flex w-full flex-col md:w-fit">
            <InputLabel value="Number of Weeks" />
            <InputRange :min="3" :max="12" v-model="meso.weeksDuration" />
            <InputError :message="errors['weeksDuration']" />
        </div>

        <ButtonSecondary class="h-fit w-full min-w-40 md:w-fit" @click="addDay()" v-if="days.length < 7">
            <Icon icon="ic:baseline-plus" width="21px" />
            Add Day
        </ButtonSecondary>
    </UiBox>
</template>
<script setup lang="ts">
import ButtonPrimary from "@/Components/Button/Primary.vue";
import ButtonSecondary from "@/Components/Button/Secondary.vue";
import InputError from "@/Components/Input/InputError.vue";
import InputLabel from "@/Components/Input/InputLabel.vue";
import InputRange from "@/Components/Input/Range.vue";
import InputText from "@/Components/Input/Text.vue";
import ModalExercise from "@/Components/Modals/Exercises.vue";
import ModalMuscleGroups from "@/Components/Modals/MuscleGroups.vue";
import UiBox from "@/Components/Ui/Box.vue";
import { useExerciseStore } from "@/stores/exerciseStore";
import { Icon } from "@iconify/vue";
import { useForm } from "@inertiajs/vue3";
import { reactive, ref } from "vue";

const props = defineProps<{
    errors: Record<string, string>;
    name?: string;
    unit?: string;
    weeksDuration?: number;
    days?: DayForm[];
}>();

const meso = ref<MesoForm>({
    name: props.name ?? "",
    unit: props.unit ?? "kg",
    weeksDuration: props.weeksDuration ?? 4,
});

const exerciseStore = useExerciseStore();
const showMuscleModal = ref<boolean>(false);
const loading = ref<boolean>(false);
const days = reactive<DayForm[]>(props.days ?? []);
const openConfigs = reactive(new Set<object>());

function toggleConfig(exercise: object) {
    if (openConfigs.has(exercise)) {
        openConfigs.delete(exercise);
    } else {
        openConfigs.add(exercise);
    }
}

function addDay() {
    days.push({
        label: String("Day " + (days.length + 1)),
        exercises: [],
    });
}

function removeDay(dayId: number) {
    days.splice(dayId, 1);
}

function removeExercise(dayId: number, exerciseID: number) {
    days[dayId].exercises.splice(exerciseID, 1);
}

const selectedDay = ref<number>(0);

function selectMuscleGroupDay(dayId: number) {
    selectedDay.value = dayId;
    showMuscleModal.value = true;
}

function addMuscleGroup(muscleGroupId: number) {
    days[selectedDay.value].exercises.push({ muscleGroup: muscleGroupId, exerciseID: 0, oneRepMax: null, sets: [] });
    showMuscleModal.value = false;
}

function addSet(dayIdx: number, exerciseIdx: number) {
    days[dayIdx].exercises[exerciseIdx].sets.push({ minReps: null, maxReps: null, minRir: null, maxRir: null });
}

function removeSet(dayIdx: number, exerciseIdx: number, setIdx: number) {
    days[dayIdx].exercises[exerciseIdx].sets.splice(setIdx, 1);
}

function submit() {
    const mesoForm = useForm({ days: [...days], ...meso.value });

    mesoForm.post("/mesocycles", {
        onStart: () => (loading.value = true),
        onFinish: () => (loading.value = false),
    });
}

// ========= EXERCISE MODAL =============
const exerciseModal = ref<{
    show: boolean;
    exercise: ExerciseForm;
}>({
    show: false,
    exercise: {
        exerciseID: 0,
        muscleGroup: 0,
    },
});

function setExerciseID(id: number) {
    exerciseModal.value.exercise.exerciseID = id;
    exerciseModal.value.show = false;
    exerciseModal.value.exercise = {
        exerciseID: 0,
        muscleGroup: 0,
    };
}

function openExerciseModal(exercise: ExerciseForm) {
    exerciseModal.value.exercise = exercise;
    exerciseModal.value.show = true;
}

// ============= TS TYPES =============
type SetForm = {
    minReps: number | null;
    maxReps: number | null;
    minRir: number | null;
    maxRir: number | null;
};

type DayForm = {
    label: string;
    exercises: {
        muscleGroup: number;
        exerciseID: number;
        oneRepMax: number | null;
        sets: SetForm[];
    }[];
};

type MesoForm = {
    name: string;
    unit: string;
    weeksDuration: number;
};

type ExerciseForm = {
    muscleGroup: MuscleGroup["id"];
    exerciseID: Exercise["id"];
};
</script>

<style lang="css" scoped>
:deep(.slider) {
    @apply w-full md:w-fit;
}
</style>
