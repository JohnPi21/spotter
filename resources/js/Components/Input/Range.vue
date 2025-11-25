<template>
    <div class="flex flex-col gap-1">
        <!-- Label + value -->
        <div class="flex items-center justify-between text-xs text-secondary">
            <InputLabel :value="label" v-if="label" />
        </div>

        <UiBox class="flex gap-0 !p-0">
            <InputText
                v-model="modelValue"
                class="max-w-[50px] border-none text-center"
                type="number"
                :min="min"
                :max="max"
            />

            <!-- Slider -->
            <div class="slider flex items-center gap-3">
                <span class="w-7 text-right text-[11px] text-helper">{{ min }}</span>

                <input
                    type="range"
                    class="range-input w-full appearance-none"
                    :min="min"
                    :max="max"
                    :step="step"
                    v-model.number="modelValue"
                    :style="trackStyle"
                />

                <span class="w-7 text-[11px] text-helper">{{ max }}</span>
            </div>
        </UiBox>

        <p v-if="helper" class="mt-1 text-[11px] text-helper">
            {{ helper }}
        </p>
    </div>
</template>

<script setup lang="ts">
import InputText from "@/Components/Input/Text.vue";
import UiBox from "@/Components/Ui/Box.vue";
import { computed } from "vue";
import InputLabel from "./InputLabel.vue";

const modelValue = defineModel<number>({
    default: 0,
});

const props = defineProps<{
    min: number;
    max: number;
    step?: number;
    label?: string;
    helper?: string;
    unit?: string;
}>();

const step = computed(() => props.step ?? 1);

const trackStyle = computed(() => {
    const min = props.min;
    const max = props.max;
    const val = modelValue.value;

    const clamped = val <= min ? min : val >= max ? max : val;

    const percent = ((clamped - min) / (max - min)) * 100;

    return {
        "--range-progress": `${percent}%`,
    } as Record<string, string>;
});
</script>

<style scoped>
.range-input {
    height: 0.5rem;
    border-radius: 9999px;
    background: #2d2d2d;
    outline: none;
}

/* WebKit */
.range-input::-webkit-slider-runnable-track {
    height: 0.5rem;
    border-radius: 9999px;
    background: linear-gradient(
        to right,
        #f97316 0%,
        #f97316 var(--range-progress),
        #2d2d2d var(--range-progress),
        #2d2d2d 100%
    );
}

.range-input::-webkit-slider-thumb {
    -webkit-appearance: none;
    appearance: none;
    width: 16px;
    height: 16px;
    margin-top: -4px; /* centers thumb on track */
    border-radius: 9999px;
    background: #0a0a0a;
    border: 2px solid #f97316;
    box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.4);
    cursor: pointer;
}

/* Firefox */
.range-input::-moz-range-track {
    height: 0.5rem;
    border-radius: 9999px;
    background: linear-gradient(
        to right,
        #f97316 0%,
        #f97316 var(--range-progress),
        #2d2d2d var(--range-progress),
        #2d2d2d 100%
    );
}

.range-input::-moz-range-thumb {
    width: 16px;
    height: 16px;
    border-radius: 9999px;
    background: #0a0a0a;
    border: 2px solid #f97316;
    box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.4);
    cursor: pointer;
}

/* Hide outline in Firefox when focused via keyboard */
.range-input::-moz-focus-outer {
    border: 0;
}
</style>
