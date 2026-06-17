<template>
    <!-- prettier-ignore-start -->
    <div class="mx-auto my-2 flex max-w-[768px] flex-col gap-3">
        <Head title="Mesocycles"></Head>

        <UiTitle title="Mesocycles" button="NEW" icon="ic:baseline-plus" url="/mesocycles/create" />

        <UiErrors :errors="errors" />
        <UiBox v-if="props.mesocycles.length > 0">
            <ul>
                <li v-for="(meso, idx) in mesocycles" :key="meso.id">
                    <Link :href="route('days.show', { mesocycle: meso.id, day: meso.last_day })">
                        <div class="flex items-center justify-between">
                            <div class="flex flex-col gap-1">
                                <h3>{{ meso.name }}</h3>
                                <div class="text-sm text-secondary">
                                    {{ meso.weeksDuration }} WEEKS - {{ meso.days_per_week }} DAYS/WEEK
                                </div>
                            </div>

                            <div class="align-end flex items-center gap-3">
                                <div class="rounded bg-blue px-2 py-0.5 opacity-50" v-if="meso.status === 1">
                                    Current
                                </div>
                                <div class="flex items-center text-secondary">
                                    <template v-for="(item, itemIndex) in mesocycleActions" :key="item.label">
                                        <span v-if="itemIndex > 0" class="px-2 text-helper" aria-hidden="true">|</span>
                                        <button
                                            type="button"
                                            :title="item.label"
                                            :aria-label="item.label"
                                            class="flex items-center justify-center transition hover:text-primary"
                                            :class="item.class"
                                            @click.prevent.stop="item.action(meso.id)"
                                        >
                                            <Icon :icon="item.icon" width="18" />
                                        </button>
                                    </template>
                                </div>
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
import UiErrors from "@/Components/Ui/Errors.vue";
import UiTitle from "@/Components/Ui/Title.vue";
import { useMesocycle } from "@/Composables/useMesocycle";
import { Icon } from "@iconify/vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const props = defineProps<{
    title: string;
    mesocycles: Array<Mesocycle>;
    errors?: object;
}>();

const form = useForm({});
const { copyMeso } = useMesocycle();

function setActive(id: number) {
    form.patch(route("mesocycles.activate", { mesocycle: id }));
}

function destroy(id: number) {
    if (!confirm("Are you sure you want to delete mesocycle?")) return;

    form.delete(route("mesocycles.destroy", { mesocycle: id }));
}

const mesocycleActions = [
    {
        icon: "ph:swap",
        label: "Set Active",
        action: (mesocycleId: number) => setActive(mesocycleId),
    },
    {
        icon: "boxicons:copy",
        label: "Copy mesocycle structure",
        action: (mesocycleId: number) => copyMeso(mesocycleId),
    },
    {
        icon: "material-symbols:delete-outline",
        label: "Delete",
        class: "text-red hover:!text-red",
        action: (mesocycleId: number) => destroy(mesocycleId),
    },
];
</script>

<style scoped></style>
