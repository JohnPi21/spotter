<template>
    <div class="flex justify-between items-center mb-4 mt-2">
        <div class="flex flex-col">
            <InputText v-model="meso.name" placeholder="Untitled Meso" />
            <InputError :message="errors['meso.name']" />
        </div>
        <ButtonPrimary @click="submit">Create Meso</ButtonPrimary>
    </div>

    <!-- <UiErrors :errors="errors" /> -->
    <InputError :message="errors['days']" />
    <div class="flex gap-2 flex-wrap">

        <UiBox class="flex flex-col gap-2 h-fit flex-1 lg:max-w-[350px]" v-for="(day, dayIdx) in days" :key="dayIdx">
            <div class="flex items-center justify-between gap-3">
                <InputText v-model="day.label" />

                <Icon icon="material-symbols-light:delete-outline" width="22px"
                    class="cursor-pointer hover:text-red transition" @click="removeDay(dayIdx)" />
            </div>
            <InputError :message="errors[`days.${dayIdx}.label`]" />

            <template v-if="day.exercises.length > 0">
                <UiBox class="bg-layer-light flex flex-col gap-2" v-for="(exercise, exerciseIdx) in day.exercises">
                    <div class="flex flex-center justify-between">
                        <div class="bg-orange-700 px-2 rounded">{{
                            exerciseStore.muscleGroups[exercise.muscleGroup]?.name
                        }}
                        </div>
                        <Icon icon="material-symbols-light:delete-outline" width="22px"
                            class="cursor-pointer hover:text-red transition"
                            @click="removeExercise(dayIdx, exerciseIdx)" />
                    </div>

                    <ButtonSecondary @click="showExerciseModal = true"
                        :class="{ 'bg-layer-light': exercise.exerciseId }">
                        <span v-if="!exercise.exerciseId">Select Exercise</span>
                        <span v-else>{{ exerciseStore?.exercises[exercise.exerciseId]?.name }}</span>
                    </ButtonSecondary>

                    <ModalExercise v-model="showExerciseModal" :only-one-muscle-group="exercise.muscleGroup"
                        @select="(exerciseID: number) => setExerciseID(exerciseID, exercise)" />

                    <InputError :message="errors[`days.${dayIdx}.exercises.${exerciseIdx}.exerciseId`]" />
                </UiBox>
            </template>

            <ButtonSecondary @click="selectMuscleGroupDay(dayIdx)">
                <Icon icon="ic:baseline-plus" width="21px" />
                Add Muscle Group
            </ButtonSecondary>
            <InputError :message="errors[`days.${dayIdx}.exercises`]" />
        </UiBox>


        <Modal :show="showMuscleModal" @close="showMuscleModal = false">
            <ModalHeader title="Choose muscle group" @close="showMuscleModal = false" />
            <ul>
                <li v-for="(muscle, idx) in exerciseStore.muscleGroups" :key="idx" @click="addMuscleGroup(idx)"
                    class="p-2 border-b border-main-border cursor-pointer text-secondary hover:text-primary transition hover:bg-layer-light last:border-b-0 flex items-center justify-between">
                    {{ muscle.name }}
                </li>
            </ul>
        </Modal>
        <ButtonPrimary class="h-fit min-w-44 w-full" @click="addDay()" v-if="days.length < 7">
            <Icon icon="ic:baseline-plus" width="21px" />
            Add Day
        </ButtonPrimary>

    </div>

</template>
<script setup lang="ts">
    import { ref, reactive } from 'vue';
    import InputText from '@/Components/Input/text.vue';
    import InputDropdown from "@/Components/Input/Dropdown.vue"
    import UiBox from '@/Components/Ui/Box.vue';
    import UiErrors from '@/Components/Ui/Errors.vue';
    import ButtonPrimary from '@/Components/Button/Primary.vue';
    import ButtonSecondary from '@/Components/Button/Secondary.vue';
    import { Icon } from '@iconify/vue';
    import Modal from '@/Components/Modal.vue';
    import ModalHeader from '@/Components/Modals/Header.vue'
    import { useForm } from '@inertiajs/vue3';
    import InputError from '@/Components/Input/InputError.vue';
    import ModalExercise from '@/Components/Modals/Exercises.vue'
    import { useExerciseStore } from '@/stores/exerciseStore';

    const props = defineProps<{
        errors: { [key: string]: string },
    }>();

    const meso = ref<MesoForm>({
        name: '',
        unit: 'kg',
        weeksDuration: 4
    });

    const exerciseStore = useExerciseStore();
    const showMuscleModal = ref<boolean>(false);
    const showExerciseModal = ref<boolean>(false);

    const days: DayForm[] = reactive([])

    function addDay() {
        days.push({
            label: String('Day ' + (days.length + 1)),
            exercises: [],
        })
    }

    function removeDay(dayId: number) {
        days.splice(dayId, 1);
    }

    function removeExercise(dayId: number, exerciseId: number) {
        days[dayId].exercises.splice(exerciseId, 1);
    }

    const selectedDay = ref<number>(0);

    function selectMuscleGroupDay(dayId: number) {
        selectedDay.value = dayId;
        showMuscleModal.value = true;
    }

    function addMuscleGroup(muscleGroupId: number) {
        days[selectedDay.value].exercises.push({ muscleGroup: muscleGroupId, exerciseId: 0 });
        showMuscleModal.value = false;
    }

    function submit() {
        const mesoForm = useForm({ 'days': [...days], 'meso': { ...meso.value } })

        mesoForm.post('/mesocycles')
    }


    function setExerciseID(id: number, exercise: any) {
        exercise.exerciseId = id;
        showExerciseModal.value = false;
    }

    type DayForm = {
        label: string,
        exercises: {
            muscleGroup: number,
            exerciseId: number,
        }[];
    };

    type MesoForm = {
        name: string,
        unit: string,
        weeksDuration: number,
    }
</script>