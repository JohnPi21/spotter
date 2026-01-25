import { router } from "@inertiajs/vue3";

export function useSet() {
    const updateSet = async (set: ExerciseSet, dayExerciseID: number) => {
        if (!set.status) return;

        router.patch(
            route("sets.update", { dayExercise: dayExerciseID, set: set.id }),
            { ...set },
            {
                preserveState: true,
                preserveScroll: true,
                onError: () => (set.status = false),
            }
        );
    };

    const addSet = (dayExerciseID: number) => {
        router.post(
            route("sets.store", { dayExercise: dayExerciseID }),
            {},
            {
                preserveScroll: true,
                preserveState: true,
            }
        );
    };

    const removeSet = (dayExerciseID: number, setID: number) => {
        router.delete(route("sets.destroy", { dayExercise: dayExerciseID, set: setID }), {
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
