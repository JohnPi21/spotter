<script setup lang="ts">
    const props = defineProps<{
        trueValue: string | boolean | number,
        falseValue: string | boolean | number
    }>();

    const emit = defineEmits(['change']);

    const model = defineModel<boolean | number | string>();

</script>

<template>
    <input :true-value="trueValue" :false-value="falseValue" type="checkbox" @change="emit('change', model)"
        v-model="model"
        class="rounded border-gray-300 text-accent shadow-sm focus:ring-transparent dark:border-gray-700 bg-input dark:focus:ring-transparent dark:focus:ring-offset-gray-800" />
</template>
