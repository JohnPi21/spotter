<template>
    <div class="flex justify-between items-center my-4">
        <h1 class="text-2xl font-extrabold tracking-tight leading-tight">{{ title }}</h1>
        <UiButton><Icon :icon="icon" />{{ button }}</UiButton>
    </div>
</template>
<script setup>
    import UiButton from '@components/Ui/Button.vue';
    import { Icon } from '@iconify/vue';

    const props = defineProps({
        title: String,
        button: String,
        icon: String
    })
</script>