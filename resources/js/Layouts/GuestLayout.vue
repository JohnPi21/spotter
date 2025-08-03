<script setup lang="ts">
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import { Link } from "@inertiajs/vue3";
</script>

<template>
    <div class="flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0">
        <div>
            <Link href="/" class="flex flex-col items-center justify-center">
                <ApplicationLogo class="h-20 w-20 fill-current text-gray-500" />
            </Link>

            <h1 class="text-2xl font-extrabold">Spotacus</h1>
        </div>

        <div
            class="mt-6 w-full overflow-hidden border border-layer-border bg-layer px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
