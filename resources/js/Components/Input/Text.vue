<script setup lang="ts">
import { onMounted, ref } from "vue";

const model = defineModel<string | number | undefined>({ required: true });

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute("autofocus")) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="ui-text-input rounded-md border-input-border bg-input text-gray-300 shadow-sm focus:border-accent focus:ring-accent"
        v-model="model"
        ref="input"
    />
</template>
