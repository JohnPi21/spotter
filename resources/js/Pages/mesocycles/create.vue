<template>
    <div class="flex justify-between items-center mb-4">
        <div class="flex flex-col">
            <InputText v-model="meso.name" placeholder="Untitled Meso" />
            <InputError :message="errors['meso.name']"/>
        </div>
        <ButtonPrimary @click="submit">Create Meso</ButtonPrimary>
    </div>

    <!-- <UiErrors :errors="errors" /> -->
     <InputError :message="errors['days']"/>
    <div class="flex gap-2 flex-wrap">
        <UiBox class="flex flex-col gap-2 h-fit" v-for="(day, dayIdx) in days" :key="dayIdx">
            <div class="flex items-center justify-between gap-3">
                <InputText v-model="day.label" />

                <Icon icon="material-symbols-light:delete-outline" width="22px" class="cursor-pointer hover:text-red transition" @click="removeDay(dayIdx)" />
            </div>
            <InputError :message="errors[`days.${dayIdx}.label`]"/>

            <template v-if="day.exercises.length > 0">
                <UiBox class="bg-layer-light flex flex-col gap-2" v-for="(exercise, exerciseIdx) in day.exercises">
                    <div class="flex flex-center justify-between">
                        <div class="bg-orange-700 px-2 rounded">{{ getMuscleGroup(exercise.muscleGroup) }}</div>
                        <Icon icon="material-symbols-light:delete-outline" width="22px"
                            class="cursor-pointer hover:text-red transition" @click="removeExercise(dayIdx, exerciseIdx)" />
                    </div>
                    <InputDropdown :options="getMuscleList(exercise.muscleGroup)" v-model="exercise.exerciseId" :selected="exercise.exerciseId" :filter="true" />
                    <InputError :message="errors[`days.${dayIdx}.exercises.${exerciseIdx}.exerciseId`]"/>
                </UiBox>
            </template>

            <ButtonSecondary @click="selectMuscleGroupDay(dayIdx)">
                <Icon icon="ic:baseline-plus" width="21px" />
                Add Muscle Group
            </ButtonSecondary>
            <InputError :message="errors[`days.${dayIdx}.exercises`]"/>
        </UiBox>

        <Modal :show="isModalOpen" @close="isModalOpen = false">
            <ul>
                <li v-for="(muscle, idx) in props.muscleGroups" :key="idx" @click="addMuscleGroup(idx)"
                    class="p-2 border-b border-main-border cursor-pointer text-secondary hover:text-primary transition hover:bg-layer-light last:border-b-0 flex items-center justify-between">
                    {{ muscle }}
                </li>
            </ul>
        </Modal>
        <ButtonSecondary class="h-fit min-w-44" @click="addDay()" v-if="days.length < 7">
            <Icon icon="ic:baseline-plus" width="21px" />
            Add Day
        </ButtonSecondary>

    </div>

</template>
<script setup lang="ts">
    // Check task list spotter (firts one)
    import { ref, reactive } from 'vue';
    import InputText from '@/Components/Input/text.vue';
    import InputDropdown from "@/Components/Input/Dropdown.vue"
    import UiBox from '@/Components/Ui/Box.vue';
    import UiErrors from '@/Components/Ui/Errors.vue';
    import ButtonPrimary from '@/Components/Button/Primary.vue';
    import ButtonSecondary from '@/Components/Button/Secondary.vue';
    import { Icon } from '@iconify/vue';
    import Modal from '@/Components/Modal.vue';
    import { useForm } from '@inertiajs/vue3';
    import InputError from '@/Components/InputError.vue';

    const props = defineProps<{
        errors: {[key: string] : string},
        exercises: Exercise[],
        muscleGroups: Record<number, string>,
        exerciseDropdown: MuscleGroups,
    }>();

    const meso = ref({
        name: '',
        unit: 'kg',
        weeksDuration: 4
    })
    const isModalOpen = ref(false);

    const days: DayForm[] = reactive([])

    function getMuscleGroup(id: number|null) {
        if(id == null) return;

        return props.muscleGroups[id]
    }

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
        isModalOpen.value = true;
    }

    function addMuscleGroup(muscleGroupId: number) {
        days[selectedDay.value].exercises.push({ muscleGroup: muscleGroupId, exerciseId: null });
        isModalOpen.value = false;
    }

    function submit() {
        const mesoForm = useForm({ 'days': [...days], 'meso': { ...meso.value } })

        mesoForm.post('/mesocycles')
    }

    function getMuscleList(id: number|null){
        if(id === null || id === undefined) return [];
        return props.exerciseDropdown[id];
    }

    type DayForm = {
        label: string,
        exercises: {
            muscleGroup: number|null,
            exerciseId: number|null,
        }[];
    };
</script>