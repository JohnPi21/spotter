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
                        <UiDropdownMenu idx="1" class="min-w-40">
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

            <div class="flex justify-between gap-1">
                <div class="flex flex-col items-center flex-1 gap-1" v-for="(week, idx) in mesocycle.calendar">
                    <p>WEEK {{ idx }}</p>
                    <Link :href="`/mesocycles/${mesocycle.id}/day/${day.id}`"
                        class="bg-main px-2 py-1 rounded-sm w-full text-center" v-for="day in week">
                    {{ day.label }}
                    </Link>
                </div>
            </div>
        </UiBox>

        <UiBox class="flex flex-col gap-2" v-for="(exercise, exercise_idx) in day.exercises">
            <div class="flex items-center justify-between">
                <div class="bg-orange-700 px-2 rounded">{{ exercise.muscle_group.name }}</div>

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
                <h4>{{ exercise.name }}</h4>
                <p class="text-secondary text-sm">{{ exercise.exercise_type }}</p>
            </div>

            <div class="grid grid-cols-6 gap-5">
                <div></div>
                <div class="col-span-2 text-center">WEIGHT</div>
                <div class="col-span-2 text-center">REPS</div>
                <div class="justify-self-end">LOG</div>
            </div>

            <div class="grid grid-cols-6 gap-5" v-for="set in exercise.sets">
                <div class="flex items-center justify-start">
                    <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                </div>
                <div class="col-span-2">
                    <InputText v-model="set.weight" />
                </div>
                <div class="col-span-2">
                    <InputText v-model="set.reps" />
                </div>
                <div class="flex items-center justify-end">
                    <input type="checkbox" v-model="set.status" class="mr-2">
                </div>
            </div>
        </UiBox>

        <!-- <UiBox class="flex flex-col gap-2" v-for="e in 3">
            <div class="flex items-center justify-between">
                <div class="bg-orange-700 px-2 rounded">Chest</div>

                <div class="flex align-end items-center gap-4">
                    <Icon icon="line-md:youtube" width="20px" />
                    <div class="relative">
                        <div class="hover:cursor-pointer">
                            <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                        </div>
                        <UiDropdownMenu :idx="e" class="min-w-40">
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
                <h4>Pushup (Deficit)</h4>
                <p class="text-secondary text-sm">BARBELL</p>
            </div>

            <div class="grid grid-cols-6 gap-5">
                <div></div>
                <div class="col-span-2 text-center">WEIGHT</div>
                <div class="col-span-2 text-center">REPS</div>
                <div class="justify-self-end">LOG</div>
            </div>

            <div class="grid grid-cols-6 gap-5" v-for="i in 5">
                <div class="flex items-center justify-start">
                    <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                </div>
                <div class="col-span-2">
                    <InputText v-model="val" />
                </div>
                <div class="col-span-2">
                    <InputText v-model="val" />
                </div>
                <div class="flex items-center justify-end">
                    <input type="checkbox" class="mr-2">
                </div>
            </div>
        </UiBox> -->
    </div>
</template>
<script setup>
    import { ref, reactive, onMounted } from 'vue';
    import { Icon } from '@iconify/vue'
    import UiBox from '@components/Ui/Box.vue';
    import UiDropdownMenu from '@components/Ui/DropdownMenu.vue';
    import InputText from '@components/Input/text.vue'
    import { Link } from '@inertiajs/vue3';

    const props = defineProps({
        mesocycle: Object
    })

    const day = ref(props.mesocycle.day);

    onMounted(() => {
        mesocycle.value = props.mesocycle;
        console.log(props.mesocycle)
    })

    const mesocycle = ref({
        id: 1,
        name: "Hypertrophy Plan",
        unit: "kg",
        days: 3,
        day: {
            label: 'Day 1',
            exercises: [
                { muscleGroup: 1, exerciseId: 22, },
                { muscleGroup: 2, exerciseId: 70, },
                { muscleGroup: 3, exerciseId: 87, },
                { muscleGroup: 3, exerciseId: null, },
            ]
        },
        weeks: 4,
        user_id: 1,
        notes: null,
        status: 1,
        meso_template_id: null,
        started_at: null,
        finished_at: null,
        deleted_at: null,
        created_at: "2025-01-01 14:46:32",
        updated_at: "2025-01-01 14:46:32"
    });

    const val = ref();
</script>