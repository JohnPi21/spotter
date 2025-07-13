<template>
    <!-- prettier-ignore-start -->
    <div class="mx-auto my-2 flex max-w-[768px] flex-col gap-3">
        <Head title="Mesocycles"></Head>

        <UiTitle title="Mesocycles" button="NEW" icon="ic:baseline-plus" url="/mesocycles/create" />

        <UiErrors :errors="errors" />
        <UiBox v-if="props.mesocycles.length > 0">
            <ul>
                <li v-for="(meso, idx) in mesocycles" :key="meso.id">
                    <Link :href="`mesocycles/${meso.id}/day/1`">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col gap-1">
                                <h3>{{ meso.name }}</h3>
                                <div class="text-sm text-secondary">
                                    {{ meso.weeks_duration }} WEEKS - {{ meso.days_per_week }} DAYS/WEEK
                                </div>
                            </div>

                            <div class="align-end flex items-center gap-3">
                                <div class="rounded bg-blue px-2 py-0.5 opacity-50" v-if="meso.status === 1">
                                    Current
                                </div>
                                <UiDropdownMenu :idx="meso.id" left="-50px" :key="meso.id">
                                    <template #header>
                                        <div class="hover:cursor-pointer">
                                            <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                                        </div>
                                    </template>

                                    <template v-slot="slotProps">
                                        <li
                                            v-for="(item, i) in mesoDropdown"
                                            :key="i"
                                            :class="item.class"
                                            @click.prevent="
                                                item.action(meso.id);
                                                slotProps.toggle();
                                            "
                                        >
                                            <Icon :icon="item.icon" />
                                            {{ item.label }}
                                        </li>
                                    </template>
                                </UiDropdownMenu>
                            </div>
                        </div>
                    </Link>
                </li>
            </ul>
        </UiBox>
        <h2 class="pt-3 text-center text-xl" v-else>No Mesocycles Created.</h2>
    </div>
</template>
<!-- prettier-ignore-end -->

<script setup lang="ts">
import UiBox from "@/Components/Ui/Box.vue";
import UiDropdownMenu from "@/Components/Ui/DropdownMenu.vue";
import UiErrors from "@/Components/Ui/Errors.vue";
import UiTitle from "@/Components/Ui/Title.vue";
import { Icon } from "@iconify/vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { onMounted } from "vue";

const props = defineProps<{
    title: string;
    mesocycles: Array<Mesocycle>;
    errors?: object;
}>();

const form = useForm({});

function setActive(id: number) {
    form.patch(`/mesocycles/${id}`);
}

function destroy(id: number) {
    if (!confirm("Are you sure you want to delete mesocycle?")) return;

    form.delete(`/mesocycles/${id}`);
}

const mesoDropdown = [
    {
        icon: "ph:swap",
        label: "Set Active",
        action: (mesocycleID: number) => setActive(mesocycleID),
    },
    {
        icon: "material-symbols:delete-outline",
        label: "Delete",
        class: "!text-red",
        action: (mesocycleID: number) => destroy(mesocycleID),
    },
];

onMounted(() => {
    console.log(props.mesocycles);
});
</script>

<style scoped></style>
