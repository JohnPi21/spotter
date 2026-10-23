<script setup lang="ts">
import WeeklySets from "@/Components/Chart/WeeklySets.vue";
import type { DashboardTraining } from "@/types/dashboard";
import { Icon } from "@iconify/vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const props = defineProps<{ training: DashboardTraining }>();
const selectedWeekIndex = ref(7);
const selectedGroup = ref<number | null>(null);
const currentWeek = computed(() => props.training.weeks[7]);
const selectedWeek = computed(() => props.training.weeks[selectedWeekIndex.value]);
const trainedGroups = computed(() => props.training.muscleGroups.filter((group) => group.sets[7] > 0).length);
const hasActivity = computed(() => props.training.weeks.some((week) => week.sets > 0));
const selectedRows = computed(() =>
    props.training.muscleGroups
        .filter((group) => selectedGroup.value === null || group.id === selectedGroup.value)
        .map((group) => ({ ...group, completed: group.sets[selectedWeekIndex.value] ?? 0 }))
        .filter((group) => group.completed > 0 || (selectedWeek.value.isCurrent && (group.plannedSets ?? 0) > 0))
        .sort((a, b) => b.completed - a.completed || a.name.localeCompare(b.name))
);
const maxCompleted = computed(() => Math.max(1, ...selectedRows.value.map((group) => group.completed)));
const trainingUrl = computed(() => route(props.training.activePlan ? "mesocycles.current" : "mesocycles"));
const formatNumber = (value: number) => new Intl.NumberFormat(undefined, { maximumFractionDigits: 1 }).format(value);
</script>

