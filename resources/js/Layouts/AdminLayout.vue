<template>
    <div v-if="page.props?.auth?.user" class="flex min-h-screen bg-main text-primary">
        <Toastr />
        <AdminSideNav class="hidden md:block" />
        <Modal />

        <main
            class="container mx-auto mb-5 min-w-0 px-3 pb-[var(--mobilenav-height)] md:pl-[var(--sidenav-width)]"
            scroll-region=""
        >
            <div class="mx-auto w-full max-w-7xl px-4 py-5 sm:px-6 md:py-8 lg:px-8">
                <slot />
            </div>
        </main>

        <AdminMobileNav class="block md:hidden" />
    </div>
</template>

<script setup lang="ts">
import AdminMobileNav from "@/Components/Admin/MobileNav.vue";
import AdminSideNav from "@/Components/Admin/SideNav.vue";
import Modal from "@/Components/Modal.vue";
import Toastr from "@/Components/Ui/Toastr.vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
</script>
