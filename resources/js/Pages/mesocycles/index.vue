<template>
    <div class="mx-auto my-2 flex w-full max-w-[768px] flex-col gap-4">
        <Head title="Mesocycles" />
        <UiTitle title="Mesocycles" button="NEW" icon="ic:baseline-plus" url="/mesocycles/create" />
        <UiErrors :errors="errors" />

        <ul v-if="props.mesocycles.length" class="flex list-none flex-col gap-3">
            <li
                v-for="meso in props.mesocycles"
                :key="meso.id"
                class="overflow-hidden rounded-lg border bg-layer shadow-sm transition-colors"
                :class="expandedId === meso.id ? 'border-helper' : 'border-layer-border'"
            >
                <div class="flex items-start gap-3 p-4 sm:items-center sm:p-5">
                    <div class="flex min-w-0 flex-1 flex-col gap-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <Link
                                :href="route('days.show', { mesocycle: meso.id, day: meso.last_day })"
                                class="break-words text-lg font-medium leading-snug tracking-tight text-primary transition hover:text-text-orange"
                            >
                                {{ meso.name }}
                            </Link>
                            <span
                                v-if="meso.finished_at"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-secondary"
                            >
                                <span class="h-1.5 w-1.5 rounded-full bg-text-green" aria-hidden="true" />
                                Finished
                            </span>
                            <span
                                v-if="meso.status === 1"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-secondary"
                            >
                                <span class="h-1.5 w-1.5 rounded-full bg-text-blue" aria-hidden="true" />
                                Current
                            </span>
                        </div>
                        <div
                            class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[13px] leading-relaxed text-secondary"
                        >
                            <span>{{ meso.weeks_duration ?? meso.weeksDuration ?? "—" }} weeks</span>
                            <span>{{ meso.days_per_week }} days / week</span>
                            <template v-if="meso.planned_stats">
                                <span>{{ meso.planned_stats.muscle_groups_count }} muscle groups</span>
                                <span>{{ meso.planned_stats.sets_per_week }} planned sets / week</span>
                            </template>
                            <span v-if="meso.last_edited" class="text-helper">Edited {{ meso.last_edited }}</span>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-input-border-focus"
                        :class="
                            expandedId === meso.id
                                ? 'border-helper bg-layer-light text-text-orange'
                                : 'border-layer-border text-secondary hover:bg-layer-light hover:text-primary'
                        "
                        :aria-expanded="expandedId === meso.id"
                        :aria-controls="`mesocycle-details-${meso.id}`"
                        :aria-label="`${expandedId === meso.id ? 'Hide' : 'Show'} details for ${meso.name}`"
                        @click="toggleDetails(meso.id)"
                    >
                        <Icon
                            icon="heroicons:chevron-down-20-solid"
                            width="20"
                            class="transition-transform duration-200"
                            :class="{ 'rotate-180': expandedId === meso.id }"
                        />
                    </button>
                </div>

                <div
                    v-if="expandedId === meso.id"
                    :id="`mesocycle-details-${meso.id}`"
                    class="flex flex-col gap-5 border-t border-layer-border bg-input/40 p-4 sm:p-5"
                >
                    <dl class="grid grid-cols-2 gap-2 sm:grid-cols-4 sm:gap-3">
                        <div class="flex flex-col gap-1 rounded-lg border border-layer-border bg-layer p-3">
                            <dt class="text-[13px] leading-relaxed text-secondary">Duration</dt>
                            <dd class="text-2xl font-medium tabular-nums leading-tight tracking-tight text-primary">
                                {{ meso.weeks_duration ?? meso.weeksDuration ?? "—" }}
                                <span class="text-[13px] font-normal tracking-normal text-secondary">weeks</span>
                            </dd>
                        </div>
                        <div class="flex flex-col gap-1 rounded-lg border border-layer-border bg-layer p-3">
                            <dt class="text-[13px] leading-relaxed text-secondary">Frequency</dt>
                            <dd class="text-2xl font-medium tabular-nums leading-tight tracking-tight text-primary">
                                {{ meso.days_per_week }}
                                <span class="text-[13px] font-normal tracking-normal text-secondary">days / week</span>
                            </dd>
                        </div>
                        <div class="flex flex-col gap-1 rounded-lg border border-layer-border bg-layer p-3">
                            <dt class="text-[13px] leading-relaxed text-secondary">Muscle groups</dt>
                            <dd class="text-2xl font-medium tabular-nums leading-tight tracking-tight text-primary">
                                {{ meso.planned_stats?.muscle_groups_count ?? "—" }}
                            </dd>
                        </div>
                        <div class="flex flex-col gap-1 rounded-lg border border-layer-border bg-layer p-3">
                            <dt class="text-[13px] leading-relaxed text-secondary">Planned sets / week</dt>
                            <dd class="text-2xl font-medium tabular-nums leading-tight tracking-tight text-primary">
                                {{ meso.planned_stats?.sets_per_week ?? "—" }}
                            </dd>
                        </div>
                    </dl>

                    <section class="flex flex-col gap-3" :aria-labelledby="`template-heading-${meso.id}`">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h4
                                :id="`template-heading-${meso.id}`"
                                class="text-[15px] font-medium leading-snug text-primary"
                            >
                                Planned weekly volume
                            </h4>
                            <span class="text-xs leading-relaxed text-secondary">Primary muscle groups</span>
                        </div>
                        <p v-if="!meso.planned_stats" class="text-sm text-secondary">
                            Planned volume is not available for this mesocycle yet.
                        </p>
                        <div v-else class="overflow-hidden rounded-lg border border-layer-border bg-layer">
                            <div
                                class="grid grid-cols-2 gap-3 border-b border-layer-border px-3 py-2.5 text-xs font-medium text-secondary sm:px-4"
                            >
                                <span>Muscle group</span>
                                <span class="text-right">Sets / week</span>
                            </div>
                            <div
                                v-for="group in meso.planned_stats?.muscle_groups ?? []"
                                :key="group.id"
                                class="grid grid-cols-2 items-center gap-3 border-b border-layer-border/60 px-3 py-2.5 last:border-0 sm:px-4"
                            >
                                <span class="text-sm leading-relaxed text-secondary">{{ group.name }}</span>
                                <div class="flex items-center justify-end gap-3">
                                    <div
                                        v-if="group.sets !== null"
                                        class="hidden h-1 w-16 overflow-hidden rounded-full bg-layer-light sm:block"
                                        aria-hidden="true"
                                    >
                                        <div
                                            class="h-full rounded-full bg-secondary/60"
                                            :style="{
                                                width: `${(group.sets / Math.max(...(meso.planned_stats?.muscle_groups.map((item) => item.sets) ?? [1]))) * 100}%`,
                                            }"
                                        />
                                    </div>
                                    <span
                                        v-if="group.sets !== null"
                                        class="min-w-[3.5rem] text-right text-sm font-medium tabular-nums text-primary"
                                    >
                                        {{ group.sets }} sets
                                    </span>
                                    <span v-else class="text-[13px] text-secondary">Not defined</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div class="flex flex-col gap-3 border-t border-layer-border pt-4">
                        <h4 class="text-[15px] font-medium leading-snug text-primary">Manage mesocycle</h4>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                            <button
                                v-for="item in actionsForMesocycle(meso)"
                                :key="item.label"
                                type="button"
                                :disabled="form.processing"
                                class="flex items-center justify-center gap-2 rounded-lg border border-layer-border bg-layer px-3 py-3 text-[13px] font-medium leading-snug text-secondary transition hover:border-helper hover:bg-layer-light hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-input-border-focus disabled:cursor-wait disabled:opacity-50"
                                :class="item.class"
                                @click="item.action(meso.id)"
                            >
                                <Icon :icon="item.icon" width="18" class="shrink-0" />
                                {{ item.label }}
                            </button>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
        <h2 v-else class="pt-3 text-center text-xl">No Mesocycles Created.</h2>
    </div>
