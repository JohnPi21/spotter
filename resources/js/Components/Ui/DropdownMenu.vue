<template>
    <div class="absolute w-full h-full left-0 top-0 cursor-pointer z-40" ref="menuDropdown" :class="{'z-high' : dropdown == idx}" @click="toggleDropdown(idx)">
        <div class="menu-dropdown absolute left-0 w-full bg-layer border-b border-layer-border rounded shadow flex flex-col top-[115%] max-h-[300px] min-w-[100px] overflow-y-auto p-1 translate-x-[-50%] scrollbar z-40" v-if="dropdown == idx" @click.stop="">
            <slot></slot>
        </div>
    </div>
</template>
<script setup>
    import { ref } from 'vue';
    import {useUtils} from '@composables/utils.js';

    const props = defineProps({
        idx: {type: [Number, String]}
    })

    const dropdown = ref(null);

    function toggleDropdown(index){
        dropdown.value = index;
    }

	const menuDropdown = ref();

	useUtils.onClickOutside(menuDropdown, () => {
		dropdown.value = null;
	})

</script>
<style lang="css" scoped>
</style>