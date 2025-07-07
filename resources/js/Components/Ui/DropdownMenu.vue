<template>
    <div class="relative">

        <slot name="header"></slot>

        <div class="absolute w-full h-full left-0 top-0 cursor-pointer z-40" ref="menuDropdown"
            :class="[{ 'z-high': dropdown == idx }]" @click.prevent="toggleDropdown(idx)">
            <div class="menu-dropdown list-none absolute w-full bg-layer-light border-b border-layer-border rounded shadow flex flex-col max-h-[300px] min-w-[150px] overflow-y-auto p-1 translate-x-[-50%] scrollbar z-40"
                :style="{ left: props.left, top: props.top }" v-if="dropdown == idx" @click.prevent.stop="">
                <slot :toggle="toggleDropdown"></slot>
            </div>
        </div>
    </div>
</template>
<script setup>
    import { ref } from 'vue';
    import { useUtils } from '@composables/utils.js';

    const props = defineProps({
        idx: { type: [Number, String] },
        left: { type: String, default: '-50px' },
        top: { type: String, default: '115%' },
    })

    const dropdown = ref(null);

    function toggleDropdown(index = props.idx) {
        if (dropdown.value == index) {
            dropdown.value = null;
            return
        }

        dropdown.value = index;
    }

    const menuDropdown = ref();

    useUtils.onClickOutside(menuDropdown, () => {
        dropdown.value = null;
    })

</script>
<style lang="css" scoped>
.menu-dropdown>* {
    @apply hover:bg-layer hover:text-primary cursor-pointer flex items-center justify-start gap-2 px-2 py-1 whitespace-nowrap text-secondary transition;
}
</style>