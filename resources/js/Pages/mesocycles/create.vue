<template>
    <div class="flex justify-between mb-4">
        <div class="flex flex-col">
            <InputText v-model="meso.name" />
            <p class="text-secondary text-sm">Generated from Template</p>
        </div>
    </div>

    <div class="flex gap-2">
        <UiBox class="flex flex-col gap-2" v-for="(day, idx) in days" :key="idx">
            <div class="flex items-center justify-between gap-3">
                <InputText v-model="day.label" />
                <Icon icon="material-symbols-light:delete-outline" width="22px"
                    class="cursor-pointer hover:text-red-hover transition" />
            </div>
            <UiBox class="bg-layer-light flex flex-col gap-2" v-for="exercise in day.exercises">
                <div class="flex flex-center justify-between">
                    <div class="bg-orange px-2 rounded">{{ getMuscleGroup(exercise.muscleGroup) }}</div>
                    <Icon icon="material-symbols-light:delete-outline" width="22px"
                        class="cursor-pointer hover:text-red-hover transition" />
                </div>
                <InputDropdown :options="exerciseDropdown[exercise.muscleGroup]" :filter="true" />
                <!-- {{ exerciseDropdown[exercise.muscleGroup] }} -->
            </UiBox>

            <div class="bg-input p-1 rounded flex items-center justify-center gap-1 border border-layer-border cursor-pointer hover:bg-layer transition"
                @click="openMuscleGroupModal(idx)">
                <Icon icon="ic:baseline-plus" width="21px" />
                Add Muscle Group
            </div>
        </UiBox>

    </div>
    <!-- {{ exercises }} -->
    <!-- {{ muscleGroups }} -->

</template>
<script setup>
    // Check task list spotter (firts one)
    import { ref, reactive, onMounted } from 'vue';
    import InputText from '@components/Input/text.vue';
    import InputDropdown from "@components/Input/Dropdown.vue"
    import UiBox from '@components/Ui/Box.vue';
    import { Icon } from '@iconify/vue';
    import { useModalStore } from '@stores/modalStore';

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
                { muscleGroup: 2, exerciseId: 183, },
                { muscleGroup: 3, exerciseId: 56, },
                { muscleGroup: 3, exerciseId: null, },
            ]
        },
        {
            label: 'Untitled',
            exercises: [
                { muscleGroup: 1, exerciseId: 22, },
                { muscleGroup: 2, exerciseId: 183, },
                { muscleGroup: 3, exerciseId: 56, },
            ]
        },
        {
            label: 'Untitled',
            exercises: [
                { muscleGroup: 1, exerciseId: 22, },
                { muscleGroup: 2, exerciseId: 183, },
                { muscleGroup: 3, exerciseId: 56, },
            ]
        },
    ])

    // I can only let IDs here and the names to display will come from exercises[id]
    const day_template = ref({
        label: 'Untitled',
        exercises: []
    })

    function openMuscleGroupModal(dayId) {
        modalStore.openModal('MuscleGroups', props.muscleGroups, (selectedMuscleGroup) => {
            addMuscleGroup(selectedMuscleGroup, dayId)
        });
    }

    // @TODO: get muscle group from here and form an object to add based on the selected muscle group
    function addMuscleGroup(selectedMuscleGroup, dayId) {
        days[dayId].exercises.push({ muscleGroup: selectedMuscleGroup, exerciseId: null });
    }

</script>