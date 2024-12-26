<template>
    <slot>
        <div class="flex justify-between mb-2">
            <h3>{{ props.title }}</h3>
            <button
                class="pointer bg-layer-light opacity-70 border border-layer-border rounded flex items-center justify-center aspect-square w-6 transition hover:opacity-100">
                <Icon icon="material-symbols-light:close" width="18px" class="" />
            </button>
        </div>
    </slot>
</template>
<script setup>
    import { Icon } from '@iconify/vue';

    const props = defineProps({
        title: ''
    })
</script>