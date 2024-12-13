<template>
    <div class="flex justify-between">
        <div class="flex flex-col">
            <InputText v-model="meso.name"/>
            <p class="text-secondary text-sm">Generated from Template</p>
        </div>
    </div>

    <div class="flex gap-2">
        <UiBox class="flex flex-col gap-2">
            <div class="flex items-center justify-between gap-3">
                <InputText v-model="day.label" />
                <Icon icon="material-symbols-light:delete-outline" width="22px"/>
            </div>
            <UiBox class="bg-layer-light">
                <div class="flex flex-center justify-between">
                    <div class="bg-orange opacity-50 px-2 rounded">Chest</div>
                    <Icon icon="material-symbols-light:delete-outline" width="22px"/>
                </div>
                <InputDropdown :options="[{label: 'Test', value: 'test'}]"/>
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
        muscle_groups : Array
    })

    onMounted(() => {
        console.log(props.exercises)
        console.log(props.muscle_groups)
    })


    const meso = ref({
        name : 'Untitled',
        days : [
            {label: 'Untitled', }
        ]

    })

    const days = reactive([
        {
            label: 'Untitled', muscle_groups: [
                {id: 1, name: 'Chest', exercise: {id: 1, name: 'Press'}},
                {id: 2, name: 'Quads', exercise: {id: 2, name: 'Squat'}},
                {id: 3, name: 'Back', exercise:  {id: 3, name: 'Rows'}},
            ]
        },
        {
            label: 'Untitled', muscle_groups: [
                {id: 1, name: 'Chest', exercise: 1},
                {id: 1, name: 'Chest', exercise: 1},
                {id: 2, name: 'BAck', exercise: {id: 1, name: 'Rows'}},
            ]
        },
        {
            label: 'Untitled', muscle_groups: [
                {id: 1, name: 'Chest', exercise: 1},
                {id: 1, name: 'Chest', exercise: 1},
                {id: 2, name: 'BAck', exercise: {id: 1, name: 'Rows'}},
            ]
        },
    ])

    // I can only let IDs here and the names to display will come from muscle_groups[id]
    const day = ref({
        label: 'Untitled', muscle_groups: [
            {id: 1, name: 'Chest', exercise: 1},
            {id: 1, name: 'Chest', exercise: 1},
            {id: 2, name: 'BAck', exercise: {id: 1, name: 'Rows'}},
        ]
    })

    // const muscle_groups = ref([
    //     {id: 1, name: 'Chest'} 
    // ])
</script>