<template>
    <slot>
        <div class="flex justify-between mb-2" :class="props.class">
            <h3>{{ props.title }}</h3>
            <button
                class="pointer bg-layer-light opacity-70 border border-layer-border rounded flex items-center justify-center aspect-square w-6 transition hover:opacity-100"
                @click="modalStore.closeModal()">
                <Icon icon="material-symbols-light:close" width="18px" class="" />
            </button>
        </div>
    </slot>
</template>
<script setup lang="ts">
    import { Icon } from '@iconify/vue';
    import { useModalStore } from '@/stores/modalStore.js';

    const props = defineProps<{
        title: string,
        class: string
    }>()

    const modalStore = useModalStore();
</script>