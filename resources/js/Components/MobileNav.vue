<template>
    <aside id="default-sidebar" class="fixed bottom-0 bg-layer border border-layer-border w-full" aria-label="SideNav">
        <div class="p-2">
            <nav class="flex flex-column">
                <ul class="list-none flex justify-between flex-auto gap-2">
                    <li class="p-1 hover:bg-accent rounded" v-for="(link, idx) in links" :key="idx">
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
    import { Link } from '@inertiajs/vue3';
    import { Icon } from '@iconify/vue';

    const links = [
        { name: 'Current Workout', path: '/mesocycles/current-day', icon: 'tabler:barbell' },
        { name: 'Mesocycles', path: '/mesocycles', icon: 'entypo:cycle' },
        { name: 'Account', path: '/profile', icon: 'codicon:account' },
    ]
</script>
<style lang="scss" scoped></style>