</template>

<script setup lang="ts">
import UiErrors from "@/Components/Ui/Errors.vue";
import UiTitle from "@/Components/Ui/Title.vue";
import { useMesocycle } from "@/Composables/useMesocycle";
import { Icon } from "@iconify/vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const props = defineProps<{
    title: string;
    mesocycles: Array<Mesocycle & { weeks_duration?: number; last_edited?: string }>;
    errors?: object;
}>();

const expandedId = ref<number | null>(props.mesocycles[0]?.id ?? null);

function toggleDetails(id: number): void {
    expandedId.value = expandedId.value === id ? null : id;
}

const form = useForm({});
const { copyMeso } = useMesocycle();

type MesocycleAction = {
    icon: string;
    label: string;
    class?: string;
    visible?: (mesocycle: Mesocycle) => boolean;
    action: (mesocycleId: number) => void;
};

function setActive(id: number) {
    form.patch(route("mesocycles.activate", { mesocycle: id }));
}

function destroy(id: number) {
    if (!confirm("Are you sure you want to delete mesocycle?")) return;

    form.delete(route("mesocycles.destroy", { mesocycle: id }));
}

const mesocycleActions: MesocycleAction[] = [
    {
        icon: "material-symbols:edit-outline",
        label: "Edit mesocycle",
        action: (mesocycleId: number) => router.visit(route("mesocycles.edit", { mesocycle: mesocycleId })),
    },
    {
        icon: "ph:swap",
        label: "Make current",
        class: "text-text-orange hover:!border-border-orange hover:!text-text-orange",
        visible: (mesocycle: Mesocycle) => mesocycle.status !== 1,
        action: (mesocycleId: number) => setActive(mesocycleId),
    },
    {
        icon: "boxicons:copy",
        label: "Copy structure",
        action: (mesocycleId: number) => copyMeso(mesocycleId),
    },
    {
        icon: "material-symbols:delete-outline",
        label: "Delete mesocycle",
        class: "text-text-red hover:!border-border-red hover:!text-text-red",
        action: (mesocycleId: number) => destroy(mesocycleId),
    },
];

function actionsForMesocycle(mesocycle: Mesocycle): MesocycleAction[] {
    return mesocycleActions.filter((action) => action.visible?.(mesocycle) ?? true);
}
</script>
