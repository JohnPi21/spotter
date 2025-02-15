<template>
    <div class="flex justify-between items-center mb-4">
        <div class="flex flex-col">
            <InputText v-model="meso.name" placeholder="Untitled Meso" />
            <p class="text-secondary text-sm">Generated from Template</p>
        </div>
        <ButtonPrimary @click="submit">Create Meso</ButtonPrimary>
    </div>

    <UiErrors :errors="errors" />
    <!-- <ul class="flex flex-col gap-2 my-2" v-if="errors && Object.keys(errors).length > 0">
        <li class="bg-red rounded-sm p-2 flex align-center gap-2" v-for="(error, field) in errors">
            <span class="flex items-center">
                <Icon icon="ix:error-filled" size="20px" />
            </span>
            <span>
                {{ field }} : {{ error }}
            </span>
        </li>
    </ul> -->

    <!-- {{ errors }} -->
    <div class="flex gap-2 flex-wrap">
        <UiBox class="flex flex-col gap-2 h-fit" v-for="(day, dayIdx) in days" :key="dayIdx">
            <div class="flex items-center justify-between gap-3">
                <InputText v-model="day.label" />
                <Icon icon="material-symbols-light:delete-outline" width="22px"
                    class="cursor-pointer hover:text-red transition" @click="removeDay(dayID)" />
            </div>
            <UiBox class="bg-layer-light flex flex-col gap-2" v-for="(exercise, exerciseIdx) in day.exercises">
                <div class="flex flex-center justify-between">
                    <div class="bg-orange-700 px-2 rounded">{{ getMuscleGroup(exercise.muscleGroup) }}</div>
                    <Icon icon="material-symbols-light:delete-outline" width="22px"
                        class="cursor-pointer hover:text-red transition" @click="removeExercise(dayIdx, exerciseIdx)" />
                </div>
                <InputDropdown :options="exerciseDropdown[exercise.muscleGroup]" v-model="exercise.exerciseId"
                    :selected="exercise.exerciseId" :filter="true" />
            </UiBox>

            <ButtonSecondary @click="openMuscleGroupModal(dayIdx)">
                <Icon icon="ic:baseline-plus" width="21px" />
                Add Muscle Group
            </ButtonSecondary>
        </UiBox>
        <ButtonSecondary class="h-fit min-w-44" @click="addDay()" v-if="days.length < 7">
            <Icon icon="ic:baseline-plus" width="21px" />
            Add Day
        </ButtonSecondary>
    </div>

</template>
<script setup>
    // Check task list spotter (firts one)
    import { ref, reactive, onMounted } from 'vue';
    import InputText from '@components/Input/text.vue';
    import InputDropdown from "@components/Input/Dropdown.vue"
    import UiBox from '@components/Ui/Box.vue';
    import UiErrors from '@/Components/Ui/Errors.vue';
    import ButtonPrimary from '@components/Button/Primary.vue';
    import ButtonSecondary from '@components/Button/Secondary.vue';
    import { Icon } from '@iconify/vue';
    import { useModalStore } from '@stores/modalStore';
    import { useForm } from '@inertiajs/vue3';

    const props = defineProps({
        errors: Object,
        exercises: Array,
        muscleGroups: Object,
        exerciseDropdown: Object,
    })

    const modalStore = useModalStore();

    const meso = ref({
        name: '',
        unit: 'kg',
        weeksDuration: 4
    })

    const days = reactive([])

    function getMuscleGroup(id) {
        return props.muscleGroups[id]
    }

    function addDay() {
        days.push({
            label: 'Day ' + (parseInt(days.length) + 1),
            exercises: []
        })
    }

    function removeDay(dayId) {
        days.splice(dayId, 1);
    }

    function removeExercise(dayId, exerciseId) {
        days[dayId].exercises.splice(exerciseId, 1);
    }

    function openMuscleGroupModal(dayId) {
        modalStore.openModal('MuscleGroups', props.muscleGroups, (selectedMuscleGroup) => {
            addMuscleGroup(selectedMuscleGroup, dayId)
        });
    }

    function addMuscleGroup(selectedMuscleGroup, dayId) {
        days[dayId].exercises.push(
            { muscleGroup: selectedMuscleGroup, exerciseId: null }
        );
    }

    function submit() {
        const mesoForm = useForm({ 'days': [...days], 'meso': { ...meso.value } })

        mesoForm.post('/mesocycles')
    }

</script>