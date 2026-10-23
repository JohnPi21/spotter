<script setup lang="ts">
import { useAppNavigation } from "@/Composables/useAppNavigation";
import { Icon } from "@iconify/vue";
import { Link } from "@inertiajs/vue3";
import { computed } from "vue";

const { links, user } = useAppNavigation();
const trainingLinks = computed(() => links.value.filter((link) => link.id !== "account"));
const accountLink = computed(() => links.value.find((link) => link.id === "account")!);
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-30 w-[var(--sidenav-width)] border-r border-layer-border bg-main"
        aria-label="Sidebar"
    >
        <div class="flex h-full flex-col">
            <Link
                :href="route('dashboard')"
                aria-label="Spotacus home"
                class="flex h-20 shrink-0 items-center px-6 text-lg font-semibold tracking-tight text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-text-orange"
            >
                Spotacus
            </Link>

            <nav aria-label="Main navigation" class="min-h-0 flex-1 overflow-y-auto py-3">
                <ul class="flex list-none flex-col gap-1">
                    <li v-for="link in trainingLinks" :key="link.id">
                        <Link
                            :href="link.path"
                            :aria-current="link.active ? 'page' : undefined"
                            class="flex min-h-[44px] items-center gap-3 border-l-2 py-3 pl-[22px] pr-5 text-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-text-orange"
                            :class="
                                link.active
                                    ? 'border-text-orange bg-layer text-primary'
                                    : 'border-transparent text-secondary hover:bg-layer hover:text-primary'
                            "
                        >
                            <Icon
                                :icon="link.icon"
                                width="19"
                                class="shrink-0"
                                :class="{ 'text-text-orange': link.active }"
                                aria-hidden="true"
                            />
                            <span>{{ link.name }}</span>
                        </Link>
                    </li>
                </ul>
            </nav>

            <div class="border-t border-layer-border py-4">
                <p class="truncate px-6 pb-2 text-xs text-helper">{{ user.name }}</p>
                <Link
                    :href="accountLink.path"
                    :aria-current="accountLink.active ? 'page' : undefined"
                    class="flex min-h-[44px] items-center gap-3 border-l-2 py-3 pl-[22px] pr-5 text-sm transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-text-orange"
                    :class="
                        accountLink.active
                            ? 'border-text-orange bg-layer text-primary'
                            : 'border-transparent text-secondary hover:bg-layer hover:text-primary'
                    "
                >
                    <Icon
                        :icon="accountLink.icon"
                        width="19"
                        class="shrink-0"
                        :class="{ 'text-text-orange': accountLink.active }"
                        aria-hidden="true"
                    />
                    <span>Account</span>
                </Link>
            </div>
        </div>
    </aside>
</template>
