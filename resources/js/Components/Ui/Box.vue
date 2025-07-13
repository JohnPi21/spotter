<template>
    <div class="rounded border border-layer-border bg-layer p-2 shadow-sm">
        <slot></slot>
    </div>
</template>

<script setup></script>

<style scoped>
:slotted(ul) {
    @apply list-none;
}

:slotted(ul li) {
    @apply border-b border-layer-border py-3 first:pt-0 last:border-none last:pb-0;
}
</style>
