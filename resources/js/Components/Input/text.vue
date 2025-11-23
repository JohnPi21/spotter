<script setup lang="ts">
import { onMounted, ref } from "vue";

const model = defineModel<string | number | null | undefined>({ required: true });

const input = ref<HTMLInputElement | null>(null);

defineProps<{
    type?: string;
}>();

onMounted(() => {
    if (input.value?.hasAttribute("autofocus")) {
        input.value?.focus();
    }
});

// Expose focus method
defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        :type="type ?? 'text'"
        v-model="model"
        ref="input"
        class="rounded-md border-input-border bg-input text-gray-300 shadow-sm focus:border-accent focus:ring-accent"
    />
</template>
<style lang="css">
input:-webkit-autofill {
    -webkit-box-shadow: 0 0 0px 1000px #1f2937 inset !important;
    /* Use your color */
    -webkit-text-fill-color: #ffffff !important;
    /* Text color if needed */
}
</style>
