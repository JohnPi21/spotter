<template>
    <div class="flex min-h-screen">
        <SideNav />
        <Modal />
        <div class="container pl-[var(--sidenav-width)] mx-auto px-5">
            <slot />
        </div>
    </div>
</template>
<script setup>
import SideNav from '@/Components/SideNav.vue';
import Modal from '@/Components/Modal.vue';
</script>