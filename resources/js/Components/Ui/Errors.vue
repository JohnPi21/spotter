<template>
    <ul class="flex flex-col gap-2 my-2" v-if="errors && Object.keys(errors).length > 0">
        <li class="bg-red rounded-sm p-2 flex align-center gap-2" v-for="(error, field) in errors">
            <span class="flex items-center">
                <Icon icon="ix:error-filled" size="20px" />
            </span>
            <span>
                {{ field }} : {{ error }}
            </span>
        </li>
    </ul>
</template>
<script setup lang="ts">
    import { Icon } from '@iconify/vue'
    const props = defineProps<{
        errors?: Object
    }>()
</script>