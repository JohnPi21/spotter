<template>
    <ModalsHeader title="Muscle Groups"/>
    <div v-for="muscle in props">{{ muscle }}</div>
</template>
<script setup>
import ModalsHeader from '@components/Modals/Header.vue';
import { useModalStore } from '@stores/modalStore';

const modalStore = useModalStore();

const props = modalStore.params;
console.log(props)
</script>