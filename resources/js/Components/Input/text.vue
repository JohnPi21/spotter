<template>
    <InputWrapper>
        <template v-slot:label v-if="props.label.length > 0" :for="name">
            {{ props.label }}
        </template>

        <Icon v-if="props.icon" :icon="props.icon" :size="iconSize" class="w-8 h-5 peer" />

        <input autocomplete="off" :readonly="props.readonly" :pattern="props.pattern" :disabled="props.readonly"
            :name="name" :type="props.type" :placeholder="props.placeholder" v-model="input_value" @input="update"
            @change="update" :required="props.required" :class="inputClass"
            class="bg-transparent px-2 py-1 border-none focus:outline-none rounded text-primary placeholder-secondary w-full peer-[]:pl-0" />

        <slot></slot>
    </InputWrapper>
    <!-- // This is for refference -->
    <!-- <div class="flex flex-col">
    <label hmtlFor="email" class="[&+input:only-of-type]:rounded-r-3xl [&+input:only-of-type]:rounded-l-3xl [&+*_span+input:last-child]:rounded-r-3xl [&+*_input:first-child]:rounded-l-3xl">Email</label>
    <input id="email" class="peer-label:only-of-type:rounded-3xl rounded-none bg-green-400" />
  </div> -->


</template>

<script setup>
    import { ref } from 'vue';
    import { watch } from 'vue';
    import { onMounted } from 'vue';
    import { Icon } from '@iconify/vue';
    import InputWrapper from '@components/Input/Wrapper.vue'

    const props = defineProps({
        label: {
            type: String,
            default: '',
        },
        placeholder: {
            type: String, Number,
            default: '',
        },
        required: {
            type: Boolean,
            default: false,
        },
        type: {
            type: String,
            default: 'text',
        },
        modelValue: {
            type: [String, Number, undefined],
            default: '',
        },
        readonly: {
            default: false,
        },
        pattern: {
            type: String,
            default: '.*',
        },
        icon: {
            type: String,
        },
        iconSize: {
            type: String,
            default: '25px',
        },
        inputClass: {
            type: String,
            default: ''
        }
    });

    const emit = defineEmits(['update:modelValue']);
    const input_value = ref(props.modelValue);
    const name = ref('');

    onMounted(() => {
        // name.value = props.name || 'input'
    });

    function update() {
        emit('update:modelValue', input_value.value);
    }

    watch(
        () => props.modelValue,
        (value) => {
            input_value.value = value;
        }
    );
</script>

<style scoped>
/* Additional styles if needed for finer control */
</style>
