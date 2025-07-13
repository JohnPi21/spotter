<template>
    <ul class="my-2 flex flex-col gap-2" v-if="errors && Object.keys(errors).length > 0">
        <li class="align-center flex gap-2 rounded-sm bg-red p-2" v-for="(error, field) in errors">
            <span class="flex items-center">
                <Icon icon="ix:error-filled" size="20px" />
            </span>
            <span>{{ field }} : {{ error }}</span>
        </li>
    </ul>
</template>
<script setup lang="ts">
import { Icon } from "@iconify/vue";
defineProps<{
    errors?: object;
}>();
</script>
