<template>
    <div class="flex flex-col gap-2">
        <TextInput label="Input" icon="tabler:barbell" icon_size="35px" />
        <UiBox>Test</UiBox>
        <UiBox>Test</UiBox>
        <UiBox>Test</UiBox>
    </div>
</template>
<script setup lang="ts">
    import TextInput from '@/Components/Input/text.vue'
    import UiBox from '@/Components/Ui/Box.vue'

    const test = 'Hello From Vue';
</script>