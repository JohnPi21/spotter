<template>
    <div class="flex justify-between">
        <div class="flex flex-col">
            <InputText v-model="meso.name"/>
            <p class="text-secondary text-sm">Generated from Template</p>
        </div>
    </div>
    <UiBox></UiBox>
</template>
<script setup>
    import UiBox from '@components/Ui/Box.vue';

    const meso = ref({
        name : 'Untitled',
        days : [
            {label: 'Untitled', }
        ]

    })

    // I can only let IDs here and the names to display will come from muscle_groups[id]
    const day = ref({
        label: 'Untitled', muscle_groups: [
            {id: 1, name: 'Chest', exercise: 1},
            {id: 1, name: 'Chest', exercise: 1},
            {id: 2, name: 'BAck', exercise: {id: 1, name: 'Rows'}},
        ]
    })

    const muscle_groups = ref([
        {id: 1, name: 'Chest'} 
    ]) // this will come from backend
</script>