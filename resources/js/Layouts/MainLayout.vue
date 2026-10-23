<template>
    <div class="flex min-h-screen" v-if="page.props?.auth?.user">
        <Toastr />
        <SideNav class="hidden md:block" />
        <Modal />
        <div
            class="container mx-auto mb-5 min-w-0 px-3 pb-[calc(var(--app-mobile-nav-height)+env(safe-area-inset-bottom)+12px)] md:pb-0 md:pl-[calc(var(--sidenav-width)+24px)]"
            scroll-region=""
        >
            <slot />
        </div>
        <MobileNav class="block md:hidden" />
    </div>
</template>
<script setup lang="ts">
import MobileNav from "@/Components/MobileNav.vue";
import Modal from "@/Components/Modal.vue";
import SideNav from "@/Components/SideNav.vue";
import Toastr from "@/Components/Ui/Toastr.vue";
import { useExerciseStore } from "@/stores/exerciseStore";
import { usePage } from "@inertiajs/vue3";
import { onMounted } from "vue";

const page = usePage();
const exerciseStore = useExerciseStore();

onMounted(async () => {
    if (!page?.props?.auth?.user) return;

    await exerciseStore.load();
});
</script>
