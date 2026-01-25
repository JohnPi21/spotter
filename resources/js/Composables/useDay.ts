import { usePage } from "@inertiajs/vue3";

export function useDay() {
    const isActiveDay = (dayID: number) => {
        const url = usePage().url.split("/");
        const length = url.length - 1;

        return Number(url[length]) === dayID;
    };
    return {
        isActiveDay,
    };
}
