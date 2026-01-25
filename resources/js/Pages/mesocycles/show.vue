<template>
    <div class="mx-auto my-2 flex max-w-[768px] flex-col gap-3">
        <UiBox class="flex flex-col">
            <ModalsExercises
                v-model="exercisesModal"
                :only-one-muscle-group="0"
                @select="(exerciseID: number) => exerciseSelected(exerciseID)"
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

            <div class="grid grid-cols-6 gap-5">
                <div
                    class="flex h-fit w-fit cursor-pointer items-center self-center rounded-full bg-input p-1"
                    @click="addSet(dayExercise.id)"
                >
                    <Icon icon="material-symbols:add" size="1rem" />
                </div>
                <div class="col-span-2 text-center">WEIGHT</div>
                <div class="col-span-2 text-center">REPS</div>
                <div class="justify-self-end">LOG</div>
            </div>

            <div class="grid grid-cols-6 gap-5" v-for="(set, set_idx) in dayExercise.sets">
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

                <div class="col-span-2">
                    <InputText
                        :placeholder="set?.target_weight ?? mesocycle.unit"
                        v-model="set.weight"
                        class="w-full"
                        :id="exercise_idx + '-' + set_idx + '-weight'"
                        :disabled="isDayFinished"
                    />
                </div>
                <div class="col-span-2">
                    <InputText
                        :placeholder="set?.target_reps != null ? `${set.target_reps}` : '3 RIR'"
                        v-model="set.reps"
                        class="w-full"
                        :id="exercise_idx + '-' + set_idx + '-reps'"
                        :disabled="isDayFinished"
                    />
                </div>
                <div class="flex items-center justify-end">
                    <Checkbox
                        :checked="set.finished_at"
                        :value="set.finished_at"
                        v-model="set.status"
                        true-value="1"
                        false-value="0"
                        @change="updateSet(set, dayExercise.id)"
                        class="mr-2"
                        :id="exercise_idx + '-' + set_idx + '-status'"
                        :disabled="isDayFinished"
                    />
                </div>
            </div>
        </UiBox>

        <ButtonPrimary @click="toggleDay(day.id)" v-if="!isDayFinished">Finish Workout</ButtonPrimary>
        <ButtonSecondary @click="toggleDay(day.id)" v-else>Reactivate</ButtonSecondary>
    </div>
</template>
<script setup lang="ts">
import ButtonPrimary from "@/Components/Button/Primary.vue";
import ButtonSecondary from "@/Components/Button/Secondary.vue";
import Checkbox from "@/Components/Input/Checkbox.vue";
import InputText from "@/Components/Input/Text.vue";
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
import { Link } from "@inertiajs/vue3";
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
const { toggleDay } = useMesocycle();
const { addExercise, moveDown, moveUp, removeExercise, replaceExercise, addNote, removeNote } = useExercise(day);
const { updateSet, addSet, removeSet } = useSet();
const { isActiveDay } = useDay();

onMounted(() => {});

async function exerciseSelected(exerciseID: number) {
    pendingExerciseAction.value?.(exerciseID);
}

function openNoteModal(dayExerciseID: number, currentNote?: string | null): void {
    noteModalExerciseId.value = dayExerciseID;
    noteModalInitial.value = currentNote ?? "";
    noteModalOpen.value = true;
}

function saveNote(note: string): void {
    if (noteModalExerciseId.value == null) return;

    addNote(noteModalExerciseId.value, note);
    noteModalExerciseId.value = null;
}

function openExerciseModal(action: (exerciseID: number) => void): void {
    pendingExerciseAction.value = action;
    exercisesModal.value = true;
}

type ExerciseAction = (dayExerciseID: number) => void;

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
                action: () => openExerciseModal((exerciseID: number) => addExercise(exerciseID)),
            },
            { icon: "hugeicons:weight-scale", label: "Bodyweight" },
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
        action: (dayExerciseID: number) => moveUp(dayExerciseID),
    },
    {
        icon: "mdi:arrow-down",
        label: "Move Down",
        action: (dayExerciseID: number) => moveDown(dayExerciseID),
    },
    {
        icon: "ph:swap",
        label: "Replace",
        action: (dayExerciseID: number) =>
            openExerciseModal((newExerciseID: number) => replaceExercise(dayExerciseID, newExerciseID)),
    },
    {
        icon: "material-symbols:add",
        label: "Add Set",
        action: (dayExerciseID: number) => addSet(dayExerciseID),
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
        action: (dayExerciseID: number) => removeExercise(dayExerciseID, day.value),
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
        action: (dayExerciseID: number, setID: number) => removeSet(dayExerciseID, setID),
    },
];
</script>
