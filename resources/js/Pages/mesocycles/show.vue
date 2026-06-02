<template>
    <div class="mx-auto my-2 flex max-w-[768px] flex-col gap-3">
        <UiBox class="flex flex-col">
            <ModalsExercises
                v-model="exercisesModal"
                :only-one-muscle-group="0"
                @select="(exerciseId: number) => exerciseSelected(exerciseId)"
            />
            <ModalsExerciseNote v-model="noteModalOpen" :initial-note="noteModalInitial" @save="saveNote" />

            <div class="mb-2 flex items-center justify-between">
                <div class="flex flex-col">
                    <p class="text-sm text-secondary">{{ mesocycle.name }}</p>
                    <h3>WEEK {{ day.week }} DAY {{ day.day_order }}: {{ day.label }}</h3>
                </div>

                <div class="align-end flex items-center gap-3">
                    <div class="flex items-center gap-1 rounded bg-green px-2" v-if="isDayFinished">
                        <p>Completed</p>
                        <Icon icon="ep:success-filled" />
                    </div>
                    <Icon
                        icon="quill:calendar"
                        width="18px"
                        @click="showCalendar = !showCalendar"
                        class="cursor-pointer transition hover:text-secondary"
                    />

                    <UiDropdownMenu idx="1" left="-50px">
                        <template #header>
                            <div class="hover:cursor-pointer">
                                <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                            </div>
                        </template>

                        <template v-for="(section, index) in dropdownItems" :key="index" v-slot="slotProps">
                            <li class="flex !justify-center bg-layer">{{ section.section }}</li>
                            <li
                                v-for="(item, i) in section.items"
                                :key="i"
                                @click="
                                    item?.action ? item.action() : '';
                                    slotProps.toggle;
                                "
                            >
                                <Icon :icon="item.icon" />
                                {{ item.label }}
                            </li>
                        </template>
                    </UiDropdownMenu>
                </div>
            </div>

            <!-- ===== Calendar ===== -->
            <div class="flex justify-between gap-1" v-if="showCalendar">
                <div class="flex flex-1 flex-col items-center gap-1" v-for="(week, idx) in mesocycle.calendar">
                    <p>W {{ idx }}</p>
                    <Link
                        :href="route('days.show', { mesocycle: mesocycle.id, day: weekDay.id })"
                        class="flex w-full flex-col items-center justify-center rounded-sm border border-transparent bg-main px-2 py-1 text-center"
                        v-for="(weekDay, dayIdx) in week"
                        :class="[
                            { 'bg-orange-700': isActiveDay(weekDay.id) },
                            { 'border !border-border-green opacity-50': weekDay.finished_at },
                        ]"
                    >
                        <p>D{{ dayIdx + 1 }}</p>
                    </Link>
                </div>
            </div>

            <button
                type="button"
                class="mt-3 flex items-center justify-between gap-3 rounded border border-input-border bg-input px-3 py-2 text-left transition hover:bg-layer-light disabled:cursor-not-allowed disabled:opacity-50"
                @click="openBodyWeightModal()"
                :disabled="isDayFinished"
            >
                <div class="flex items-center gap-2">
                    <Icon icon="hugeicons:weight-scale" width="18px" class="text-accent" />
                    <div class="flex flex-col">
                        <span class="text-sm text-secondary">Body weight</span>
                        <span v-if="hasBodyWeight">{{ day.body_weight }} {{ mesocycle.unit }}</span>
                        <span v-else>Insert weight for this day</span>
                    </div>
                </div>

                <Icon :icon="hasBodyWeight ? 'material-symbols:edit-outline' : 'material-symbols:add'" width="18px" />
            </button>
        </UiBox>

        <!-- ===== Errors ===== -->
        <UiErrors :errors="errors" />

        <!-- ===== Exercises ===== -->
        <UiBox class="flex flex-col gap-2" v-for="(dayExercise, exercise_idx) in day.day_exercises">
            <div class="flex items-center justify-between">
                <div class="rounded bg-orange-700 px-2">
                    {{ dayExercise.exercise.muscle_group.name }}
                </div>

                <div class="align-end flex items-center gap-4">
                    <a target="_blank" :href="`https://www.youtube.com/watch?v=${dayExercise.exercise.youtube_id}`">
                        <Icon icon="line-md:youtube" width="20px" />
                    </a>

                    <UiDropdownMenu :idx="exercise_idx" left="-50px">
                        <template #header>
                            <div class="hover:cursor-pointer">
                                <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                            </div>
                        </template>

                        <template v-slot="slotProps">
                            <li
                                v-for="(item, i) in exerciseDropdownItems"
                                :key="i"
                                :class="item.class"
                                @click="
                                    item.action(dayExercise.id, dayExercise.note);
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

            <div class="flex flex-col">
                <h4>{{ dayExercise.exercise.name }}</h4>
                <p class="text-sm text-secondary">{{ dayExercise.exercise.exercise_type }}</p>
            </div>

            <div
                v-if="dayExercise?.note"
                class="flex w-full items-center justify-between gap-2 rounded bg-layer-light px-2 py-1 text-sm text-secondary"
            >
                <div class="flex flex-1 items-start gap-2">
                    <Icon icon="clarity:note-line" width="15px" class="mt-[2px] text-primary" />
                </div>
                <div class="break-words break-all">
                    {{ dayExercise?.note }}
                </div>
                <button
                    type="button"
                    class="flex items-center text-secondary transition hover:text-red"
                    @click="removeNote(dayExercise.id)"
                    :disabled="isDayFinished"
                >
                    <Icon icon="material-symbols:delete-outline" width="16" />
                </button>
            </div>

            <div class="grid gap-3" :class="setGridClass(dayExercise)">
                <div
                    class="flex h-fit w-fit cursor-pointer items-center self-center rounded-full bg-input p-1"
                    @click="addSet(dayExercise.id)"
                >
                    <Icon icon="material-symbols:add" size="1rem" />
                </div>
                <div class="text-center" v-if="!isBodyWeightExercise(dayExercise)">WEIGHT</div>
                <div class="text-center">REPS</div>
                <div></div>
                <div class="justify-self-end">LOG</div>
            </div>

            <div
                class="grid items-center gap-3"
                :class="setGridClass(dayExercise)"
                v-for="(set, set_idx) in dayExercise.sets"
            >
                <div class="flex items-center justify-start">
                    <UiDropdownMenu :idx="set_idx" left="50px">
                        <template #header>
                            <div class="hover:cursor-pointer">
                                <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                            </div>
                        </template>

                        <li
                            v-for="(item, i) in setDropdownItems"
                            :key="i"
                            :class="item.class"
                            @click="item.action(dayExercise.id, set.id)"
                        >
                            <Icon :icon="item.icon" />
                            {{ item.label }}
                        </li>
                    </UiDropdownMenu>
                </div>

                <div v-if="!isBodyWeightExercise(dayExercise)">
                    <InputText
                        :placeholder="set?.target_weight ?? mesocycle.unit"
                        v-model="set.weight"
                        class="w-full"
                        :id="exercise_idx + '-' + set_idx + '-weight'"
                        :disabled="isDayFinished"
                    />
                </div>
                <div>
                    <InputText
                        :placeholder="getRepsPlaceholder(set)"
                        v-model="set.reps"
                        class="w-full"
                        :id="exercise_idx + '-' + set_idx + '-reps'"
                        :disabled="isDayFinished"
                    />
                </div>
                <div class="flex justify-center">
                    <Icon
                        v-if="getSetStatus(set) === 'under'"
                        icon="material-symbols:trending-down-rounded"
                        class="text-yellow-400"
                        width="18px"
                    />
                    <Icon
                        v-else-if="getSetStatus(set) === 'hit'"
                        icon="material-symbols:track-changes"
                        class="text-green"
                        width="18px"
                    />
                    <Icon
                        v-else-if="getSetStatus(set) === 'over'"
                        icon="material-symbols:trending-up-rounded"
                        class="text-orange-500"
                        width="18px"
                    />
                </div>
                <div class="flex items-center justify-end">
                    <Checkbox
                        :checked="set.finished_at"
                        :value="set.finished_at"
                        v-model="set.status"
                        true-value="1"
                        false-value="0"
                        @change="checkSet(set, dayExercise)"
                        class="mr-2"
                        :id="exercise_idx + '-' + set_idx + '-status'"
                        :disabled="isDayFinished"
                    />
                </div>
            </div>
        </UiBox>

        <ButtonPrimary @click="toggleDay(day.id)" v-if="!isDayFinished">Finish Workout</ButtonPrimary>
        <ButtonSecondary @click="toggleDay(day.id)" v-else>Reactivate</ButtonSecondary>

        <Modal :show="bodyWeightModal" max-width="sm" @close="closeBodyWeightModal">
            <form class="flex flex-col gap-4 p-4" @submit.prevent="submitBodyWeight">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3>Body weight</h3>
                        <p class="text-sm text-secondary">Set your weight for this workout day.</p>
                    </div>

                    <button type="button" class="rounded p-1 transition hover:bg-input" @click="closeBodyWeightModal">
                        <Icon icon="material-symbols:close-rounded" width="20px" />
                    </button>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-sm text-secondary" for="body-weight">Weight ({{ mesocycle.unit }})</label>
                    <InputText
                        id="body-weight"
                        type="number"
                        step="0.1"
                        min="20"
                        max="400"
                        v-model="bodyWeightForm.bodyWeight"
                        class="w-full"
                        autofocus
                    />
                    <p class="text-sm text-red" v-if="bodyWeightForm.errors.bodyWeight">
                        {{ bodyWeightForm.errors.bodyWeight }}
                    </p>
                </div>

                <div class="flex justify-end gap-2">
                    <ButtonSecondary type="button" @click="closeBodyWeightModal">Cancel</ButtonSecondary>
                    <ButtonPrimary type="submit" :disabled="bodyWeightForm.processing">Save</ButtonPrimary>
                </div>
            </form>
        </Modal>
    </div>
