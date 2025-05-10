<template>
    <div class="flex flex-col gap-3 my-2 max-w-[768px] mx-auto">
        <UiBox class="flex flex-col">

            <div class="flex justify-between items-center mb-2">
                <div class="flex flex-col">
                    <p class="text-secondary text-sm">{{ mesocycle.name }}</p>
                    <h3>WEEK {{ day.week }} DAY {{ day.day_order }}: {{ day.label }} </h3>
                </div>

                <div class="flex align-end items-center gap-3">
                    <div class="bg-green px-2 rounded flex items-center gap-1" v-if="mesocycle.day.status === 1">
                        <p>Completed</p>
                        <Icon icon="ep:success-filled" />
                    </div>
                    <Icon icon="quill:calendar" width="18px" />

                    <UiDropdownMenu idx="1">
                        <template #header>
                            <div class="hover:cursor-pointer">
                                <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                            </div>
                        </template>

                        <template v-for="(section, index) in dropdownItems" :key="index">
                            <li class="flex !justify-center bg-layer">{{ section.section }}</li>
                            <li v-for="(item, i) in section.items" :key="i">
                                <Icon :icon="item.icon" /> {{ item.label }}
                            </li>
                        </template>
                    </UiDropdownMenu>
                </div>
            </div>

            <!-- ===== Calendar ===== -->
            <div class="flex justify-between gap-1">
                <div class="flex flex-col items-center flex-1 gap-1" v-for="(week, idx) in mesocycle.calendar">
                    <p>WEEK {{ idx }}</p>
                    <Link :href="`/mesocycles/${mesocycle.id}/day/${weekDay.id}`"
                        class="bg-main px-2 py-1 rounded-sm w-full text-center flex items-center justify-center gap-1"
                        v-for="weekDay in week"
                        :class="[{ 'bg-orange-700': isActiveDay(weekDay.id) }, { 'border-border-green border opacity-50': weekDay.status == 1 }]">

                    <Icon icon="ep:success-filled" class="text-green" v-if="weekDay.status == 1" />
                    {{ weekDay.label }}
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

                    <UiDropdownMenu :idx="exercise_idx">
                        <template #header>
                            <div class="hover:cursor-pointer">
                                <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                            </div>
                        </template>

                        <li v-for="(item, i) in exerciseDropdownItems" :key="i" :class="item.class"
                            @click="item.action(day.id, dayExercise.id)">
                            <Icon :icon="item.icon" /> {{ item.label }}
                        </li>
                    </UiDropdownMenu>
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

            <div class="grid grid-cols-6 gap-5" v-for="(set, set_idx) in dayExercise.sets">
                <div class="flex items-center justify-start">
                    <UiDropdownMenu :idx="set_idx">
                        <template #header>
                            <div class="hover:cursor-pointer">
                                <Icon icon="iconamoon:menu-kebab-vertical" width="18px" />
                            </div>
                        </template>

                        <li v-for="(item, i) in setDropdownItems" :key="i" :class="item.class"
                            @click="item.action(set.id)">
                            <Icon :icon="item.icon" /> {{ item.label }}
                        </li>
                    </UiDropdownMenu>
                </div>

                <div class="col-span-2">
                    <InputText :placeholder="set?.target_weight ?? mesocycle.unit" v-model="set.weight"
                        inputClass="text-center" />
                </div>
                <div class="col-span-2">
                    <InputText :placeholder="set?.target_reps != null ? `${set.target_reps} RIR` : '3 RIR'"
                        v-model="set.reps" inputClass="text-center" />
                </div>
                <div class="flex items-center justify-end">
                    <Checkbox :checked="set.status == true" :value="set.status" v-model="set.status" true-value="1"
                        false-value="0" @change="updateSet(set)" class="mr-2" />
                </div>
            </div>
        </UiBox>

        <ButtonPrimary @click="toggleDay(day.id)" v-if="mesocycle.day.status === 0">Finish Workout</ButtonPrimary>
        <ButtonSecondary @click="toggleDay(day.id)" v-else>Reactivate</ButtonSecondary>

    </div>
