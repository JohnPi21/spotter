<template>
    <div class="flex min-h-screen">
        <SideNav class="hidden md:block" />
        <Modal />
        <div class="container md:pl-[var(--sidenav-width)] px-3 mx-auto">
            <slot />
        </div>
        <MobileNav />
    </div>
</template>
<script setup lang="ts">
    import SideNav from '@/Components/SideNav.vue';
    import Modal from '@/Components/Modal.vue';
    import MobileNav from '@/Components/MobileNav.vue'
</script>