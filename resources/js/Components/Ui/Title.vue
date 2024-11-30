<template>
    <div class="flex justify-between items-center my-4">
        <h1 class="text-2xl font-extrabold tracking-tight leading-tight">{{ title }}</h1>

        <Link :href="props.url" v-if="url">
            <UiButton><Icon :icon="icon" />{{ button }}</UiButton>
        </Link>

        <UiButton v-else><Icon :icon="icon" />{{ button }}</UiButton>
    </div>
</template>
<script setup>
    import UiButton from '@components/Ui/Button.vue';
    import { Icon } from '@iconify/vue';
    import {Link } from '@inertiajs/vue3';

    const props = defineProps({
        title: String,
        button: String,
        icon: String,
        url: {type: String, default: null}
    })
</script>