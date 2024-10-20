<template>
    <div class="bg-layer border border-layer-border p-2 rounded shadow-sm">
        <slot></slot>
    </div>
</template>

<script setup></script>

<style scoped>
    :slotted(ul){
        @apply list-none ;
    }

    :slotted(ul li){
        @apply py-3 border-b border-layer-border last:border-none last:pb-0 first:pt-0;
    }
</style>