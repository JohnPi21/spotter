<template>
    <TabGroup v-model="selectedIndex" as="div">
        <TabList class="inline-flex overflow-hidden rounded-md border border-layer-border">
            <Tab v-for="(tab, index) in tabs" :key="index" v-slot="{ selected }" as="template">
                <button
                    class="whitespace-nowrap px-4 py-1.5 text-sm font-medium transition-colors duration-200 focus:outline-none"
                    :class="[
                        selected ? 'bg-accent text-white' : 'bg-layer text-secondary hover:bg-layer-light',
                        // No spacing, handle rounding based on position
                        index === 0 ? 'rounded-l-md' : index === tabs.length - 1 ? 'rounded-r-md' : '',
                    ]"
                >
                    {{ tab.name }}
                </button>
            </Tab>
        </TabList>
        <slot />
    </TabGroup>
</template>

<script setup>
import { Tab, TabGroup, TabList } from "@headlessui/vue";
import { ref } from "vue";

defineProps({
    tabs: {
        type: Array,
        required: true,
        default: () => [],
    },
});

const selectedIndex = ref(0);
defineExpose({ selectedIndex });
</script>
