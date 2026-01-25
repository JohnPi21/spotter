import { router } from "@inertiajs/vue3";
import { Ref } from "vue";

export function useExercise(day: Ref) {
    const addExercise = (exerciseID: number) => {
        router.post(
            route("dayExercises.store", { day: day.value.id }),
            { exercise_id: exerciseID },
            { preserveState: false }
        );
    };

    const replaceExercise = (dayExerciseID: number, newExerciseID: number) => {
        router.patch(route("dayExercises.replace", { day: day.value.id }), {
            day_exercise_id: dayExerciseID,
            new_exercise_id: newExerciseID,
        });
    };

    const removeExercise = async (dayExerciseID: number, day: Day) => {
        router.delete(route("dayExercise.destroy", { day: day.id, dayExercise: dayExerciseID }), {
            preserveState: "errors",
        });
    };

    const moveUp = (dayExerciseID: number): Promise<void> | undefined => {
        const position = getPosition(dayExerciseID);

        if (position === -1 || position === 0) return;

        swap(day.value.day_exercises, position, position - 1);
    };

    const moveDown = (dayExerciseID: number) => {
        const position = getPosition(dayExerciseID);

        if (position === -1 || position === day.value.day_exercises.length - 1) return;

        swap(day.value.day_exercises, position, position + 1);

        updateOrder();
    };

    function getPosition(dayExerciseID: number): number {
        return day.value.day_exercises.findIndex((dayEx: Exercise) => dayEx.id == dayExerciseID);
    }

    async function updateOrder() {
        const order = day.value.day_exercises.map((ex: Exercise) => ex.id);

        router.patch(route("dayExercises.reorder", { day: day.value.id }), { order });
    }

    function swap<T>(arr: T[], from: number, to: number) {
        [arr[from], arr[to]] = [arr[to], arr[from]];
    }
    return {
        addExercise,
        removeExercise,
        replaceExercise,
        moveDown,
        moveUp,
    };
}
