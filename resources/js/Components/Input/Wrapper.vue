<template>
    <div>
        <label class="block mb-1 text-sm text-secondary">
            <slot name="label"></slot>
        </label>
        <div
            class="flex items-center rounded bg-input border border-input-border focus-within:border-input-border-focus">
            <slot></slot>
        </div>
    </div>
</template>
<script setup>
</script>
<style scoped>
</style>