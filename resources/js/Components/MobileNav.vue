<script setup lang="ts">
import { useAppNavigation } from "@/Composables/useAppNavigation";
import { Icon } from "@iconify/vue";
import { Link } from "@inertiajs/vue3";

const { links } = useAppNavigation();
</script>

<template>
    <nav
        aria-label="Mobile navigation"
        class="fixed inset-x-0 bottom-0 z-30 border-t border-layer-border bg-main pb-[env(safe-area-inset-bottom)]"
    >
        <ul class="mx-auto flex h-[var(--app-mobile-nav-height)] max-w-[768px] list-none items-stretch">
            <li v-for="link in links" :key="link.id" class="min-w-0 flex-1">
                <Link
                    :href="link.path"
                    :aria-current="link.active ? 'page' : undefined"
                    class="flex h-full min-h-[48px] flex-col items-center justify-center gap-1 border-t-2 text-[11px] transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-text-orange"
                    :class="
                        link.active
                            ? 'border-text-orange text-text-orange'
                            : 'border-transparent text-secondary hover:text-primary'
                    "
                >
                    <Icon :icon="link.icon" width="21" class="shrink-0" aria-hidden="true" />
                    <span>{{ link.mobileName }}</span>
                </Link>
            </li>
        </ul>
    </nav>
</template>
