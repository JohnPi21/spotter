<template>
    <Layout>
        <h1 class="text-3xl font-bold underline">
            {{ test }}
        </h1>
        <Input />
    </Layout>
</template>
<script setup>
    import Layout from '@/Layout.vue';
    import Input from '@components/input.vue';

    const test = 'Hello From Vue';
</script>