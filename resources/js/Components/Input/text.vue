<script setup lang="ts">
    import { onMounted, ref } from 'vue';

    const model = defineModel<string | number | null | undefined>({ required: true });

    const input = ref<HTMLInputElement | null>(null);

    defineProps<{
        type?: string
    }>();

    onMounted(() => {
        if (input.value?.hasAttribute('autofocus')) {
            input.value?.focus();
        }
    });

    // Expose focus method
    defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input :type="type ?? 'text'" v-model="model" ref="input"
        class="rounded-md border-input-border shadow-sm focus:border-accent focus:ring-indigo-500 dark:bg-layer-light dark:text-gray-300 dark:focus:border-accent dark:focus:ring-accent" />
</template>
