<script setup>
    import { Link } from '@inertiajs/vue3';
    defineProps({
        status: Number,
        message: String,
    });
</script>

<template>
    <div class="p-6 max-w-lg mx-auto text-center">
        <h1 class="text-4xl font-bold mb-4">Error {{ status }}</h1>
        <p class="text-lg text-gray-600">{{ message }}</p>
        <Link href="/" class="text-blue-600 hover:underline mt-4 block">Go back home</Link>
    </div>
</template>