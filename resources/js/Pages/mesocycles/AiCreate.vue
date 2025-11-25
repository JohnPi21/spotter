<template>
    <div class="mx-auto flex max-w-3xl flex-col gap-6">
        <!-- Header -->
        <div class="mt-3 flex flex-col gap-2">
            <h1 class="text-xl font-semibold text-primary">AI Mesocycle Generator</h1>
            <p class="text-sm text-secondary">
                Describe how you want to train and let the AI create a structured mesocycle for you.
            </p>
        </div>

        <UiBox class="flex flex-col gap-6 bg-layer">
            <!-- Basic Info -->
            <section class="flex flex-col gap-4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-helper">Basic Setup</h2>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="flex flex-col gap-1">
                        <InputLabel value="Program Name" />
                        <InputText v-model="form.name" placeholder="Hypertrophy Block – Winter" />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="flex flex-col gap-1">
                        <InputLabel value="Unit of Measure" />
                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="flex-1 rounded-md border px-3 py-2 text-sm transition"
                                :class="
                                    form.unit === 'kg'
                                        ? 'border-orange bg-layer-light text-primary'
                                        : 'border-input-border bg-input text-secondary hover:border-layer-border'
                                "
                                @click="form.unit = 'kg'"
                            >
                                kg
                            </button>
                            <button
                                type="button"
                                class="flex-1 rounded-md border px-3 py-2 text-sm transition"
                                :class="
                                    form.unit === 'lb'
                                        ? 'border-orange bg-layer-light text-primary'
                                        : 'border-input-border bg-input text-secondary hover:border-layer-border'
                                "
                                @click="form.unit = 'lb'"
                            >
                                lb
                            </button>
                        </div>
                        <InputError :message="errors.unit" />
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <InputLabel value="Weeks Duration" />
                        <InputRange
                            v-model="form.weeksDuration"
                            :min="3"
                            :max="12"
                            :step="1"
                            unit="weeks"
                            helper="Your meso must be between 3 and 12 weeks."
                        />
                        <InputError :message="errors.weeksDuration" />
                    </div>

                    <div>
                        <InputLabel value="Training Days per Week" />
                        <InputRange
                            v-model="form.daysPerWeek"
                            :min="1"
                            :max="7"
                            :step="1"
                            unit="days"
                            helper="How many days per week can you train?"
                        />
                    </div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="flex flex-col gap-1">
                        <InputLabel value="Average Session Length" />
                        <InputSelect
                            v-model="form.sessionDuration"
                            :options="sessionDurationOptions"
                            placeholder="Select session length"
                        />
                    </div>

                    <div class="flex flex-col gap-1">
                        <InputLabel value="Experience Level" />
                        <InputSelect
                            v-model="form.experienceLevel"
                            :options="experienceOptions"
                            placeholder="Select experience level"
                        />
                    </div>
                </div>
            </section>

            <!-- Focus -->
            <section class="flex flex-col gap-4 border-t border-main-border pt-4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-helper">Training Focus</h2>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="flex flex-col gap-1">
                        <InputLabel value="Primary Goal" />
                        <InputSelect
                            v-model="form.primaryGoal"
                            :options="primaryGoalOptions"
                            placeholder="Select primary goal"
                        />
                    </div>

                    <div class="flex flex-col gap-1">
                        <InputLabel value="Split Preference" />
                        <InputSelect
                            v-model="form.splitPreference"
                            :options="splitPreferenceOptions"
                            placeholder="Select split"
                        />
                    </div>
                </div>
            </section>

            <!-- Equipment -->
            <section class="flex flex-col gap-4 border-t border-main-border pt-4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-helper">Equipment & Environment</h2>

                <div class="grid gap-2 text-sm text-secondary md:grid-cols-2">
                    <label
                        v-for="item in equipmentOptions"
                        :key="item.value"
                        class="flex cursor-pointer items-center gap-2 rounded-md border border-main-border bg-input px-3 py-2 hover:border-layer-border hover:text-primary"
                    >
                        <InputCheckbox v-model="form.equipment" :value="item.value" />
                        <span>{{ item.label }}</span>
                    </label>
                </div>
            </section>

            <!-- Constraints -->
            <section class="flex flex-col gap-4 border-t border-main-border pt-4">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-helper">Constraints & Notes</h2>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="flex flex-col gap-1">
                        <InputLabel value="Injuries / Limitations" />
                        <textarea
                            v-model="form.injuries"
                            rows="4"
                            placeholder="E.g. Lower back issues, no overhead pressing, avoid deep knee flexion..."
                            class="rounded-md border border-input-border bg-input px-3 py-2 text-sm text-primary outline-none transition placeholder:text-helper focus:border-accent focus:ring-accent"
                        ></textarea>
                    </div>

                    <div class="flex flex-col gap-1">
                        <InputLabel value="Preferences / Extra Details" />
                        <textarea
                            v-model="form.notes"
                            rows="4"
                            placeholder="Favourite lifts, muscle groups you want to emphasise, time constraints, etc."
                            class="rounded-md border border-input-border bg-input px-3 py-2 text-sm text-primary outline-none transition placeholder:text-helper focus:border-accent focus:ring-accent"
                        ></textarea>
                    </div>
                </div>
            </section>

            <!-- Submit -->
            <div
                class="flex flex-col gap-2 border-t border-main-border pt-4 md:flex-row md:items-center md:justify-between"
            >
                <p class="max-w-xl text-xs text-helper">
                    The AI will generate a mesocycle with
                    <span class="text-secondary">valid days, exercises, and muscle groups</span>
                    based on your inputs. You’ll be able to review and edit it before saving.
                </p>

                <ButtonPrimary class="mt-2 w-full md:mt-0 md:w-auto" :disabled="form.processing" @click="submit">
                    {{ form.processing ? "Generating..." : "Generate Mesocycle with AI" }}
                </ButtonPrimary>
            </div>
        </UiBox>
    </div>
