<script setup>
import PrimaryButton from "@/Components/Button/Primary.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Link } from "@inertiajs/vue3";

defineProps({
    status: Number,
    message: String,
});

defineOptions({
    layout: GuestLayout,
});
</script>

<template>
    <div class="mx-auto max-w-lg p-6 text-center">
        <h1 class="mb-4 text-4xl font-bold">{{ status }}</h1>
        <p class="text-xl">{{ message }}</p>
        <Link href="/" class="text-blue-600 mt-4 block hover:underline">
            <PrimaryButton>Go Home</PrimaryButton>
        </Link>
    </div>
</template>
