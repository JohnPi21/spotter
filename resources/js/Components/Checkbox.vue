<script setup lang="ts">
import { AnyARecord } from 'dns';

const props = defineProps<{
    trueValue: String,
    falseValue: String
}>();

const emit = defineEmits(['change']);

const model = defineModel();

</script>

<template>
    <input
        :true-value="trueValue"
        :false-value="falseValue"
        type="checkbox"
        @change="emit('change', model)"
        v-model="model"
        class="rounded border-gray-300 text-accent shadow-sm focus:ring-transparent dark:border-gray-700 dark:bg-gray-900 dark:focus:ring-transparent dark:focus:ring-offset-gray-800"
    />
</template>

