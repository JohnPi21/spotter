<template>
    <Switch
        v-model="enabled"
        :class="enabled ? 'bg-blue-600' : 'bg-gray-200'"
        class="relative inline-flex h-6 w-11 items-center rounded-full"
    >
        <slot class="sr-only"></slot>
        <span
            :class="enabled ? 'translate-x-6' : 'translate-x-1'"
            class="inline-block h-4 w-4 transform rounded-full bg-white transition"
        />
    </Switch>
</template>

<script setup>
import { Switch } from "@headlessui/vue";

const enabled = defineModel(false);
</script>
