<template>
    <UiTitle title="Mesocycles" button="NEW" icon="ic:baseline-plus" />
    <UiBox>
        <ul>
            <li v-for="(meso,idx) in mesocycles" :key="idx">
                <div class="flex justify-between items-center">
                    <div class="flex flex-col">
                        <h3>{{ meso.name }}</h3>
                        <div class="text-secondary text-sm">{{ meso.duration }} - {{ meso.frequency }}</div>
                    </div>
                    <div class="hover:cursor-pointer"><Icon icon="iconamoon:menu-kebab-vertical" width="18px"/></div>
                </div>
            </li>
        </ul>
    </UiBox>
</template>

<script setup>
    import { Icon } from '@iconify/vue';
    import UiBox from '@components/Ui/Box.vue';
    import UiTitle from '@components/Ui/Title.vue';
    import { ref } from 'vue';

    const mesocycles = ref([
        {name: 'Superman Workout', duration: '6 weeks', frequency: '3 Days / Week'},
        {name: 'Spiderman Workout', duration: '6 weeks', frequency: '3 Days / Week'},
        {name: 'Ironman Workout', duration: '6 weeks', frequency: '3 Days / Week'},
        {name: 'Hilk Workout', duration: '6 weeks', frequency: '3 Days / Week'},
    ]);
</script>

<style scoped>
</style>