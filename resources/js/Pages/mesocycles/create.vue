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
                    <div class="bg-orange px-2 rounded">{{ exercise.muscleGroup }}</div>
                    <Icon icon="material-symbols-light:delete-outline" width="22px"
                        class="cursor-pointer hover:text-red-hover transition" />
                </div>
                <InputDropdown :options="[]" :filter="true" />
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
        muscleGroups: Object
    })

    const modalStore = useModalStore();
    const selected_day = ref(null);

    onMounted(() => {
        console.log(props.exercises)
        console.log(props.muscleGroups)
    })

    const meso = ref({
        name: 'Untitled',
        days: [
            { label: 'Untitled', }
        ]

    })

    const days = reactive([
        {
            label: 'Untitled',
            exercises: [
                { id: 1, muscleGroup: 1, name: 'Dumbbell Press (Flat)' },
                { id: 2, muscleGroup: 2, name: 'Smith Machine Squat (Feet Forward)' },
                { id: 3, muscleGroup: 3, name: 'Seated Cable Row' },
            ]
        },
        {
            label: 'Untitled',
            exercises: [
                { id: 1, muscleGroup: 'Chest', name: 'Dumbbell Press (Flat)' },
                { id: 2, muscleGroup: 'Quads', name: 'Smith Machine Squat (Feet Forward)' },
                { id: 3, muscleGroup: 'Back', name: 'Seated Cable Row' },
            ]
        },
        {
            label: 'Untitled',
            exercises: [
                { id: 1, muscleGroup: 'Chest', name: 'Dumbbell Press (Flat)' },
                { id: 2, muscleGroup: 'Quads', name: 'Smith Machine Squat (Feet Forward)' },
                { id: 3, muscleGroup: 'Back', name: 'Seated Cable Row' },
            ]
        },
    ])

    // I can only let IDs here and the names to display will come from exercises[id]
    const day_template = ref({
        label: 'Untitled',
        exercises: [
            { id: 1, muscleGroup: 'Chest', name: 'Dumbbell Press (Flat)' },
            { id: 2, muscleGroup: 'Quads', name: 'Smith Machine Squat (Feet Forward)' },
            { id: 3, muscleGroup: 'Back', name: 'Seated Cable Row' },
        ]
    })

    function openMuscleGroupModal(day_id) {
        modalStore.openModal('MuscleGroups', props.muscleGroups, addMuscleGroup);
        selected_day.value = day_id;
    }

    // @TODO: get muscle group from here and form an object to add based on the selected muscle group
    function addMuscleGroup(muscleGroup) {
        console.log(days);
        console.log(days[selected_day.value]);
        console.log(selected_day.value);
        days[selected_day.value].exercises.push(day_template.value.exercises[0]);
    }


</script>