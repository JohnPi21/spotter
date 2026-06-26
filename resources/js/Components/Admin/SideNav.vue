<template>
    <aside
        class="fixed left-0 top-0 z-30 h-full w-[var(--sidenav-width)] border-r border-layer-border bg-layer"
        aria-label="Admin navigation"
    >
        <div class="flex h-full flex-col">
            <div class="flex h-16 items-center gap-3 border-b border-layer-border px-4">
                <div class="flex h-9 w-9 items-center justify-center rounded-md bg-accent text-primary">
                    <Icon icon="material-symbols:shield-outline" width="22" />
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-primary">Spotter</p>
                    <p class="text-xs text-secondary">Administration</p>
                </div>
            </div>

            <nav class="flex min-h-0 flex-1 flex-col justify-between gap-6 p-3">
                <ul class="flex list-none flex-col gap-1">
                    <li v-for="item in adminNavigation" :key="item.path">
                        <Link
                            :href="item.path"
                            class="flex h-10 items-center gap-3 rounded-md px-3 text-sm transition"
                            :class="
                                isActive(item)
                                    ? 'bg-layer-light text-primary'
                                    : 'text-secondary hover:bg-layer-light hover:text-primary'
                            "
                            :aria-current="isActive(item) ? 'page' : undefined"
                        >
                            <Icon :icon="item.icon" width="20" />
                            <span>{{ item.name }}</span>
                        </Link>
                    </li>
                </ul>

                <ul class="flex list-none flex-col gap-1 border-t border-layer-border pt-3">
                    <li v-for="item in applicationNavigation" :key="item.path">
                        <Link
                            :href="item.path"
                            class="flex h-10 items-center gap-3 rounded-md px-3 text-sm text-secondary transition hover:bg-layer-light hover:text-primary"
                        >
                            <Icon :icon="item.icon" width="20" />
                            <span>{{ item.name }}</span>
                        </Link>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
</template>

<script setup lang="ts">
import { adminNavigation, applicationNavigation, type AdminNavigationItem } from "@/Components/Admin/navigation";
import { Icon } from "@iconify/vue";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();

function isActive(item: AdminNavigationItem): boolean {
    const currentPath = page.url.split("?")[0];

    return item.exact ? currentPath === item.path : currentPath.startsWith(item.path);
}
</script>
