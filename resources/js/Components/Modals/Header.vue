<template>
    <slot>
        <div class="flex justify-between mb-2 p-4 border-b border-layer-border" :class="props.class">
            <h3 class="text-lg">{{ props.title }}</h3>

            <button
                class="pointer bg-layer-light opacity-70 border border-layer-border rounded flex items-center justify-center aspect-square w-6 transition hover:opacity-100"
                @click="emits('close')">
                <Icon icon="material-symbols-light:close" width="18px" class="" />
            </button>
        </div>
    </slot>
</template>
<script setup lang="ts">
    import { Icon } from '@iconify/vue';

    const props = defineProps<{
        title: string,
        class?: string,
    }>();

    const emits = defineEmits(['close']);

</script>