</template>
<script setup lang="ts">
import ButtonPrimary from "@/Components/Button/Primary.vue";
import ButtonSecondary from "@/Components/Button/Secondary.vue";
import Checkbox from "@/Components/Input/Checkbox.vue";
import InputText from "@/Components/Input/Text.vue";
import Modal from "@/Components/Modal.vue";
import ModalsExerciseNote from "@/Components/Modals/ExerciseNote.vue";
import ModalsExercises from "@/Components/Modals/Exercises.vue";
import UiBox from "@/Components/Ui/Box.vue";
import UiDropdownMenu from "@/Components/Ui/DropdownMenu.vue";
import UiErrors from "@/Components/Ui/Errors.vue";
import { useDay } from "@/Composables/useDay";
import { useExercise } from "@/Composables/useExercise";
import { useMesocycle } from "@/Composables/useMesocycle";
import { useSet } from "@/Composables/useSet";
import { Icon } from "@iconify/vue";
import { Link, useForm } from "@inertiajs/vue3";
import { computed, onMounted, ref } from "vue";

const props = defineProps<{
    mesocycle: Mesocycle;
    errors?: object;
}>();

const day = computed<Day>(() => props.mesocycle.day);
const exercisesModal = ref<boolean>();
const noteModalOpen = ref<boolean>(false);
const noteModalExerciseId = ref<number | null>(null);
const noteModalInitial = ref<string>("");
const showCalendar = ref<boolean>(true);
const isDayFinished = computed<boolean>(() => {
    return !!day.value.finished_at;
});
const pendingExerciseAction = ref<ExerciseAction | null>(null);
const pendingBodyWeightSet = ref<PendingBodyWeightSet | null>(null);
const bodyWeightModal = ref<boolean>(false);
const bodyWeightForm = useForm({
    bodyWeight: props.mesocycle.day.body_weight ?? "",
});
const { toggleDay } = useMesocycle();
const { addExercise, moveDown, moveUp, removeExercise, replaceExercise, addNote, removeNote } = useExercise(day);
const { updateSet, addSet, removeSet } = useSet();
const { isActiveDay } = useDay();

