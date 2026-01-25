<template>
    <Modal :show="showModal" @close="close">
        <ModalsHeader title="Add Note" class="border-b border-layer-border p-2" @close="close" />
        <div class="flex max-h-[80vh] flex-col gap-3 p-2">
            <textarea
                v-model="note"
                rows="5"
                placeholder="Add note"
                class="w-full rounded-md border-input-border bg-input p-2 text-gray-300 shadow-sm focus:border-accent focus:ring-accent"
            />
            <div class="flex gap-2">
                <ButtonSecondary class="w-full" @click="close">Cancel</ButtonSecondary>
                <ButtonPrimary class="w-full" :disabled="!note.trim()" @click="save">Save Note</ButtonPrimary>
            </div>
        </div>
    </Modal>
</template>

<script setup lang="ts">
import ButtonPrimary from "@/Components/Button/Primary.vue";
import ButtonSecondary from "@/Components/Button/Secondary.vue";
import Modal from "@/Components/Modal.vue";
import ModalsHeader from "@/Components/Modals/Header.vue";
import { ref, watch } from "vue";

const showModal = defineModel<boolean>();

const props = defineProps<{
    initialNote?: string;
}>();

const emit = defineEmits<{
    (e: "save", note: string): void;
}>();

const note = ref<string>(props.initialNote ?? "");

watch(
    () => showModal.value,
    (isOpen) => {
        if (isOpen) {
            note.value = props.initialNote ?? "";
        }
    }
);

function close(): void {
    showModal.value = false;
}

function save(): void {
    emit("save", note.value);
    close();
}
</script>
