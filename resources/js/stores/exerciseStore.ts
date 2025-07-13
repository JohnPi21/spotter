import axios from "axios";
import { defineStore } from "pinia";
import { ref } from "vue";

export const useExerciseStore = defineStore("exercise", () => {
    const exercisesByMuscle = ref<MuscleGroup[]>([]);

    const exercises = ref<Record<number, Exercise>>({});

    const muscleGroups = ref<Record<number, MuscleGroup>>({});

    async function load(): Promise<void> {
        try {
            const response = await axios.get("/exercises");

            exercises.value = response.data.exercises;

            muscleGroups.value = response.data.muscleGroups;

            exercisesByMuscle.value = Object.values(muscleGroups.value).map((mg: MuscleGroup) => {
                return {
                    ...mg,
                    exercises: Object.values(exercises.value).filter((ex: Exercise) => ex.muscle_group_id === mg.id),
                };
            });
        } catch (error) {
            console.error(error);
        }
    }

    return {
        exercises,
        muscleGroups,
        load,
        exercisesByMuscle,
    };
});
