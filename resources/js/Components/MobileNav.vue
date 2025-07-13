<template>
    <aside id="default-sidebar" class="fixed bottom-0 w-full border border-layer-border bg-layer" aria-label="SideNav">
        <div class="p-2">
            <nav class="flex-column flex">
                <ul class="flex flex-auto list-none justify-between gap-2">
                    <li class="rounded p-1 hover:bg-accent" v-for="(link, idx) in links" :key="idx">
                        <Link :href="link.path" class="flex items-center gap-2">
                            <Icon :icon="link.icon" width="25px" />
                            <!-- <span class="leading-7">{{ link.name }}</span> -->
                        </Link>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
</template>
<script setup lang="ts">
import { Icon } from "@iconify/vue";
import { Link } from "@inertiajs/vue3";

const links = [
    { name: "Account", path: "/profile", icon: "codicon:account" },
    { name: "Mesocycles", path: "/", icon: "icon-park-outline:cycle-one" },
    { name: "Current Workout", path: "/mesocycles/current-day", icon: "tabler:barbell" },
];
</script>
<style lang="scss" scoped></style>
