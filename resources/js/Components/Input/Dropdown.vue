<template>
    <InputWrapper>
        <template v-slot:label v-if="props.label.length > 0">
            {{ props.label }}
        </template>

        <div class="dropdown" ref="inputDropdown">
            <div class="dropdown-header" @click="toggleDropdown" :class="{ active: isOpen }">
                <Icon :name="icon" size="25px" v-if="icon" />
                {{ selectedOption.label }}
            </div>
            <div v-show="isOpen" class="dropdown-list">
                <div class="filter input-group" v-if="includeFilter">
                    <input type="text" v-model="filter_by" placeholder="Filter" @input="emitFilter" />
                </div>
                <slot name="options" class="dropdown-list" :selectOption="selectOption"
                    :selectedOption="selectedOption">
                    <!-- This is fallback content in case we don t use slot-->
                    <div v-for="(option, index) in filteredOptions" :key="index" class="menu-item"
                        @click="selectOption(option)">
                        {{ option.label }}
                    </div>
                </slot>
            </div>
        </div>
    </InputWrapper>
</template>

<script setup>
    const emit = defineEmits(["update:modelValue", "updateFilter", "change"]);

    const props = defineProps({
        label: { type: String, default: "" },
        options: { type: Array, default: [] },
        selected: { type: [String, Number, Boolean, Object] }, 	// The default selected option
        default: { type: String, default: "Select an option" }, // The text displayed before choosing an option	
        all: { type: Boolean, default: false },					// Returns the index of the option
        icon: { type: String }, 								// Expects the icon name from header
        includeFilter: { type: Boolean, default: false }, 		// Specifies if it should include filter
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

        if (props.includeFilter) {
            emitFilter();
        }
    });

    onUnmounted(() => {
        watcher();
    });

    const inputDropdown = ref();

    Utility.onClickOutside(inputDropdown, () => {
        isOpen.value = false;
    })
</script>

<style lang="scss" scoped>
.dropdown {
    position: relative;
    width: 100%;
    font-family: Arial, sans-serif;
    color: $text-secondary;
    min-width: 200px;
}

.dropdown-header {
    @include flex(center);
    background: transparent;
    padding: 0 10px;
    border-radius: 8px;
    cursor: pointer;
    user-select: none;
    color: #f3f3f380;
    font-size: 14px;
    line-height: 40px;
    gap: 5px;

    &:hover {
        background: #94979e29;
        color: #fff;
    }

    &.active {
        background: #94979e29;
    }

    .icon {
        padding: 0;
    }
}

.dropdown-list {
    position: absolute;
    top: 120%;
    left: 0;
    min-width: min-content;
    background-color: $bg-element;
    border: 1px solid #272a31;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    z-index: 20;
    max-height: 250px;
    overflow-y: auto;
    width: 100%;
    padding: 5px;

    @include scrollBar();
}

.filter {
    border: none;
    border-bottom: 1px solid $bg-element-border;
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
}
</style>
