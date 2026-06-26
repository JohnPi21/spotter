<template>
    <nav
        class="fixed inset-x-0 bottom-0 z-30 border-t border-layer-border bg-layer px-3 pb-[env(safe-area-inset-bottom)]"
        aria-label="Admin mobile navigation"
    >
        <ul class="grid h-[var(--mobilenav-height)] list-none grid-cols-2 gap-2">
            <li v-for="item in navigation" :key="item.path">
                <Link
                    :href="item.path"
                    class="flex h-full items-center justify-center gap-2 rounded-md px-2 text-xs transition"
                    :class="isActive(item) ? 'text-primary' : 'text-secondary hover:text-primary'"
                    :aria-current="isActive(item) ? 'page' : undefined"
                >
                    <Icon :icon="item.icon" width="21" />
                    <span>{{ item.name }}</span>
                </Link>
            </li>
        </ul>
    </nav>
</template>

<script setup lang="ts">
import { adminNavigation, applicationNavigation, type AdminNavigationItem } from "@/Components/Admin/navigation";
import { Icon } from "@iconify/vue";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();
const navigation = [...adminNavigation, ...applicationNavigation];

function isActive(item: AdminNavigationItem): boolean {
    const currentPath = page.url.split("?")[0];

    return item.exact ? currentPath === item.path : currentPath.startsWith(item.path);
}
</script>