onMounted(() => {});

async function exerciseSelected(exerciseId: number) {
    pendingExerciseAction.value?.(exerciseId);
}

function openNoteModal(dayExerciseId: number, currentNote?: string | null): void {
    noteModalExerciseId.value = dayExerciseId;
    noteModalInitial.value = currentNote ?? "";
    noteModalOpen.value = true;
}

function saveNote(note: string): void {
    if (noteModalExerciseId.value == null) return;

    addNote(noteModalExerciseId.value, note);
    noteModalExerciseId.value = null;
}

function openExerciseModal(action: (exerciseId: number) => void): void {
    pendingExerciseAction.value = action;
    exercisesModal.value = true;
}

type SetStatus = "under" | "hit" | "over";
type ExerciseAction = (dayExerciseId: number) => void;
type PendingBodyWeightSet = {
    set: ExerciseSet;
    dayExerciseId: number;
};

const hasBodyWeight = computed<boolean>(() => day.value.body_weight != null);

function isBodyWeightExercise(dayExercise: DayExercise): boolean {
    return dayExercise.exercise.exercise_type === "bodyweight-only";
}

function setGridClass(dayExercise: DayExercise): string {
    return isBodyWeightExercise(dayExercise)
        ? "grid-cols-[24px_minmax(0,1fr)_24px_24px]"
        : "grid-cols-[24px_minmax(0,1fr)_minmax(0,1fr)_24px_24px]";
}

