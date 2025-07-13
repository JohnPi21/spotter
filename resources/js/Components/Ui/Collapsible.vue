<template>
    <div class="overflow-hidden rounded">
        <!-- Header -->
        <button @click="toggle" class="flex w-full items-center justify-between px-4 py-2 transition">
            <span>{{ title }}</span>
            <svg
                :class="['transform transition-transform duration-200', isOpen ? 'rotate-180' : 'rotate-0']"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
            >
                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" />
            </svg>
        </button>

        <!-- Smooth Transition Content -->
        <transition @enter="enter" @leave="leave">
            <div v-show="isOpen" ref="contentRef" class="overflow-hidden px-4 py-2">
                <slot />
            </div>
        </transition>
    </div>
</template>

<script setup lang="ts">
import { nextTick, ref } from "vue";

defineProps<{ title: string }>();

const isOpen = ref(false);
const contentRef = ref<HTMLElement | null>(null);

const toggle = () => {
    isOpen.value = !isOpen.value;
};

// Transition methods
const enter = async (el: Element) => {
    const target = el as HTMLElement;
    target.style.height = "0px";
    await nextTick();
    const scrollHeight = target.scrollHeight;
    target.style.transition = "height 0.3s ease";
    target.style.height = scrollHeight + "px";
};

const leave = (el: Element) => {
    const target = el as HTMLElement;
    target.style.height = target.scrollHeight + "px";
    void target.offsetHeight; // force reflow
    target.style.transition = "height 0.3s ease";
    target.style.height = "0px";
};
</script>
