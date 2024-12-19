<template>
    <div class="flex justify-between mb-4">
        <div class="flex flex-col">
            <InputText v-model="meso.name"/>
            <p class="text-secondary text-sm">Generated from Template</p>
        </div>
    </div>

    <div class="flex gap-2" >
        <UiBox class="flex flex-col gap-2" v-for="(day, idx) in days" :key="idx">
            <div class="flex items-center justify-between gap-3">
                <InputText v-model="day.label" />
                <Icon icon="material-symbols-light:delete-outline" width="22px" class="cursor-pointer hover:text-red-hover transition"/>
            </div>
            <UiBox class="bg-layer-light flex flex-col gap-2" v-for="exercise in day.exercises">
                <div class="flex flex-center justify-between">
                    <div class="bg-orange px-2 rounded">{{ exercise.muscleGroup }}</div>
                    <Icon icon="material-symbols-light:delete-outline" width="22px" class="cursor-pointer hover:text-red-hover transition"/>
                </div>
                <InputDropdown :options="[]" :filter="true"/>
            </UiBox>
        </UiBox>
    </div>
</template>
<script setup>
    import { ref, reactive, defineProps, onMounted } from 'vue';
    import InputText from '@components/Input/text.vue';
    import InputDropdown from "@components/Input/Dropdown.vue"
    import UiBox from '@components/Ui/Box.vue';
    import { Icon } from '@iconify/vue';

    const props = defineProps({
        exercises : Array,
        muscleGroups : Object
    })

    onMounted(() => {
        console.log(props.exercises)
        console.log(props.muscleGroups)
    })


    const meso = ref({
        name : 'Untitled',
        days : [
            {label: 'Untitled', }
        ]

    })

    const days = reactive([
        {
            label: 'Untitled', 
            exercises: [
                {id: 1, muscleGroup: 1, name: 'Dumbbell Press (Flat)'},
                {id: 2, muscleGroup: 2, name: 'Smith Machine Squat (Feet Forward)'},
                {id: 3, muscleGroup: 3,  name: 'Seated Cable Row'},
            ]
        },
        {
            label: 'Untitled',
            exercises: [
                {id: 1, muscleGroup: 'Chest', name: 'Dumbbell Press (Flat)'},
                {id: 2, muscleGroup: 'Quads', name: 'Smith Machine Squat (Feet Forward)'},
                {id: 3, muscleGroup: 'Back',  name: 'Seated Cable Row'},
            ]
        },
        {
            label: 'Untitled',
            exercises: [
                {id: 1, muscleGroup: 'Chest', name: 'Dumbbell Press (Flat)'},
                {id: 2, muscleGroup: 'Quads', name: 'Smith Machine Squat (Feet Forward)'},
                {id: 3, muscleGroup: 'Back',  name: 'Seated Cable Row'},
            ]
        },
    ])

    // I can only let IDs here and the names to display will come from exercises[id]
    const day_template = ref({
            label: 'Untitled',
            exercises: [
                {id: 1, muscleGroup: 'Chest', name: 'Dumbbell Press (Flat)'},
                {id: 2, muscleGroup: 'Quads', name: 'Smith Machine Squat (Feet Forward)'},
                {id: 3, muscleGroup: 'Back',  name: 'Seated Cable Row'},
            ]
    })

</script>