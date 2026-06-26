import { createInertiaApp } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
import { renderToString } from "@vue/server-renderer";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { createSSRApp, DefineComponent, h } from "vue";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import AdminLayout from "./Layouts/AdminLayout.vue";
import MainLayout from "./Layouts/MainLayout.vue";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        title: (title) => `${title} - ${appName}`,
        resolve: async (name) => {
            resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>("./Pages/**/*.vue"));
            const pages = import.meta.glob<DefineComponent>("./Pages/**/*.vue");
            const page = (await pages[`./Pages/${name}.vue`]()).default;

            // Same default layout logic as app.ts
            if (!page.layout) {
                page.layout = name.startsWith("Admin/") ? AdminLayout : MainLayout;
            }

            return page;
        },
        setup({ App, props, plugin }) {
            return createSSRApp({ render: () => h(App, props) })
                .use(plugin)
                .use(ZiggyVue, {
                    ...page.props.ziggy,
                    location: new URL(page.props.ziggy.location),
                });
        },
    })
);
