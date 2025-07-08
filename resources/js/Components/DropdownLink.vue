<script setup lang="ts">
    import { Link } from '@inertiajs/vue3';

    defineProps<{
        href: string;
    }>();
</script>

<template>
    <Link :href="href"
        class="block w-full px-4 py-2 text-start text-sm leading-5 transition duration-150 ease-in-out focus:outline-none bg-layer-light text-gray-300 hover:bg-input focus:bg-gray-800">
    <slot />
    </Link>
</template>
