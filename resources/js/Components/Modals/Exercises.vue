<template>
    <div class="flex">
        <Modal :show="showModal" @close="showModal = false">
            <ModalsHeader title="Muscle Groups" class="p-2 border-b border-layer-border" />

            <div class="flex flex-col p-2">
                <TextInput class="w-full" placeholder="Search exercise" v-model="search" />

                <Collapsible class="border-b border-main-border" :title="muscle.name"
                    v-for="(muscle, mIdx) in filteredGroups">
                    <ul>
                        <li v-for="(exercise, eIdx) in muscle.exercises" :key="eIdx" @click="select(eIdx)"
                            :class="[selected == eIdx ? 'bg-layer-light text-primary' : 'text-secondary']"
                            class="p-2 border-b border-main-border cursor-pointer hover:text-primary transition hover:bg-layer-light last:border-b-0 flex items-center justify-between">
                            <div class="flex-col flex gap-1">
                                <p>{{ exercise.name }}</p>
                                <span
                                    class="bg-layer-light border border-layer-border px-1 rounded-sm w-fit text-sm text-helper">{{
                                        capitalize(exercise.exercise_type)
                                    }}</span>
                            </div>

                            <Icon icon="ri:checkbox-circle-fill" v-if="selected == eIdx" class="text-text-green" />
                        </li>
                    </ul>

                </Collapsible>

                <ButtonPrimary class="disabled:opacity-75 w-full" :class="{ 'disabled': !selected }">Select
                </ButtonPrimary>
            </div>

        </Modal>
    </div>
</template>
<script setup lang="ts">
    import { Icon } from '@iconify/vue'
    import { ref, onMounted, computed } from 'vue';
    import axios from 'axios';
    import ModalsHeader from '@/Components/Modals/Header.vue'
    import Modal from '@/Components/Modal.vue';
    import Collapsible from '@/Components/Ui/Collapsible.vue'
    import TextInput from '@/Components/TextInput.vue';
    import ButtonPrimary from '@/Components/Button/Primary.vue';

    const showModal = defineModel<boolean>();

    const muscleGroups = ref<MuscleGroup[]>([]);
    const selected = ref<number>();
    const search = ref<string>('');

    const filteredGroups = computed(() => {
        return muscleGroups.value.map(mg => {
            const hasExercises = mg.exercises?.filter(ex => ex.name.toLowerCase().includes(search.value.toLowerCase())) || [];

            if (hasExercises?.length > 0) {
                return { ...mg, exercises: hasExercises };
            }

            return null;
        }).filter((mg) => mg !== null)
    })

    onMounted(async () => {
        try {
            const response = await axios.get('/exercises');

            muscleGroups.value = response.data;

        } catch (error) {
            console.error(error)
        }
    })

    function select(idx: number): void {
        selected.value = idx;
    }

    function capitalize(string: string): string {
        return string.slice(0, 1).toUpperCase() + string.slice(1, string.length);
    }
</script>