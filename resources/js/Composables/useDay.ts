import { usePage } from "@inertiajs/vue3";

export function useDay() {
    const isActiveDay = (dayId: number) => {
        const url = usePage().url.split("/");
        const length = url.length - 1;

        return Number(url[length]) === dayId;
    };
    return {
        isActiveDay,
    };
}
