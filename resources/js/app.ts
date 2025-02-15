import './bootstrap'; // Core not the framework
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import Layout from '@/Layout.vue';

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        const page = pages[`./Pages/${name}.vue`]

        page.default.layout = page.default.layout || Layout;

        return page;
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia()
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .mount(el)
    },
})