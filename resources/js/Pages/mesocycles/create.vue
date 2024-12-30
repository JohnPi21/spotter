<template>
    <div class="flex justify-between items-center mb-4">
        <div class="flex flex-col">
            <InputText v-model="meso.name" />
            <p class="text-secondary text-sm">Generated from Template</p>
        </div>
        <ButtonPrimary @click="submit">Create Meso</ButtonPrimary>
    </div>

    <div class="flex gap-2 flex-wrap">
        <UiBox class="flex flex-col gap-2 h-fit" v-for="(day, dayIdx) in days" :key="dayIdx">
            <div class="flex items-center justify-between gap-3">
                <InputText v-model="day.label" />
                <Icon icon="material-symbols-light:delete-outline" width="22px"
                    class="cursor-pointer hover:text-red transition" @click="removeDay(dayID)" />
            </div>
            <UiBox class="bg-layer-light flex flex-col gap-2" v-for="(exercise, exerciseIdx) in day.exercises">
                <div class="flex flex-center justify-between">
                    <div class="bg-orange-300 px-2 rounded">{{ getMuscleGroup(exercise.muscleGroup) }}</div>
                    <Icon icon="material-symbols-light:delete-outline" width="22px"
                        class="cursor-pointer hover:text-red transition" @click="removeExercise(dayIdx, exerciseIdx)" />
                </div>
                <InputDropdown :options="exerciseDropdown[exercise.muscleGroup]" :selected="exercise.exerciseId"
                    :filter="true" />
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
    import ButtonPrimary from '@components/Button/Primary.vue';
    import ButtonSecondary from '@components/Button/Secondary.vue';
    import { Icon } from '@iconify/vue';
    import { useModalStore } from '@stores/modalStore';
    import { useForm } from '@inertiajs/vue3';

    const props = defineProps({
        exercises: Array,
        muscleGroups: Object,
        exerciseDropdown: Object,
    })

    const modalStore = useModalStore();

    onMounted(() => {
        console.log(props.exercises)
        console.log(props.muscleGroups)
        console.log(props.exerciseDropdown)
    })

    const meso = ref({
        name: 'Untitled',
        days: [
            { label: 'Untitled', }
        ]
    })

    function getExercise(id) {
        return props.exercises[id]
    }

    function getMuscleGroup(id) {
        return props.muscleGroups[id]
    }

    const days = reactive([
        {
            label: 'Untitled',
            exercises: [
                { muscleGroup: 1, exerciseId: 22, },
                { muscleGroup: 2, exerciseId: 70, },
                { muscleGroup: 3, exerciseId: 87, },
                { muscleGroup: 3, exerciseId: null, },
            ]
        },
        {
            label: 'Untitled',
            exercises: [
                { muscleGroup: 1, exerciseId: 22, },
                { muscleGroup: 2, exerciseId: 70, },
                { muscleGroup: 3, exerciseId: 87, },
            ]
        },
        {
            label: 'Untitled',
            exercises: [
                { muscleGroup: 1, exerciseId: 22, },
                { muscleGroup: 2, exerciseId: 70, },
                { muscleGroup: 3, exerciseId: 87, },
            ]
        },
    ])

    function addDay() {
        days.push({
            label: 'Untitled',
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

    // Dummy data
    const requestData = {
        meso: {
            name: 'Hypertrophy Plan',
            unit: 'kg',
            weeks: 4,
            status: 'active', // Example status
        },
        days: [
            {
                label: 'Day 1',
                exercises: [
                    { muscleGroup: 1, exerviseId: 101 },
                    { muscleGroup: 2, exerviseId: 102 },
                ],
            },
            {
                label: 'Day 2',
                exercises: [
                    { muscleGroup: 3, exerviseId: 103 },
                    { muscleGroup: 4, exerviseId: 104 },
                ],
            },
            {
                label: 'Day 3',
                exercises: [
                    { muscleGroup: 5, exerviseId: 105 },
                    { muscleGroup: 6, exerviseId: 106 },
                ],
            },
        ],
    };

    const mesoForm = useForm(requestData);

    function submit() {
        mesoForm.post('/mesocycles')
    }



</script>