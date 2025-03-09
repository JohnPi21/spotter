<template>
    <div class="flex flex-col gap-2">
        <TextInput label="Input" icon="tabler:barbell" icon_size="35px" />
        <UiBox>Test</UiBox>
        <UiBox>Test</UiBox>
        <UiBox>Test</UiBox>
        <button @click="modalOpen = true">Open modal</button>
        <Modal :show="modalOpen">
            <p>Tst</p>
        </Modal>
    </div>
</template>
<script setup lang="ts">
    import TextInput from '@/Components/Input/text.vue'
import Modal from '@/Components/Modal.vue';
    import UiBox from '@/Components/Ui/Box.vue'
    import {ref} from 'vue';

    const test = 'Hello From Vue';

    const modalOpen = ref(false);
</script>