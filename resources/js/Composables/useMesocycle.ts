import { router } from "@inertiajs/vue3";

export function useMesocycle() {
    const addExercise = (exerciseID: number, day: Day) => {
        router.post(
            route("dayExercises.store", { day: day.id }),
            { exercise_id: exerciseID },
            { preserveState: false }
        );
    };

    const replaceExercise = (exerciseID: number, day: Day) => {
        router.post(route("dayExercises.replace"), { exercise_id: exerciseID, day: day.id });
    };

    const removeExercise = async (dayExerciseID: number, day: Day) => {
        router.delete(route("dayExercise.destroy", { day: day.id, dayExercise: dayExerciseID }), {
            preserveState: "errors",
        });
    };

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

    const toggleDay = (dayID: number) => {
        router.patch(route("days.toggle", { day: dayID }));
    };

    return {
        addExercise,
        removeExercise,
        replaceExercise,

        removeSet,
        addSet,
        updateSet,
        toggleDay,
    };
}