</template>
<script setup lang="ts">
    import { ref, reactive, onMounted, computed } from 'vue';
    import { Icon } from '@iconify/vue'
    import UiBox from '@/Components/Ui/Box.vue';
    import UiErrors from '@/Components/Ui/Errors.vue';
    import ButtonPrimary from '@/Components/Button/Primary.vue';
    import ButtonSecondary from '@/Components/Button/Secondary.vue';
    import UiDropdownMenu from '@/Components/Ui/DropdownMenu.vue';
    import InputText from '@/Components/Input/text.vue'
    import { Link, router, useForm, usePage } from '@inertiajs/vue3';
    import Checkbox from '@/Components/Checkbox.vue';
    import axios from 'axios';

    const props = defineProps<{
        mesocycle: Mesocycle,
        errors?: Object,
    }>();

    const day = ref(props.mesocycle.day);

    function isActiveDay(dayID: Number) {
        const url = usePage().url.split("/");
        const length = url.length - 1;

        return Number(url[length]) === dayID
    }

    onMounted(() => {
        console.log(props.mesocycle);
    })

    // TODO: straight up redirect in laravel to /show and that s it (reload the page)
    async function updateSet(set: ExerciseSet) {
        if (!set.status) {
            return;
        }

        try {
            const res = await axios.patch(`/sets/${set.id}`, { ...set })
        } catch (error) {
            console.log(error)
            // Make an notificaiton error to handle these
        }
    }


    async function removeExercise(dayExerciseID: number, exerciseID: number) {
        router.delete('/', {
            preserveState: 'errors'
        })
    }

    async function addSet(dayExerciseID: number) {
        router.post('/sets', { dayExerciseID }, {
            preserveState: false,
        });
    }

    async function removeSet(setID: number) {
        router.delete(`/sets/${setID}`, {
            preserveState: false
        })
    }

    async function addComment() {
        console.log('addig comment');
    }

    async function toggleDay(dayID: number) {
        router.patch(`/day/${dayID}`)
    }



    const dropdownItems = [
        {
            section: "Mesocycle",
            items: [
                { icon: "clarity:note-line", label: "View Notes" },
                { icon: "tabler:italic", label: "Rename" },
                { icon: "bi:graph-up-arrow", label: "Progressions" },
                { icon: "carbon:book", label: "Summary" },
                { icon: "carbon:stop-outline", label: "End" },
            ],
        },
        {
            section: "Workout",
            items: [
                { icon: "icon-park-outline:write", label: "New Note" },
                { icon: "tabler:italic", label: "Relabel" },
                { icon: "material-symbols:add", label: "Add Exercise" },
                { icon: "hugeicons:weight-scale", label: "Bodyweight" },
                { icon: "carbon:reset", label: "Reset" },
                { icon: "carbon:stop-outline", label: "End" },
            ],
        },
    ];

    const exerciseDropdownItems = [
        {
            icon: "icon-park-outline:write",
            label: "New Note",
            action: () => console.log("New Note clicked"),
        },
        {
            icon: "mdi:arrow-up",
            label: "Move Up",
            action: () => console.log("Move Up clicked"),
        },
        {
            icon: "mdi:arrow-down",
            label: "Move Down",
            action: () => console.log("Move Down clicked"),
        },
        {
            icon: "ph:swap",
            label: "Replace",
            action: () => console.log("Replace clicked"),
        },
        {
            icon: "material-symbols:add",
            label: "Add Set",
            action: (dayID: number) => addSet(dayID),
        },
        {
            icon: "ix:skip",
            label: "Skip",
            action: () => console.log("Skip clicked"),
        },
        {
            icon: "material-symbols:delete-outline",
            label: "Delete",
            class: "!text-red",
            action: (dayID: number, exerciseID: number) => removeExercise(dayID, exerciseID),
        },
    ];

    const setDropdownItems = [
        {
            icon: "material-symbols:delete-outline",
            label: "Change Type",
            action: () => console.log('Should Open Modal')
        },

        {
            icon: "material-symbols:delete-outline",
            label: "Delete",
            class: "!text-red",
            action: (setID: number) => removeSet(setID),
        },
    ];
</script>