function openBodyWeightModal(pendingSet: PendingBodyWeightSet | null = null): void {
    pendingBodyWeightSet.value = pendingSet;
    bodyWeightForm.bodyWeight = day.value.body_weight ?? "";
    bodyWeightForm.clearErrors();
    bodyWeightModal.value = true;
}

function closeBodyWeightModal(): void {
    if (bodyWeightForm.processing) {
        return;
    }

    bodyWeightModal.value = false;

    if (pendingBodyWeightSet.value) {
        pendingBodyWeightSet.value.set.status = false;
        pendingBodyWeightSet.value = null;
    }
}

function submitBodyWeight(): void {
    bodyWeightForm.patch(route("days.body-weight", { day: day.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            const submittedBodyWeight = Number(bodyWeightForm.bodyWeight);

            day.value.body_weight = Number.isFinite(submittedBodyWeight) ? submittedBodyWeight : day.value.body_weight;
            bodyWeightModal.value = false;

            if (pendingBodyWeightSet.value) {
                const { set, dayExerciseId } = pendingBodyWeightSet.value;

                set.weight = day.value.body_weight ?? undefined;
                updateSet(set, dayExerciseId);
                pendingBodyWeightSet.value = null;
            }
        },
    });
}

function checkSet(set: ExerciseSet, dayExercise: DayExercise): void {
    if (!set.status) {
        return;
    }

    if (!isBodyWeightExercise(dayExercise)) {
        updateSet(set, dayExercise.id);

        return;
    }

    if (!hasBodyWeight.value) {
        openBodyWeightModal({ set, dayExerciseId: dayExercise.id });

        return;
    }

    set.weight = day.value.body_weight ?? undefined;
    updateSet(set, dayExercise.id);
}

function hasRepTarget(set: ExerciseSet): boolean {
    return set.min_reps != null || set.max_reps != null;
}

