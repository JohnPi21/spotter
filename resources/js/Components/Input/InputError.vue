<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <div v-show="message">
        <p class="text-sm text-red">
            {{ message }}
        </p>
    </div>
</template>
<style lang="css"></style>
