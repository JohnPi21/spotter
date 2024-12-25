<template>
    <slot>
        <div>{{ props.title }}</div>
    </slot>
</template>
<script setup>
const props = defineProps({
    title : ''
})
</script>