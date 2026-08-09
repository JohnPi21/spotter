export type AdminNavigationItem = {
    name: string;
    path: string;
    icon: string;
    exact?: boolean;
};

export const adminNavigation: AdminNavigationItem[] = [
    {
        name: "Dashboard",
        path: "/panel",
        icon: "material-symbols:dashboard-outline",
        exact: true,
    },
    {
        name: "Users",
        path: "/panel/users",
        icon: "material-symbols:group-outline",
    },
    {
        name: "Exercises",
        path: "/panel/exercises",
        icon: "material-symbols:fitness-center",
    },
];

export const applicationNavigation: AdminNavigationItem[] = [
    {
        name: "Return to app",
        path: "/dashboard",
        icon: "material-symbols:arrow-back-rounded",
    },
];
