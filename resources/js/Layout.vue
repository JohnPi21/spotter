<template>
    <nav>
        <Link href="/">Home</Link>
        <Link href="/workouts">Workouts</Link>
        <Link href="/exercises">Exercises</Link>
    </nav>
    <slot />
</template>
<script setup>
import {Link} from '@inertiajs/vue3';

</script>