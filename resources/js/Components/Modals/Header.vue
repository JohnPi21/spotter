<template>
    <slot>
        <div class="mb-2 flex justify-between border-b border-layer-border p-4" :class="props.class">
            <h3 class="text-lg">{{ props.title }}</h3>

            <button
                class="pointer flex w-6 items-center justify-center rounded border border-layer-border bg-layer-light opacity-70 transition hover:opacity-100"
                @click="emits('close')"
            >
                <Icon icon="material-symbols-light:close" width="18px" class="" />
            </button>
        </div>
    </slot>
</template>
<script setup lang="ts">
import { Icon } from "@iconify/vue";

const props = defineProps<{
    title: string;
    class?: string;
}>();

const emits = defineEmits(["close"]);
</script>