function formatRange(min?: number, max?: number): string | null {
    if (min == null && max == null) {
        return null;
    }

    if (min != null && max != null) {
        return min === max ? `${min}` : `${min}-${max}`;
    }

    return `${min ?? max}`;
}

function formatRir(set: ExerciseSet): string | null {
    return formatRange(set.min_rir, set.max_rir);
}

function getRepsPlaceholder(set: ExerciseSet): string {
    const repRange = formatRange(set.min_reps, set.max_reps);

    if (repRange) {
        const rirRange = formatRir(set);

        return rirRange ? `${repRange} @ ${rirRange}` : repRange;
    }

    if (set.target_reps != null) {
        return `${set.target_reps}`;
    }

    return "3 RIR";
}

function parseSetReps(reps?: number | string): number | null {
    if (reps == null || reps === "") {
        return null;
    }

    const parsedReps = Number(reps);

    return Number.isFinite(parsedReps) ? parsedReps : null;
}

function getSetStatus(set: ExerciseSet): SetStatus | null {
    if (!hasRepTarget(set)) {
        return null;
    }

    const reps = parseSetReps(set.reps);

    if (reps == null) {
        return null;
    }

    if (set.min_reps != null && reps < set.min_reps) {
        return "under";
    }

    if (set.max_reps != null && reps > set.max_reps) {
        return "over";
    }

    return "hit";
}

const dropdownItems = [
    {
        section: "Mesocycle",
        items: [
            { icon: "clarity:note-line", label: "View Notes" },
            { icon: "tabler:italic", label: "Rename" },
            { icon: "bi:graph-up-arrow", label: "Progressions" },
            { icon: "carbon:book", label: "Summary" },
            { icon: "carbon:stop-outline", label: "End" },
        ],
    },
    {
        section: "Workout",
        items: [
            { icon: "icon-park-outline:write", label: "New Note" },
            { icon: "tabler:italic", label: "Relabel" },
            {
                icon: "material-symbols:add",
                label: "Add Exercise",
                action: () => openExerciseModal((exerciseId: number) => addExercise(exerciseId)),
            },
            { icon: "hugeicons:weight-scale", label: "Bodyweight", action: () => openBodyWeightModal() },
            { icon: "carbon:reset", label: "Reset" },
            { icon: "carbon:stop-outline", label: "End" },
        ],
    },
];

const exerciseDropdownItems = [
    {
        icon: "icon-park-outline:write",
        label: "Note",
        action: (dayExerciseID: number, currentNote?: string | null) => openNoteModal(dayExerciseID, currentNote),
    },
    {
        icon: "mdi:arrow-up",
        label: "Move Up",
        action: (dayExerciseId: number) => moveUp(dayExerciseId),
    },
    {
        icon: "mdi:arrow-down",
        label: "Move Down",
        action: (dayExerciseId: number) => moveDown(dayExerciseId),
    },
    {
        icon: "ph:swap",
        label: "Replace",
        action: (dayExerciseId: number) =>
            openExerciseModal((newExerciseId: number) => replaceExercise(dayExerciseId, newExerciseId)),
    },
    {
        icon: "material-symbols:add",
        label: "Add Set",
        action: (dayExerciseId: number) => addSet(dayExerciseId),
    },
    {
        icon: "ix:skip",
        label: "Skip",
        action: () => console.log("Skip clicked"),
    },
    {
        icon: "material-symbols:delete-outline",
        label: "Delete",
        class: "!text-red",
        action: (dayExerciseId: number) => removeExercise(dayExerciseId, day.value),
    },
];

const setDropdownItems = [
    {
        icon: "material-symbols:delete-outline",
        label: "Change Type",
        action: () => console.log("Should Open Modal"),
    },

    {
        icon: "material-symbols:delete-outline",
        label: "Delete",
        class: "!text-red",
        action: (dayExerciseId: number, setId: number) => removeSet(dayExerciseId, setId),
    },
];
</script>
