<template>
    <Head title="Mesocycles"></Head>

    <UiTitle title="Mesocycles" button="NEW" icon="ic:baseline-plus" url="/mesocycles/create" />
    <UiBox v-if="props.mesocycles.length > 0">
        <ul>
            <li v-for="(meso, idx) in mesocycles" :key="idx">
                <Link :href="`mesocycles/${meso.id}/day/1`">
                <div class="flex justify-between items-center">
                    <div class="flex flex-col gap-1">
                        <h3>{{ meso.name }}</h3>
                        <div class="text-secondary text-sm">{{ meso.weeks_duration }} WEEKS - {{ meso.days_per_week }} DAYS/WEEK</div>
                    </div>
                    <div class="flex align-end items-center gap-3">
                        <div class="bg-blue rounded px-2 py-0.5 opacity-50" v-if="meso.status === 1">Current</div>

                        <UiDropdownMenu :idx="idx">
                            <template #header>
                                <div class="hover:cursor-pointer">
                                    <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                                </div>
                            </template>

                            <li v-for="(item, i) in mesoDropdown" :key="i" :class="item.class" @click="item.action(meso.id)">
                                <Icon :icon="item.icon" /> {{ item.label }}
                            </li>
                        </UiDropdownMenu>
                    </div>
                </div>
                </Link>
            </li>
        </ul>
    </UiBox>
    <h2 class="text-xl text-center pt-3" v-else>No Mesocycles Created.</h2>
</template>

<script setup lang="ts">
    import { Icon } from '@iconify/vue';
    import UiBox from '@/Components/Ui/Box.vue';
    import UiTitle from '@/Components/Ui/Title.vue';
    import UiDropdownMenu from '@/Components/Ui/DropdownMenu.vue';
    import { Link, useForm } from '@inertiajs/vue3';
    import { Head } from '@inertiajs/vue3'
    import { onMounted } from 'vue';

    const props = defineProps< {
        title: String,
        mesocycles: Array<Mesocycle>,
    }>();

    const form = useForm({});

    function setActive(id: number){
        form.patch(`/mesocycles/${id}`)
    }

    function destroy(id: number){
        console.log(id)
        if(! confirm('Are you sure you want to delete mesocycles?')) return;

        form.delete(`/mesocycles/${id}`);
    }

    const mesoDropdown = [
        {
            icon: "ph:swap",
            label: "Set Active",
            action: (mesocycleID: number) => {}
        },
        {
            icon: "material-symbols:delete-outline",
            label: "Delete",
            class: "!text-red",
            action: () => console.log("Replace clicked"),
        },
    ];

    onMounted(() => {
        console.log(props.mesocycles)
    })
</script>

<style scoped></style>