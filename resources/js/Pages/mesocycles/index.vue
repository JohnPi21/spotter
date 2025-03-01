<template>
    <Head title="Mesocycles"></Head>

    <UiTitle title="Mesocycles" button="NEW" icon="ic:baseline-plus" url="/mesocycles/create" />
    <UiBox v-if="mesocycles.length > 0">
        <ul>
            <li v-for="(meso, idx) in mesocycles" :key="idx">
                <Link :href="`mesocycles/${meso.id}/day/1`">
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
                                <li class="flex justify-between" @click="setActive(meso.id)">
                                    <p>Set Active</p>
                                    <Icon name="gg:check-o"/>
                                </li>

                                <li class="flex justify-between" @click="destroy(meso.id)">
                                    <p>Delete</p>
                                    <Icon name="material-symbols-light:delete"/>
                                </li>
                            </UiDropdownMenu>
                        </div>
                    </div>
                </div>
                </Link>
            </li>
        </ul>
    </UiBox>
    <h2 class="text-xl text-center pt-3" v-else>No Mesocycles Created.</h2>
</template>

<script setup>
    import { Icon } from '@iconify/vue';
    import UiBox from '@components/Ui/Box.vue';
    import UiTitle from '@components/Ui/Title.vue';
    import UiDropdownMenu from '@components/Ui/DropdownMenu.vue';
    import { Link, useForm } from '@inertiajs/vue3';
    import { Head } from '@inertiajs/vue3'

    const props = defineProps({
        title: String,
        mesocycles: Array,
    })

    function setActive(id){
        useForm().patch(`/mesocycles/${id}`)
    }

    function destroy(id){
        console.log(id)
        if(! confirm('Are you sure you want to delete mesocycles?')) return;

        useForm().delete(`/mesocycles/${id}`);
    }
</script>

<style scoped></style>