import { router } from "@inertiajs/vue3";
import { Ref } from "vue";

export function useExercise(day: Ref) {
    const addExercise = (exerciseId: number) => {
        router.post(
            route("dayExercises.store", { day: day.value.id }),
            { exercise_id: exerciseId },
            { preserveState: false }
        );
    };

    const replaceExercise = (dayExerciseId: number, newExerciseId: number) => {
        router.patch(route("dayExercises.replace", { day: day.value.id }), {
            day_exercise_id: dayExerciseId,
            new_exercise_id: newExerciseId,
        });
    };

    const removeExercise = async (dayExerciseId: number, day: Day) => {
        router.delete(route("dayExercise.destroy", { day: day.id, dayExercise: dayExerciseId }), {
            preserveState: "errors",
        });
    };

    const moveUp = (dayExerciseId: number): Promise<void> | undefined => {
        const position = getPosition(dayExerciseId);

        if (position === -1 || position === 0) return;

        swap(day.value.day_exercises, position, position - 1);
    };

    const moveDown = (dayExerciseId: number) => {
        const position = getPosition(dayExerciseId);

        if (position === -1 || position === day.value.day_exercises.length - 1) return;

        swap(day.value.day_exercises, position, position + 1);

        updateOrder();
    };

    const addNote = (dayExerciseId: number, note: string) => {
        router.put(route("dayExercises.saveNote", { day: day.value.id }), {
            day_exercise_id: dayExerciseId,
            note: note,
        });
    };

    const removeNote = (dayExerciseId: number) => {
        router.delete(route("dayExercises.deleteNote", { day: day.value.id }), {
            data: { day_exercise_id: dayExerciseId },
        });
    };

    function getPosition(dayExerciseId: number): number {
        return day.value.day_exercises.findIndex((dayEx: Exercise) => dayEx.id == dayExerciseId);
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
        addNote,
        removeNote,
    };
}
