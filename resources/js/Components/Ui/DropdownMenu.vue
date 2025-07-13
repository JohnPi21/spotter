<template>
    <div class="relative">
        <slot name="header"></slot>

        <div
            class="absolute left-0 top-0 z-40 h-full w-full cursor-pointer"
            ref="menuDropdown"
            :class="[{ 'z-high': dropdown == idx }]"
            @click.prevent="toggleDropdown(idx)"
        >
            <div
                class="menu-dropdown scrollbar absolute z-40 flex max-h-[300px] w-full min-w-[150px] translate-x-[-50%] list-none flex-col overflow-y-auto rounded border-b border-layer-border bg-layer-light p-1 shadow"
                :style="{ left: props.left, top: props.top }"
                v-if="dropdown == idx"
                @click.prevent.stop=""
            >
                <slot :toggle="toggleDropdown"></slot>
            </div>
        </div>
    </div>
</template>
<script setup>
import { ref } from "vue";
import { useUtils } from "@composables/utils.js";

const props = defineProps({
    idx: { type: [Number, String] },
    left: { type: String, default: "-50px" },
    top: { type: String, default: "115%" },
});

const dropdown = ref(null);

function toggleDropdown(index = props.idx) {
    if (dropdown.value == index) {
        dropdown.value = null;
        return;
    }

    dropdown.value = index;
}

const menuDropdown = ref();

useUtils.onClickOutside(menuDropdown, () => {
    dropdown.value = null;
});
</script>
<style lang="css" scoped>
.menu-dropdown > * {
    @apply flex cursor-pointer items-center justify-start gap-2 whitespace-nowrap px-2 py-1 text-secondary transition hover:bg-layer hover:text-primary;
}
</style>
