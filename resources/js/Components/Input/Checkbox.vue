<script setup lang="ts">
const emit = defineEmits(["change"]);

const model = defineModel<boolean | number | string | string[]>();
</script>

<template>
    <input
        type="checkbox"
        @change="emit('change', model)"
        v-model="model"
        :value="model"
        class="rounded border-gray-300 bg-input text-accent shadow-sm focus:ring-transparent dark:border-gray-700 dark:focus:ring-transparent dark:focus:ring-offset-gray-800"
    />
</template>
