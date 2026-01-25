<template>
    <Modal :show="showModal" @close="showModal = false">
        <ModalsHeader title="Exercises" class="border-b border-layer-border p-2" @close="showModal = false" />

        <div class="flex max-h-[80vh] flex-col overflow-hidden p-2">
            <InputText class="w-full" placeholder="Search exercise" v-model="search" />

            <div class="scrollbar mt-2 flex flex-1 flex-col overflow-y-auto">
                <Collapsible
                    class="flex-none border-b border-main-border"
                    :title="muscle.name"
                    v-for="(muscle, mIdx) in filteredGroups"
                    v-if="!props.onlyOneMuscleGroup"
                >
                    <ul>
                        <li
                            v-for="(exercise, eIdx) in muscle.exercises"
                            :key="eIdx"
                            @click="select(exercise.id)"
                            :class="[selected == exercise.id ? 'bg-layer-light text-primary' : 'text-secondary']"
                            class="flex cursor-pointer items-center justify-between border-b border-main-border p-2 transition last:border-b-0 hover:bg-layer-light hover:text-primary"
                        >
                            <div class="flex flex-col gap-1">
                                <p>{{ exercise.name }}</p>
                                <span
                                    class="w-fit rounded-sm border border-layer-border bg-layer-light px-1 text-sm text-helper"
                                >
                                    {{ capitalize(exercise.exercise_type) }}
                                </span>
                            </div>

                            <Icon
                                icon="ri:checkbox-circle-fill"
                                v-if="selected == exercise.id"
                                class="text-text-green"
                            />
                        </li>
                    </ul>
                </Collapsible>

                <ul v-else>
                    <li
                        v-for="(exercise, eIdx) in filteredExercises"
                        :key="eIdx"
                        @click="select(exercise.id)"
                        :class="[selected == exercise.id ? 'bg-input text-primary' : 'text-secondary']"
                        class="flex cursor-pointer items-center justify-between border-b border-main-border p-2 transition last:border-b-0 hover:bg-layer-light hover:text-primary"
                    >
                        <div class="flex flex-col gap-1">
                            <p>{{ exercise.name }}</p>
                            <span
                                class="w-fit rounded-sm border border-layer-border bg-layer-light px-1 text-sm text-helper"
                            >
                                {{ capitalize(exercise.exercise_type) }}
                            </span>
                        </div>

                        <Icon icon="ri:checkbox-circle-fill" v-if="selected == exercise.id" class="text-text-green" />
                    </li>
                </ul>
            </div>

            <ButtonPrimary class="w-full disabled:opacity-75" :class="{ disabled: !selected }">Select</ButtonPrimary>
        </div>
    </Modal>
</template>
<script setup lang="ts">
import ButtonPrimary from "@/Components/Button/Primary.vue";
import InputText from "@/Components/Input/Text.vue";
import Modal from "@/Components/Modal.vue";
import ModalsHeader from "@/Components/Modals/Header.vue";
import Collapsible from "@/Components/Ui/Collapsible.vue";
import { useExerciseStore } from "@/stores/exerciseStore";
import { Icon } from "@iconify/vue";
import { storeToRefs } from "pinia";
import { computed, ref, watch } from "vue";

const showModal = defineModel<boolean>();

const props = defineProps<{
    onlyOneMuscleGroup: number | string | undefined;
    preSelected?: number | undefined;
}>();

const emit = defineEmits<{
    (e: "select", id: number): void;
}>();

const { exercisesByMuscle } = storeToRefs(useExerciseStore());

const selected = ref<number | undefined>(props.preSelected);
const search = ref<string>("");

watch(
    () => props.preSelected,
    (newVal) => {
        selected.value = newVal;
    },
    { immediate: true }
);

const filteredGroups = computed(() => {
    return exercisesByMuscle.value
        .map((mg: MuscleGroup) => {
            const hasExercises =
                mg.exercises?.filter((ex) => ex.name.toLowerCase().includes(search.value.toLowerCase())) || [];

            if (hasExercises?.length > 0) {
                return { ...mg, exercises: hasExercises };
            }

            return null;
        })
        .filter((mg) => mg !== null);
});

const filteredExercises = computed(() => {
    const exercises = exercisesByMuscle.value.find((mg) => mg.id == props.onlyOneMuscleGroup);

    return (exercises?.exercises || []).filter((ex) => ex.name.toLowerCase().includes(search.value.toLowerCase()));
});

function select(exerciseID: number): void {
    selected.value = exerciseID;

    emit("select", exerciseID);

    showModal.value = false;
    search.value = "";
}

function capitalize(string: string): string {
    return string.slice(0, 1).toUpperCase() + string.slice(1, string.length);
}
</script>
