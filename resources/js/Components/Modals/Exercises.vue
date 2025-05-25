<template>
    <div class="flex">
        <Modal :show="showModal" @close="showModal = false">
            <ModalsHeader title="Muscle Groups" />

            <ul>
                <li v-for="(muscle, idx) in muscleGroups" :key="idx" @click="select(idx)"
                    :class="[selected == idx ? 'bg-layer-light text-primary' : 'text-secondary']"
                    class="p-2 border-b border-main-border cursor-pointer hover:text-primary transition hover:bg-layer-light last:border-b-0 flex items-center justify-between">
                    {{ muscle }}

                    <Icon icon="ri:checkbox-circle-fill" v-if="selected == idx" class="text-text-green" />
                </li>
            </ul>
        </Modal>
    </div>
</template>
<script setup>
    import Modal from '@/Components/Modal.vue';
    import { ref, defineModel, onMounted } from 'vue';
    import axios from 'axios';

    const showModal = defineModel(false)

    const muscleGroups = ref<Array[MuscleGroup]>([]);

    onMounted(async () => {
        try{
            const response = await axios.get('/exercises');



        } catch(error){
            console.error(error)
        }
    })
</script>