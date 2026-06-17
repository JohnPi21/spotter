import { useToastStore } from "@/stores/toastStore";
import { router } from "@inertiajs/vue3";
import axios from "axios";

export function useMesocycle() {
    const toast = useToastStore();

    const addExercise = (exerciseId: number, day: Day) => {
        router.post(
            route("dayExercises.store", { day: day.id }),
            { exercise_id: exerciseId },
            { preserveState: false }
        );
    };

    const replaceExercise = (exerciseId: number, day: Day) => {
        router.post(route("dayExercises.replace"), { exercise_id: exerciseId, day: day.id });
    };

    const removeExercise = async (dayExerciseId: number, day: Day) => {
        router.delete(route("dayExercise.destroy", { day: day.id, dayExercise: dayExerciseId }), {
            preserveState: "errors",
        });
    };

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

    const copyMeso = async (mesocycleId: number): Promise<void> => {
        try {
            const response = await axios.get<MesocycleCopyResponse>(
                route("mesocycles.copy", { mesocycle: mesocycleId })
            );

            await navigator.clipboard.writeText(response.data.text);
            toast.success(response.data.message);
        } catch (error) {
            console.error(error);
        }
    };

    const toggleDay = (dayId: number) => {
        router.patch(route("days.toggle", { day: dayId }));
    };

    return {
        addExercise,
        removeExercise,
        replaceExercise,

        removeSet,
        addSet,
        updateSet,
        toggleDay,

        copyMeso,
    };
}
