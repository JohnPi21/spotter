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
];

export const applicationNavigation: AdminNavigationItem[] = [
    {
        name: "Return to app",
        path: "/dashboard",
        icon: "material-symbols:arrow-back-rounded",
    },
];
