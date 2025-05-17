<template>
    <div class="flex bg-accent">
        <PrimaryButton @click="showModal = true">Open Exercises</PrimaryButton>

        <Modal :show="showModal">
            Exercises
        </Modal>
    </div>
</template>
<script setup>
    import Modal from '@/Components/Modal.vue';
    import PrimaryButton from '../PrimaryButton.vue';
    import { ref } from 'vue';

    const showModal = ref(false)
</script>