<template>
    <div class="mx-auto my-3 flex w-full max-w-[768px] flex-col gap-6 pb-6">
        <Head title="Training overview" />

        <header class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-col gap-1">
                <p class="text-xs font-medium uppercase tracking-[0.16em] text-text-orange">Your training</p>
                <h1 class="text-2xl font-medium tracking-tight text-primary sm:text-3xl">Every set counts.</h1>
                <p class="text-sm text-secondary">A clearer view of what you train, week by week.</p>
            </div>
            <Link
                :href="trainingUrl"
                class="inline-flex items-center gap-2 rounded-lg bg-accent px-4 py-3 text-sm font-medium text-white transition hover:bg-orange-hover focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-text-orange"
            >
                <Icon icon="ic:baseline-plus" width="18" />
                {{ training.activePlan ? "Open workout" : "View mesocycles" }}
            </Link>
        </header>

        <section aria-labelledby="this-week-heading" class="flex flex-col gap-3">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="this-week-heading" class="text-sm font-medium text-primary">This week so far</h2>
                <span class="text-xs text-helper">{{ currentWeek.range }}</span>
            </div>
            <dl class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="flex flex-col gap-2 rounded-xl border border-border-orange/40 bg-layer p-4">
                    <dt class="text-xs text-secondary">Completed sets</dt>
                    <dd class="text-3xl font-medium tabular-nums tracking-tight text-text-orange">
                        {{ currentWeek.sets }}
                    </dd>
                </div>
                <div class="flex flex-col gap-2 rounded-xl border border-layer-border bg-layer p-4">
                    <dt class="text-xs text-secondary">Muscle groups trained</dt>
                    <dd class="text-3xl font-medium tabular-nums tracking-tight text-primary">{{ trainedGroups }}</dd>
                </div>
                <div class="flex flex-col gap-2 rounded-xl border border-layer-border bg-layer p-4">
                    <dt class="text-xs text-secondary">Finished sessions</dt>
                    <dd class="text-3xl font-medium tabular-nums tracking-tight text-primary">
                        {{ currentWeek.sessions }}
                    </dd>
                </div>
                <div class="flex flex-col gap-2 rounded-xl border border-layer-border bg-layer p-4">
                    <dt class="text-xs text-secondary">Planned sets / week</dt>
                    <dd class="text-3xl font-medium tabular-nums tracking-tight text-primary">
                        {{ training.activePlan?.setsPerWeek ?? "—" }}
                    </dd>
                </div>
            </dl>
        </section>

        <section
            aria-labelledby="weekly-activity-heading"
            class="flex flex-col gap-5 rounded-xl border border-layer-border bg-layer p-4 sm:p-5"
        >
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex flex-col gap-1">
                    <h2 id="weekly-activity-heading" class="text-lg font-medium tracking-tight text-primary">
                        Training over time
                    </h2>
                    <p class="text-sm text-secondary">Completed sets per muscle group, each week.</p>
                </div>
                <label class="flex flex-col gap-1.5 text-xs text-secondary">
                    Muscle group
                    <select
                        v-model="selectedGroup"
                        class="rounded-lg border-layer-border bg-input py-2 text-sm text-primary focus:border-helper focus:ring-helper"
                    >
                        <option :value="null">All muscle groups</option>
                        <option v-for="group in training.muscleGroups" :key="group.id" :value="group.id">
                            {{ group.name }}
                        </option>
                    </select>
                </label>
            </div>
            <div v-if="hasActivity" class="h-64 w-full sm:h-72">
                <WeeklySets :weeks="training.weeks" :groups="training.muscleGroups" :selected-group="selectedGroup" />
            </div>
            <div
                v-else
                class="flex min-h-[200px] flex-col items-center justify-center gap-3 rounded-lg border border-dashed border-layer-border bg-input px-5 text-center"
            >
                <Icon icon="heroicons:chart-bar" width="30" class="text-text-orange" />
                <p class="text-base font-medium text-primary">Your next set starts the story.</p>
                <p class="max-w-sm text-sm text-secondary">
                    Complete a set in a workout to start building your weekly training history.
                </p>
            </div>
            <p class="border-t border-layer-border pt-3 text-xs leading-relaxed text-helper">
                Monday–Sunday · {{ training.timezone }} · Sets count when marked complete, including workouts still in
                progress. Each set is attributed to its exercise’s primary muscle group.
            </p>
        </section>

        <section
            aria-labelledby="weekly-breakdown-heading"
            class="flex flex-col gap-4 rounded-xl border border-layer-border bg-layer p-4 sm:p-5"
        >
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-col gap-1">
                    <h2 id="weekly-breakdown-heading" class="text-lg font-medium tracking-tight text-primary">
                        Where your sets went
                    </h2>
                    <p class="text-sm text-secondary">
                        {{ selectedWeek.isCurrent ? "This week so far" : "Completed training" }} ·
                        {{ selectedWeek.range }}
                    </p>
                </div>
                <label class="flex flex-col gap-1.5 text-xs text-secondary">
                    Week starting
                    <select
                        v-model="selectedWeekIndex"
                        class="rounded-lg border-layer-border bg-input py-2 text-sm text-primary focus:border-helper focus:ring-helper"
                    >
                        <option v-for="(week, index) in training.weeks" :key="week.start" :value="index">
                            {{ week.label }}{{ week.isCurrent ? " · This week" : "" }}
                        </option>
                    </select>
                </label>
            </div>

            <div v-if="selectedRows.length" class="overflow-hidden rounded-lg border border-layer-border">
                <table class="w-full text-left text-sm">
                    <caption class="sr-only">Sets per primary muscle group for {{ selectedWeek.range }}</caption>
                    <thead class="bg-input text-xs text-secondary">
                        <tr>
                            <th scope="col" class="px-3 py-3 font-medium sm:px-4">Muscle group</th>
                            <th scope="col" class="px-3 py-3 text-right font-medium sm:px-4">Completed</th>
                            <th
                                v-if="selectedWeek.isCurrent"
                                scope="col"
                                class="px-3 py-3 text-right font-medium sm:px-4"
                            >
                                Current plan
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-layer-border">
                        <tr v-for="group in selectedRows" :key="group.id">
                            <th scope="row" class="px-3 py-3 font-normal text-primary sm:px-4">{{ group.name }}</th>
                            <td class="px-3 py-3 sm:px-4">
                                <div class="flex items-center justify-end gap-3">
                                    <div
                                        class="hidden h-1 w-20 overflow-hidden rounded-full bg-layer-light sm:block"
                                        aria-hidden="true"
                                    >
                                        <div
                                            class="h-full rounded-full bg-text-orange"
                                            :style="{ width: `${(group.completed / maxCompleted) * 100}%` }"
                                        />
                                    </div>
                                    <span class="min-w-[3rem] text-right font-medium tabular-nums text-primary">
                                        {{ group.completed }} sets
                                    </span>
                                </div>
                            </td>
                            <td
                                v-if="selectedWeek.isCurrent"
                                class="px-3 py-3 text-right tabular-nums text-secondary sm:px-4"
                            >
                                {{ group.plannedSets === null ? "—" : `${group.plannedSets} sets` }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="rounded-lg bg-input p-4 text-sm text-secondary">No completed sets for this selection.</p>

            <div
                v-if="training.activePlan"
                class="flex flex-wrap items-center justify-between gap-2 border-t border-layer-border pt-3 text-xs text-secondary"
            >
                <span>
                    Current plan:
                    <span class="text-primary">{{ training.activePlan.name }}</span>
                    · {{ training.activePlan.daysPerWeek }} days / week
                </span>
                <Link :href="route('mesocycles.edit', training.activePlan.id)" class="text-text-orange hover:underline">
                    View plan
                </Link>
            </div>
            <p class="text-xs leading-relaxed text-helper">
                Completed counts include all your mesocycles. Current-plan counts describe your active mesocycle’s saved
                weekly routine and are shown only for this week.
            </p>
        </section>

        <section aria-labelledby="recent-workouts-heading" class="flex flex-col gap-3">
            <div class="flex items-center justify-between gap-3">
                <h2 id="recent-workouts-heading" class="text-lg font-medium tracking-tight text-primary">
                    Recent sessions
                </h2>
                <Link :href="route('mesocycles')" class="text-xs text-secondary transition hover:text-primary">
                    All mesocycles →
                </Link>
            </div>
            <div
                v-if="!training.recentWorkouts.length"
                class="rounded-xl border border-layer-border bg-layer p-5 text-sm text-secondary"
            >
                Finished workouts will appear here, with the sets you completed for each muscle group.
            </div>
            <article
                v-for="workout in training.recentWorkouts"
                :key="workout.id"
                class="flex flex-col gap-4 rounded-xl border border-layer-border bg-layer p-4 sm:p-5"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="flex flex-col gap-1">
                        <Link
                            :href="route('days.show', { mesocycle: workout.mesocycleId, day: workout.id })"
                            class="text-base font-medium text-primary transition hover:text-text-orange"
                        >
                            {{ workout.label }}
                        </Link>
                        <p class="text-xs text-helper">{{ workout.mesocycleName }} · {{ workout.finishedAt }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-medium tabular-nums text-primary">
                            {{ workout.sets }}
                            <span class="text-xs font-normal text-secondary">sets</span>
                        </p>
                        <p class="text-xs text-helper">{{ workout.exercises }} exercises</p>
                    </div>
                </div>
                <ul class="flex list-none flex-wrap gap-2">
                    <li
                        v-for="group in workout.muscleGroups"
                        :key="group.name"
                        class="rounded-md border border-layer-border bg-input px-2.5 py-1.5 text-xs text-secondary"
                    >
                        {{ group.name }}
                        <span class="pl-1 font-medium tabular-nums text-primary">{{ group.sets }}</span>
                    </li>
                </ul>
                <details class="border-t border-layer-border pt-3 text-xs text-secondary">
                    <summary class="cursor-pointer transition hover:text-primary">Load &amp; volume</summary>
                    <dl class="mt-3 flex flex-wrap gap-x-6 gap-y-2">
                        <div class="flex gap-2">
                            <dt>Total logged load</dt>
                            <dd class="tabular-nums text-primary">
                                {{ formatNumber(workout.load) }} {{ workout.unit }}
                            </dd>
                        </div>
                        <div class="flex gap-2">
                            <dt>Volume</dt>
                            <dd class="tabular-nums text-primary">
                                {{ formatNumber(workout.volume) }} {{ workout.unit }} × reps
                            </dd>
                        </div>
                    </dl>
                </details>
            </article>
        </section>
    </div>
</template>
