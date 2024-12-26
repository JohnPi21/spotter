<template>
    <ModalsHeader title="Muscle Groups" />
    <ul>
        <li v-for="(muscle, idx) in props" :key="idx" @click="selected = idx"
            :class="[selected == idx ? 'bg-layer-light text-primary' : 'text-secondary']"
            class="p-2 border-b border-main-border cursor-pointer hover:text-primary transition hover:bg-layer-light last:border-b-0">

            {{ muscle }}
        </li>
    </ul>
    <button class="bg-accent px-2 py-1 mt-1 rounded w-full" @click="modalStore.closeModal(selected)">Select</button>
</template>
<script setup>
    import { ref } from 'vue';
    import ModalsHeader from '@components/Modals/Header.vue';
    import { useModalStore } from '@stores/modalStore';

    const modalStore = useModalStore();

    const props = modalStore.data;

    const selected = ref(null);
</script>