<template>
    <ModalsHeader title="Muscle Groups" />
    <ul>
        <li v-for="(muscle, idx) in props" :key="idx" @click="selected = idx"
            :class="[selected == idx ? 'bg-layer-light text-primary' : 'text-secondary']"
            class="p-2 border-b border-main-border cursor-pointer hover:text-primary transition hover:bg-layer-light last:border-b-0 flex items-center justify-between">
            {{ muscle }}

            <Icon icon="ri:checkbox-circle-fill" v-if="selected == idx" class="text-text-green" />
        </li>
    </ul>
    <ButtonPrimary @click="modalStore.confirmClose(selected)" class="w-full mt-1">Select</ButtonPrimary>
    <!-- <button class="bg-accent px-2 py-1 mt-1 rounded w-full" @click="modalStore.confirmClose(selected)">Select</button> -->
</template>
<script setup>
    import { ref } from 'vue';
    import { Icon } from '@iconify/vue';
    import ModalsHeader from '@components/Modals/Header.vue';
    import { useModalStore } from '@stores/modalStore';
    import ButtonPrimary from '@components/Button/Primary.vue';

    const modalStore = useModalStore();

    const props = modalStore.data;

    const selected = ref(null);
</script>