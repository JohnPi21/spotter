<template>
    <aside
        id="default-sidebar"
        class="fixed left-0 top-0 h-full w-[var(--sidenav-width)] border border-layer-border bg-layer"
        aria-label="SideNav"
    >
        <div class="p-2">
            <nav class="flex-column flex">
                <ul class="flex flex-auto list-none flex-col gap-2">
                    <template v-for="(link, idx) in links" :key="idx">
                        <li class="rounded p-1 hover:bg-accent" v-if="showLink(link)">
                            <Link :href="link.path" class="flex items-center gap-2">
                                <Icon :icon="link.icon" width="25px" />
                                <span class="leading-7">{{ link.name }}</span>
                            </Link>
                        </li>
                    </template>
                </ul>
            </nav>
        </div>
    </aside>
</template>
<script setup>
import { Icon } from "@iconify/vue";
import { Link, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

const page = usePage();

const links = computed(() => [
    {
        name: "Dashboard",
        path: "/",
        icon: "material-symbols:dashboard-outline",
    },
    {
        name: "Current Workout",
        path: "/mesocycles/current-day",
        icon: "tabler:barbell",
        show: page.props.auth.flags.hasActiveMeso,
    },
    {
        name: "Mesocycles",
        path: "/mesocycles",
        icon: "entypo:cycle",
    },
    {
        name: "Account",
        path: "/profile",
        icon: "codicon:account",
    },
]);

function showLink(listLink) {
    if (!listLink.hasOwnProperty("show")) return true;

    return listLink.show;
}
</script>
<style lang="scss" scoped></style>
