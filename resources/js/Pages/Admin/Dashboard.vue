<template>
    <div class="flex flex-col gap-6">
        <Head title="Admin dashboard" />

        <header>
            <p class="text-sm font-medium text-accent">Administration</p>
            <h1 class="mt-1 text-2xl font-semibold text-primary">Dashboard</h1>
        </header>

        <section class="border-y border-layer-border py-5">
            <p class="text-sm text-secondary">Select a resource from the navigation to manage application data.</p>
        </section>
    </div>
</template>

<script setup lang="ts">
import { Head } from "@inertiajs/vue3";
</script>
