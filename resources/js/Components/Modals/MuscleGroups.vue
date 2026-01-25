<template>
    <Modal :show="showModal" @close="showModal = false">
        <ModalsHeader title="Muscle Groups" class="border-b border-layer-border p-2" @close="showModal = false" />
        <ul class="scrollbar max-h-[80vh] overflow-y-auto">
            <li
                v-for="muscle in groups"
                :key="muscle.id"
                @click="select(muscle.id)"
                :class="[selected == muscle.id ? 'bg-layer-light text-primary' : 'text-secondary']"
                class="flex cursor-pointer items-center justify-between border-b border-main-border p-2 transition last:border-b-0 hover:bg-layer-light hover:text-primary"
            >
                {{ muscle.name }}

                <Icon icon="ri:checkbox-circle-fill" v-if="selected == muscle.id" class="text-text-green" />
            </li>
        </ul>
    </Modal>
</template>
<script setup lang="ts">
import Modal from "@/Components/Modal.vue";
import ModalsHeader from "@/Components/Modals/Header.vue";
import { Icon } from "@iconify/vue";
import { computed, ref, watch } from "vue";

const showModal = defineModel<boolean>();

const props = defineProps<{
    muscleGroups: Record<number, MuscleGroup> | MuscleGroup[];
    preSelected?: number;
}>();

const emit = defineEmits<{
    (e: "select", id: number): void;
}>();

const selected = ref<number | undefined>(props.preSelected);

watch(
    () => props.preSelected,
    (newVal) => {
        selected.value = newVal;
    },
    { immediate: true }
);

const groups = computed(() => {
    if (Array.isArray(props.muscleGroups)) {
        return props.muscleGroups;
    }

    return Object.values(props.muscleGroups);
});

function select(id: number): void {
    selected.value = id;
    emit("select", id);
    showModal.value = false;
    selected.value = undefined;
}
</script>
