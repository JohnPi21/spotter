<template>
    <aside id="default-sidebar" class="fixed top-0 left-0 bg-layer w-[var(--sidenav-width)] h-full" aria-label="SideNav">
        <div class="p-2">
            <nav class="flex flex-column">
                <ul class="list-none flex flex-auto flex-col gap-2">
                    <li class="p-1 hover:bg-accent rounded" v-for="(link, idx) in links" :key="idx">
                        <Link :href="link.path" class="flex items-center gap-2">
                            <Icon :icon="link.icon" width="25px"/>
                            <span class="leading-7">{{ link.name }}</span>
                        </Link>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
</template>
<script setup>
    import {Link} from '@inertiajs/vue3';
    import {Icon} from '@iconify/vue';
    import { useUserStore } from '../stores/userStore';

    const userStore = useUserStore();

    const links = [
        {name: 'Current Workout', path: '/', icon: 'tabler:barbell'},
        {name: 'Mesocycles', path: '/mesocycles', icon: 'entypo:cycle'},
        {name: 'Execises', path: '/', icon: 'solar:bill-list-linear'},
        {name: 'Templates', path: '/', icon: 'carbon:template'},
    ]
</script>
<style lang="scss" scoped></style>