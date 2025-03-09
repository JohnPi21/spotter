<template>
    <div class="flex flex-col gap-3 my-2 max-w-[768px] mx-auto">
        <UiBox class="flex flex-col">
            <div class="flex justify-between items-center">
                <div class="flex flex-col">
                    <p class="text-secondary text-sm">{{ mesocycle.name }}</p>
                    <h3>WEEK {{ day.week }} DAY {{ day.day_order }}: {{ day.label }} </h3>
                </div>
                <div class="flex align-end items-center gap-3">
                    <Icon icon="quill:calendar" width="18px" />
                    <div class="relative">
                        <div class="hover:cursor-pointer">
                            <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                        </div>
                        <UiDropdownMenu idx="1">
                            <li class="flex !justify-center bg-layer">
                                Mesocycle
                            </li>
                            <li>
                                <Icon icon="clarity:note-line" /> View Notes
                            </li>
                            <li>
                                <Icon icon="tabler:italic" /> Rename
                            </li>
                            <li>
                                <Icon icon="bi:graph-up-arrow" /> Progressions
                            </li>
                            <li>
                                <Icon icon="carbon:book" /> Summary
                            </li>
                            <li>
                                <Icon icon="carbon:stop-outline" /> End
                            </li>

                            <li class="flex !justify-center bg-layer">
                                Workout
                            </li>
                            <li>
                                <Icon icon="icon-park-outline:write" /> New Note
                            </li>
                            <li>
                                <Icon icon="tabler:italic" /> Relabel
                            </li>
                            <li>
                                <Icon icon="material-symbols:add" /> Add Exercise
                            </li>
                            <li>
                                <Icon icon="hugeicons:weight-scale" /> Bodyweight
                            </li>
                            <li>
                                <Icon icon="carbon:reset" /> Reset
                            </li>
                            <li>
                                <Icon icon="carbon:stop-outline" /> End
                            </li>
                        </UiDropdownMenu>
                    </div>
                </div>
            </div>

            <!-- ===== Calendar ===== -->
            <div class="flex justify-between gap-1">
                <div class="flex flex-col items-center flex-1 gap-1" v-for="(week, idx) in mesocycle.calendar">
                    <p>WEEK {{ idx }}</p>
                    <Link :href="`/mesocycles/${mesocycle.id}/day/${day.id}`"
                        class="bg-main px-2 py-1 rounded-sm w-full text-center" v-for="day in week"
                        :class="[{ 'bg-orange-700': $page.url.endsWith(day.id.toString()) }]">
                    {{ day.label }}
                    </Link>
                </div>
            </div>
        </UiBox>

        <!-- ===== Errors ===== -->
        <UiErrors :errors="errors" />


        <!-- ===== Exercises ===== -->
        <UiBox class="flex flex-col gap-2" v-for="(dayExercise, exercise_idx) in day.day_exercises">
            <div class="flex items-center justify-between">
                <div class="bg-orange-700 px-2 rounded">{{ dayExercise.exercise.muscle_group.name }}</div>

                <div class="flex align-end items-center gap-4">
                    <Icon icon="line-md:youtube" width="20px" />
                    <div class="relative">
                        <div class="hover:cursor-pointer">
                            <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                        </div>
                        <UiDropdownMenu :idx="exercise_idx" class="min-w-40">
                            <li>
                                <Icon icon="icon-park-outline:write" /> New Note
                            </li>
                            <li>
                                <Icon icon="mdi:arrow-up" /> Move Up
                            </li>
                            <li>
                                <Icon icon="mdi:arrow-down" /> Move Down
                            </li>
                            <li>
                                <Icon icon="ph:swap" /> Replace
                            </li>
                            <li>
                                <Icon icon="material-symbols:add" /> Add Set
                            </li>
                            <li>
                                <Icon icon="ix:skip" /> Skip
                            </li>
                            <li class="!text-red">
                                <Icon icon="material-symbols:delete-outline" /> Delete
                            </li>
                        </UiDropdownMenu>
                    </div>
                </div>
            </div>

            <div class="flex flex-col">
                <h4>{{ dayExercise.exercise.name }}</h4>
                <p class="text-secondary text-sm">{{ dayExercise.exercise.exercise_type }}</p>
            </div>

            <div class="grid grid-cols-6 gap-5">
                <div></div>
                <div class="col-span-2 text-center">WEIGHT</div>
                <div class="col-span-2 text-center">REPS</div>
                <div class="justify-self-end">LOG</div>
            </div>

            <div class="grid grid-cols-6 gap-5" v-for="set in dayExercise.sets">
                <div class="flex items-center justify-start">
                    <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                </div>
                <div class="col-span-2">
                    <InputText :placeholder="set?.target_weight ?? mesocycle.unit" v-model="set.weight"
                        inputClass="text-center" />
                </div>
                <div class="col-span-2">
                    <InputText :placeholder="set?.target_reps ?? '3 RIR'" v-model="set.reps" inputClass="text-center" />
                </div>
                <div class="flex items-center justify-end">
                    <Checkbox :checked="set.status == true" :value="set.status" v-model="set.status" true-value="1" false-value="0"
                        @change="handleUpdate(set)" class="mr-2"/>
                </div>
            </div>
        </UiBox>

        <ButtonPrimary>Finish Workout</ButtonPrimary>
    </div>
</template>
<script setup lang="ts">
    import { ref, reactive, onMounted } from 'vue';
    import { Icon } from '@iconify/vue'
    import UiBox from '@/Components/Ui/Box.vue';
    import UiErrors from '@/Components/Ui/Errors.vue';
    import ButtonPrimary from '@/Components/Button/Primary.vue';
    import UiDropdownMenu from '@/Components/Ui/DropdownMenu.vue';
    import InputText from '@/Components/Input/text.vue'
    import { Link, useForm, usePage } from '@inertiajs/vue3';
    import Checkbox from '@/Components/Checkbox.vue';
    import axios from 'axios';

    const props = defineProps<{
        mesocycle: Mesocycle,
        errors?: Object,
    }>();

    const day = ref(props.mesocycle.day);

    onMounted(() => {
        console.log(props.mesocycle);
    })

    // TODO: straight up redirect in laravel to /show and that s it (reload the page)
    async function handleUpdate(set: ExerciseSet) {
        if (!set.status) {
            return;
        }

        try {
            const res = await axios.patch(`/sets/${set.id}`, { ...set })
        } catch (error) {
            console.log(error)
            // Make an notificaiton error to handle these
        }

        // set = res.set;
    }


</script>