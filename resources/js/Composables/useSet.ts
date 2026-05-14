import { router } from "@inertiajs/vue3";

export function useSet() {
    const updateSet = async (set: ExerciseSet, dayExerciseId: number) => {
        if (!set.status) return;

        router.patch(
            route("sets.update", { dayExercise: dayExerciseId, set: set.id }),
            { ...set },
            {
                preserveState: true,
                preserveScroll: true,
                onError: () => (set.status = false),
            }
        );
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
    };
}
