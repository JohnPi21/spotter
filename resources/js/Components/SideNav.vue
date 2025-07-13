<template>
    <aside id="default-sidebar"
        class="fixed top-0 left-0 bg-layer border border-layer-border w-[var(--sidenav-width)] h-full"
        aria-label="SideNav">
        <div class="p-2">
            <nav class="flex flex-column">
                <ul class="list-none flex flex-auto flex-col gap-2">
                    <template v-for="(link, idx) in links" :key="idx">
                        <li class="p-1 hover:bg-accent rounded" v-if="showLink(link)">
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
    import { Link } from '@inertiajs/vue3';
    import { Icon } from '@iconify/vue';
    import { usePage } from '@inertiajs/vue3';
    import { computed } from 'vue';

    const page = usePage();

    const links = computed(() => [
        {
            name: 'Dashboard',
            path: '/',
            icon: 'material-symbols:dashboard-outline',
            show: page.props.auth.flags.hasActiveMeso
        },
        {
            name: 'Current Workout',
            path: '/mesocycles/current-day',
            icon: 'tabler:barbell',
            show: page.props.auth.flags.hasActiveMeso
        },
        {
            name: 'Mesocycles',
            path: '/mesocycles',
            icon: 'entypo:cycle'
        },
        {
            name: 'Account',
            path: '/profile',
            icon: 'codicon:account'
        },
    ]);

    function showLink(listLink) {
        if (!listLink.hasOwnProperty('show')) return true;

        return listLink.show;
    }
</script>
<style lang="scss" scoped></style>