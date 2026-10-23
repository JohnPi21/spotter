import type { PageProps } from "@/types";
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";

type NavigationItem = {
    id: "dashboard" | "workout" | "mesocycles" | "account";
    name: string;
    mobileName: string;
    path: string;
    icon: string;
    active: boolean;
};

export function useAppNavigation() {
    const page = usePage<PageProps<{ mesocycle?: { status?: number } }>>();
    const currentPath = computed(() => page.url.split(/[?#]/)[0].replace(/\/$/, ""));
    const hasActiveMesocycle = computed(() => Boolean(page.props.auth?.flags?.hasActiveMeso));
    const isWorkout = computed(
        () =>
            hasActiveMesocycle.value &&
            (currentPath.value === "/mesocycles/current-day" ||
                (/^\/mesocycles\/\d+\/days\/\d+$/.test(currentPath.value) && page.props.mesocycle?.status === 1))
    );
    const links = computed<NavigationItem[]>(() => [
        {
            id: "dashboard",
            name: "Dashboard",
            mobileName: "Overview",
            path: route("dashboard"),
            icon: "heroicons:squares-2x2",
            active: currentPath.value === "/dashboard",
        },
        ...(hasActiveMesocycle.value
            ? [
                  {
                      id: "workout" as const,
                      name: "Current workout",
                      mobileName: "Workout",
                      path: route("mesocycles.current"),
                      icon: "tabler:barbell",
                      active: isWorkout.value,
                  },
              ]
            : []),
        {
            id: "mesocycles",
            name: "Mesocycles",
            mobileName: "Mesocycles",
            path: route("mesocycles"),
            icon: "heroicons:rectangle-stack",
            active: /^\/mesocycles(?:\/|$)/.test(currentPath.value) && !isWorkout.value,
        },
        {
            id: "account",
            name: "Account",
            mobileName: "Account",
            path: route("profile.edit"),
            icon: "heroicons:user-circle",
            active: /^\/profile(?:\/|$)/.test(currentPath.value),
        },
    ]);
    const user = computed(() => page.props.auth.user);

    return { links, user };
}
