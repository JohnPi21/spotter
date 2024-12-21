<template>
    <div>
        <div v-if="props.label && props.label.length > 0">
            {{ props.label }}
        </div>

        <div class="dropdown text-secondary" ref="inputDropdown">
            <div class="dropdown-header bg-input border-input-border border active:bg-layer hover:bg-layer" @click="toggleDropdown" :class="{ active: isOpen }">
                <Icon :icon="icon" width="25px" v-if="icon" />
                {{ selectedOption.label }}
            </div>
            <div v-show="isOpen" class="dropdown-list bg-layer scrollbar">
                <div class="filter input-group bg-layer" v-if="filter">
                    <!-- <input type="text" v-model="filter_by" placeholder="Filter" @input="emitFilter" /> -->

                    <InputText v-model="filter_by" placeholder="Filter" @input="emitFilter"/>
                </div>
                <slot name="options" class="dropdown-list bg-layer" :selectOption="selectOption"
                    :selectedOption="selectedOption">
                    <!-- This is fallback content in case we don t use slot-->
                    <div v-for="(option, index) in filteredOptions" :key="index" class="p-2 hover:bg-layer-light cursor-pointer bg-layer"
                        @click="selectOption(option)">
                        {{ option.label }}
                    </div>
                </slot>
            </div>
        </div>
    </div>
</template>

<script setup>
    import {ref, defineEmits, reactive, computed, watch, onMounted, onUnmounted} from "vue";
    import {useUtils} from '@composables/utils.js'
    import InputText from '@components/Input/text.vue'
    import { Icon } from '@iconify/vue';

    const emit = defineEmits(["update:modelValue", "updateFilter", "change"]);

    const props = defineProps({
        label: { type: String, default: "" },
        options: { type: Array, default: [] },
        selected: { type: [String, Number, Boolean, Object] }, 	// The default selected option
        default: { type: String, default: "Select an option" }, // The text displayed before choosing an option	
        all: { type: Boolean, default: false },					// Returns the index of the option
        icon: { type: String }, 								// Expects the icon name from header
        filter: { type: Boolean, default: false }, 		// Specifies if it should include filter
        value_key: { type: [String, Number, Boolean], default: 'value' } // Changes the emit value key {label: First, value_key: 1}
    });

    const isOpen = ref(false);

    const selectedOption = reactive({ label: props.default, value: "" });

    const toggleDropdown = () => {
        isOpen.value = !isOpen.value;
    };

    const selectOption = (option) => {
        selectedOption.label = option.label;
        selectedOption[props.value_key] = option[props.value_key];
        emit("update:modelValue", props.all ? option : selectedOption[props.value_key]);
        emit("change", selectedOption[props.value_key]);
        isOpen.value = false;
    };

    const filter_by = ref("");
    const filteredOptions = computed(() => {
        if (props.options.length == 0 || Object.keys(props.options[0]).length == 0) {
            return props.options;
        }
        return props.options.filter((option) => option.label.toLowerCase().includes(filter_by.value));
    });

    function emitFilter() {
        emit("updateFilter", filter_by.value);
    }

    const watcher = watch(
        () => props.selected,
        (newVal) => {
            if (!props.options) {
                return;
            }
            for (let i = 0; i < props?.options.length; i++) {
                if (props.options[i].value == props.selected) {
                    selectOption(props.options[i]);
                }
            }
        },
        {
            immediate: true,
            deep: true,
        }
    );

    onMounted(() => {
        if (props.selected) {
            for (let i = 0; i < props.options.length; i++) {
                if (props.options[i].value == props.selected) {
                    selectOption(props.options[i]);
                }
            }
        }

        if (props.filter) {
            emitFilter();
        }
    });

    onUnmounted(() => {
        watcher();
    });

    const inputDropdown = ref();

    useUtils.onClickOutside(inputDropdown, () => {
        isOpen.value = false;
    })
</script>

<style scoped>
.dropdown {
    position: relative;
    width: 100%;
    font-family: Arial, sans-serif;
    /* color: $text-secondary; */
    min-width: 200px;
}

.dropdown-header {
    display: flex;
    align-items: center;
    /* background: transparent; */
    padding: 0 10px;
    border-radius: 5px;
    cursor: pointer;
    user-select: none;
    /* color: #f3f3f380; */
    font-size: 14px;
    line-height: 35px;
    gap: 5px;

}

.dropdown-header:hover {
    /* background: #94979e29; */
    color: #fff;
}

.dropdown-header.active {
    /* background: #94979e29; */
}

.dropdown-header .icon {
    padding: 0;
}

.dropdown-list {
    position: absolute;
    top: 100%;
    left: 0;
    min-width: min-content;
    /* background-color: $bg-element; */
    border: 1px solid #272a31;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    z-index: 20;
    max-height: 250px;
    overflow-y: auto;
    width: 100%;
    padding: 5px;

    /* @include scrollBar(); */
}

.dropdown-list {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
}

.filter {
    border: none;
    /* border-bottom: 1px solid $bg-element-border; */
    /* border-bottom: 1px solid grey; */
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
}
</style>
