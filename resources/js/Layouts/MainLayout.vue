<template>
    <div class="flex min-h-screen" v-if="$page.props?.auth?.user">
        <SideNav class="hidden md:block" />
        <Modal />
        <div class="container mx-auto mb-5 px-3 pb-[var(--mobilenav-height)] md:pl-[var(--sidenav-width)]">
            <slot />
        </div>
        <MobileNav class="block md:hidden" />
    </div>
</template>
<script setup lang="ts">
import MobileNav from "@/Components/MobileNav.vue";
import Modal from "@/Components/Modal.vue";
import SideNav from "@/Components/SideNav.vue";
import { useExerciseStore } from "@/stores/exerciseStore";
import { onMounted } from "vue";

const exerciseStore = useExerciseStore();

onMounted(async () => {
    await exerciseStore.load();
});
</script>