</template>

<script setup lang="ts">
import { useForm } from "@inertiajs/vue3";

import ButtonPrimary from "@/Components/Button/Primary.vue";
import InputCheckbox from "@/Components/Input/Checkbox.vue";
import InputError from "@/Components/Input/InputError.vue";
import InputLabel from "@/Components/Input/InputLabel.vue";
import InputRange from "@/Components/Input/Range.vue";
import InputSelect from "@/Components/Input/Select.vue";
import InputText from "@/Components/Input/Text.vue";
import UiBox from "@/Components/Ui/Box.vue";

defineProps<{ errors: { [key: string]: string } }>();

type PrimaryGoal = "hypertrophy" | "strength" | "fat_loss" | "recomp";
type SplitPreference = "full_body" | "upper_lower" | "push_pull_legs" | "bro_split" | "custom";
type ExperienceLevel = "beginner" | "intermediate" | "advanced";

interface AiMesocycleForm {
    [key: string]: any;
    name: string;
    unit: "kg" | "lb";
    weeksDuration: number;
    daysPerWeek: number;
    sessionDuration: number;
    primaryGoal: PrimaryGoal;
    splitPreference: SplitPreference;
    experienceLevel: ExperienceLevel;
    equipment: string[];
    injuries: string;
    notes: string;
}

const form = useForm<AiMesocycleForm>({
    name: "",
    unit: "kg",
    weeksDuration: 6,
    daysPerWeek: 4,
    sessionDuration: 60,
    primaryGoal: "hypertrophy",
    splitPreference: "upper_lower",
    experienceLevel: "intermediate",
    equipment: [],
    injuries: "",
    notes: "",
});

// ---- OPTIONS CENTRALIZED HERE ----

const sessionDurationOptions = [30, 45, 60, 75, 90];

const experienceOptions = [
    { value: "beginner", label: "Beginner" },
    { value: "intermediate", label: "Intermediate" },
    { value: "advanced", label: "Advanced" },
] as const;

const primaryGoalOptions = [
    { value: "hypertrophy", label: "Hypertrophy (muscle gain)" },
    { value: "strength", label: "Strength" },
    { value: "fat_loss", label: "Fat loss" },
    { value: "recomp", label: "Recomposition" },
] as const;

const splitPreferenceOptions = [
    { value: "full_body", valueShort: "Full Body", label: "Full Body" },
    { value: "upper_lower", valueShort: "Upper / Lower", label: "Upper / Lower" },
    { value: "push_pull_legs", valueShort: "Push / Pull / Legs", label: "Push / Pull / Legs" },
    { value: "bro_split", valueShort: "Bodypart Split", label: "Bodypart Split" },
    { value: "custom", valueShort: "Let AI decide", label: "Let AI decide" },
] as const;

const equipmentOptions = [
    { value: "barbell", label: "Barbell + Plates" },
    { value: "dumbbells", label: "Dumbbells" },
    { value: "machines", label: "Machines" },
    { value: "cables", label: "Cable stack" },
    { value: "kettlebells", label: "Kettlebells" },
    { value: "bands", label: "Bands" },
    { value: "bodyweight", label: "Bodyweight only" },
    { value: "home_gym", label: "Limited home gym" },
];

function submit() {
    form.post("/mesocycles/ai-generate", {
        preserveScroll: true,
    });
}
</script>

<style lang="css" scoped>
:deep(.slider) {
    @apply w-full;
}
</style>
