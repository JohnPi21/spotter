<template>
    <div>
        <label class="mb-1 block text-sm text-secondary">
            <slot name="label"></slot>
        </label>
        <div
            class="flex items-center rounded border border-main-border bg-input focus-within:border-input-border-focus"
        >
            <slot></slot>
        </div>
    </div>
</template>
<script setup></script>
<style scoped></style>
