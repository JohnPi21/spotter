<template>
    <div class="flex pl-[var(--sidenav-width)] min-h-screen">
        <SideNav />
        <Modal />
        <div class="container mx-auto px-5">
            <slot />
        </div>
    </div>
</template>
<script setup>
import SideNav from '@/Components/SideNav.vue';
import Modal from '@/Components/Modal.vue';

</script>