<template>
    <UiTitle title="Mesocycles" button="NEW" icon="ic:baseline-plus" url="/mesocycles/create" />
    <UiBox>
        <ul>
            <li v-for="(meso, idx) in mesocycles" :key="idx">
                <Link href="mesocycles/show">
                <div class="flex justify-between items-center">
                    <div class="flex flex-col gap-1">
                        <h3>{{ meso.name }}</h3>
                        <div class="text-secondary text-sm">{{ meso.weeks }} WEEKS - {{ meso.days }} DAYS/WEEK</div>
                    </div>
                    <div class="flex align-end items-center gap-3">
                        <div class="bg-blue rounded px-2 py-0.5 opacity-50" v-if="meso.status === 1">Current</div>
                        <div class="relative">
                            <div class="hover:cursor-pointer">
                                <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                            </div>
                            <UiDropdownMenu :idx="idx">
                                <div v-for="li in 15">First el</div>
                            </UiDropdownMenu>
                        </div>
                    </div>
                </div>
                </Link>
            </li>
        </ul>
    </UiBox>
</template>

<script setup>
    import { Icon } from '@iconify/vue';
    import UiBox from '@components/Ui/Box.vue';
    import UiTitle from '@components/Ui/Title.vue';
    import UiDropdownMenu from '@components/Ui/DropdownMenu.vue';
    import { Link } from '@inertiajs/vue3';

    const props = defineProps({
        mesocycles: Array
    })

    // const mesocycles = ref([
    //     {name: 'Superman Workout', duration: '6 weeks', frequency: '3 Days / Week'},
    //     {name: 'Spiderman Workout', duration: '6 weeks', frequency: '3 Days / Week'},
    //     {name: 'Ironman Workout', duration: '6 weeks', frequency: '3 Days / Week'},
    //     {name: 'Hilk Workout', duration: '6 weeks', frequency: '3 Days / Week'},
    // ]);
</script>

<style scoped></style>