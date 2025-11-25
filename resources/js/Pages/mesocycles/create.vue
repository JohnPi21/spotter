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
                    class="flex flex-col gap-2 bg-layer-light"
                    v-for="(exercise, exerciseIDx) in day.exercises"
                    :key="exerciseIDx"
                >
                    <div class="flex-center flex justify-between">
                        <div class="rounded bg-orange-700 px-2">
                            {{ exerciseStore.muscleGroups[exercise.muscleGroup]?.name }}
                        </div>
                        <Icon
                            icon="material-symbols-light:delete-outline"
                            width="22px"
                            class="cursor-pointer transition hover:text-red"
                            @click="removeExercise(dayIdx, exerciseIDx)"
                        />
                    </div>

                    <ButtonSecondary
                        @click="openExerciseModal(exercise)"
                        :class="{ 'bg-layer !text-gray-400': exercise.exerciseID }"
                    >
                        <span v-if="!exercise.exerciseID">Select Exercise</span>
                        <span v-else>{{ exerciseStore?.exercises[exercise.exerciseID]?.name }}</span>
                    </ButtonSecondary>

                    <InputError :message="errors[`days.${dayIdx}.exercises.${exerciseIDx}.exerciseID`]" />
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

        <Modal :show="showMuscleModal" @close="showMuscleModal = false">
            <ModalHeader title="Choose muscle group" @close="showMuscleModal = false" />
            <ul>
                <li
                    v-for="(muscle, idx) in exerciseStore.muscleGroups"
                    :key="idx"
                    @click="addMuscleGroup(idx)"
                    class="flex cursor-pointer items-center justify-between border-b border-main-border p-2 text-secondary transition last:border-b-0 hover:bg-layer-light hover:text-primary"
                >
                    {{ muscle.name }}
                </li>
            </ul>
        </Modal>
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
import Modal from "@/Components/Modal.vue";
import ModalExercise from "@/Components/Modals/Exercises.vue";
import ModalHeader from "@/Components/Modals/Header.vue";
import UiBox from "@/Components/Ui/Box.vue";
import { useExerciseStore } from "@/stores/exerciseStore";
import { Icon } from "@iconify/vue";
import { useForm } from "@inertiajs/vue3";
import { reactive, ref } from "vue";

defineProps<{ errors: { [key: string]: string } }>();

const meso = ref<MesoForm>({
    name: "",
    unit: "kg",
    weeksDuration: 4,
});

const exerciseStore = useExerciseStore();
const showMuscleModal = ref<boolean>(false);
const loading = ref<boolean>(false);
const days: DayForm[] = reactive([]);

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
    days[selectedDay.value].exercises.push({ muscleGroup: muscleGroupId, exerciseID: 0 });
    showMuscleModal.value = false;
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
type DayForm = {
    label: string;
    exercises: {
        muscleGroup: number;
        exerciseID: number;
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
