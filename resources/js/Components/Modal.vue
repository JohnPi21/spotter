<template>
    <div class="fixed w-screen h-screen top-0 left-0 z-50" v-if="modalStore.currentModal">
        <div class="absolute w-full h-full bg-black opacity-60" @click="modalStore.closeModal()"></div>
        <UiBox class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 min-w-64 min-h-64">
            <component :is="modals[modalStore.currentModal]" />
        </UiBox>
    </div>

</template>
<script setup>
import UiBox from '@components/Ui/Box.vue';
import { useModalStore } from '@stores/modalStore';

import MuscleGroups from '@components/Modals/MuscleGroups.vue';
import Exercises from '@components/Modals/Exercises.vue';

const modalStore = useModalStore();

const modals = {
    "MuscleGroups"  : MuscleGroups,
    "Exercises"     : Exercises
}

</script>