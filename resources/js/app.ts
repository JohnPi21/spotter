import { createInertiaApp } from "@inertiajs/vue3";
import { createPinia } from "pinia";
import { createApp, DefineComponent, h } from "vue";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import "./bootstrap";
import Layout from "./Layouts/MainLayout.vue";

const appName = import.meta.env.VITE_APP_NAME || "Spotter";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: async (name: string) => {
        const pages = import.meta.glob<DefineComponent>("./Pages/**/*.vue");
        const page = (await pages[`./Pages/${name}.vue`]()).default;

        // Ensure default layout is applied
        if (!page.layout) {
            page.layout = Layout;
        }

        return page;
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();

        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});
