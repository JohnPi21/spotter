<template>
    <ModalsHeader title="Muscle Groups" />
    <ul>
        <li
            v-for="(muscle, idx) in props"
            :key="idx"
            @click="select(idx)"
            :class="[selected == idx ? 'bg-layer-light text-primary' : 'text-secondary']"
            class="flex cursor-pointer items-center justify-between border-b border-main-border p-2 transition last:border-b-0 hover:bg-layer-light hover:text-primary"
        >
            {{ muscle }}

            <Icon icon="ri:checkbox-circle-fill" v-if="selected == idx" class="text-text-green" />
        </li>
    </ul>
    <!-- <ButtonPrimary @click="modalStore.confirmClose(selected)" class="w-full mt-1">Select</ButtonPrimary> -->
    <!-- <button class="bg-accent px-2 py-1 mt-1 rounded w-full" @click="modalStore.confirmClose(selected)">Select</button> -->
</template>
<script setup>
import ModalsHeader from "@components/Modals/Header.vue";
import { Icon } from "@iconify/vue";
import { useModalStore } from "@stores/modalStore";
import { ref } from "vue";
// import ButtonPrimary from '@components/Button/Primary.vue';

const modalStore = useModalStore();

const props = modalStore.data;

const selected = ref(null);

function select(idx) {
    selected.value = idx;

    modalStore.confirmClose(selected.value);
}
</script>
