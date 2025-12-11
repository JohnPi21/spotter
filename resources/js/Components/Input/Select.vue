<script setup lang="ts">
import { computed } from "vue";

const props = defineProps<{
    options: readonly (string | number | DropdownOption)[];
    placeholder?: string;
    disabled?: boolean;
}>();

const model = defineModel<string | number | null>({
    default: null,
});

// Normalizing options into { value, label }
const normalizedOptions = computed(() => {
    return props.options.map((opt) => {
        if (typeof opt === "string" || typeof opt === "number") {
            return { value: String(opt), label: String(opt) };
        }
        return opt;
    });
});
</script>

<template>
    <select
        v-model="model"
        :disabled="disabled"
        class="w-full rounded-md border border-input-border bg-input px-3 py-2 text-sm text-primary outline-none transition placeholder:text-helper focus:border-accent focus:ring-2 focus:ring-accent focus:ring-opacity-40 disabled:cursor-not-allowed disabled:opacity-50"
    >
        <option v-if="placeholder" disabled value="">
            {{ placeholder }}
        </option>

        <option v-for="(opt, idx) in normalizedOptions" :key="idx" :value="opt.value">
            {{ opt.label }}
        </option>
    </select>
</template>
