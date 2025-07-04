<template>
    <div class="flex flex-col">
        <div class="flex justify-between items-center gap-1 mt-1">
            <InputText :id="id" :type="viewPassword ? 'text' : 'password'" class="block w-full" v-model="model" required
                :autocomplete="autocomplete" :placeholder="placeholder" />

            <ButtonSecondary type="button" @click="toggleView">
                <Icon icon="clarity:eye-show-line" width="20px" v-if="!viewPassword" />
                <Icon icon="bx:low-vision" width="20px" v-else />
            </ButtonSecondary>
        </div>
    </div>
</template>
<script setup lang="ts">
    import { Icon } from '@iconify/vue';
    import { ref } from 'vue';
    import InputText from '@/Components/Input/text.vue'
    import ButtonSecondary from '@/Components/Button/Secondary.vue';

    const model = defineModel<string | number | null>(undefined);
    const viewPassword = ref<boolean>(false);

    defineProps<{
        autocomplete?: string,
        id?: string,
        placeholder?: string,
    }>();

    function toggleView(): void {
        viewPassword.value = !viewPassword.value;
    }
</script>