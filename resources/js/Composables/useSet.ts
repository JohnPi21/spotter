import { router } from "@inertiajs/vue3";
import axios from "axios";
import { ref } from "vue";

export function useSet() {
    const savingSetIds = ref<number[]>([]);
    const setErrors = ref<Record<string, string>>({});
    const isSavingSet = (setId: number): boolean => savingSetIds.value.includes(setId);

    const updateSet = async (set: ExerciseSet, dayExerciseId: number): Promise<void> => {
        if (!set.status || isSavingSet(set.id)) return;

        savingSetIds.value.push(set.id);
        setErrors.value = {};

        try {
            const response = await axios.patch<{ set: ExerciseSet }>(
                route("sets.update", { dayExercise: dayExerciseId, set: set.id }),
                { reps: set.reps, weight: set.weight, status: set.status },
                { headers: { Accept: "application/json" } }
            );

            Object.assign(set, response.data.set, { status: Boolean(response.data.set.finished_at) });
        } catch (error) {
            set.status = Boolean(set.finished_at);

            if (axios.isAxiosError<{ message?: string; errors?: Record<string, string[]> }>(error)) {
                const validationErrors = error.response?.data.errors;
                setErrors.value = validationErrors
                    ? Object.fromEntries(Object.entries(validationErrors).map(([field, messages]) => [field, messages[0]]))
                    : { set: error.response?.data.message ?? "Could not save this set. Please try again." };
            } else {
                setErrors.value = { set: "Could not save this set. Please try again." };
            }
        } finally {
            savingSetIds.value = savingSetIds.value.filter((id) => id !== set.id);
        }
    };

    const addSet = (dayExerciseId: number) => {
        router.post(
            route("sets.store", { dayExercise: dayExerciseId }),
            {},
            {
                preserveScroll: true,
                preserveState: true,
            }
        );
    };

    const removeSet = (dayExerciseId: number, setId: number) => {
        router.delete(route("sets.destroy", { dayExercise: dayExerciseId, set: setId }), {
            preserveState: true,
            preserveScroll: true,
        });
    };

    return {
        removeSet,
        addSet,
        updateSet,
        isSavingSet,
        setErrors,
    };
}
