import { compileScript, parse } from "@vue/compiler-sfc";
import { renderToString } from "@vue/server-renderer";
import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import { createRequire } from "node:module";
import test from "node:test";
import ts from "typescript";
import { createSSRApp, defineComponent, h, reactive } from "vue";

const require = createRequire(import.meta.url);
const paths = {
    dashboard: "/dashboard",
    "mesocycles.current": "/mesocycles/current-day",
    mesocycles: "/mesocycles",
    "profile.edit": "/profile",
};
const route = (name) => `https://spotacus.test${paths[name]}`;
let page;
const emptyComponent = defineComponent({ render: () => null });
const inertia = {
    usePage: () => page,
    Link: defineComponent({
        props: ["href"],
        setup:
            (props, { slots }) =>
            () =>
                h("a", { href: props.href }, slots.default?.()),
    }),
};
const compile = (source) =>
    ts.transpileModule(source, {
        compilerOptions: { module: ts.ModuleKind.CommonJS, esModuleInterop: true },
    }).outputText;
const navigation = { exports: {} };
new Function(
    "require",
    "module",
    "exports",
    "route",
    compile(readFileSync(new URL("../../resources/js/Composables/useAppNavigation.ts", import.meta.url), "utf8"))
)((name) => (name === "@inertiajs/vue3" ? inertia : require(name)), navigation, navigation.exports, route);

function component(filename) {
    const { descriptor } = parse(
        readFileSync(new URL(`../../resources/js/Components/${filename}.vue`, import.meta.url), "utf8")
    );
    const script = compileScript(descriptor, { id: `navigation-${filename}`, inlineTemplate: true });
    const module = { exports: {} };
    new Function("require", "module", "exports", "route", compile(script.content))(
        (name) => {
            if (name === "@/Composables/useAppNavigation") return navigation.exports;
            if (name === "@inertiajs/vue3") return inertia;
            if (name === "@iconify/vue") return { Icon: emptyComponent };
            return require(name);
        },
        module,
        module.exports,
        route
    );
    return module.exports.default;
}

const SideNav = component("SideNav");
const MobileNav = component("MobileNav");
function setPage(url, active = true, status) {
    page = reactive({
        url,
        props: {
            auth: { user: { name: "Jane Doe" }, flags: { hasActiveMeso: active } },
            mesocycle: status === undefined ? undefined : { status },
        },
    });
}
async function render(Component) {
    const app = createSSRApp(Component);
    app.config.globalProperties.route = route;
    return renderToString(app);
}
function currentLinks(html) {
    return [...html.matchAll(/<a\b[^>]*aria-current="page"[^>]*>/g)].map((match) => match[0]);
}

test("sidebar shows branding and account identity with one active dashboard link", async () => {
    setPage("/dashboard?period=week");
    const html = await render(SideNav);
    assert.match(html, /Spotacus/);
    assert.match(html, /Jane Doe/);
    assert.match(html, />Account<\/span>/);
    assert.equal(currentLinks(html).length, 1);
    assert.match(currentLinks(html)[0], /\/dashboard/);
});

test("mobile navigation has visible labels for every available destination", async () => {
    setPage("/dashboard");
    const html = await render(MobileNav);
    for (const label of ["Overview", "Workout", "Mesocycles", "Account"]) assert.match(html, new RegExp(`>${label}<`));
    assert.match(html, /aria-label="Mobile navigation"/);
    assert.equal(currentLinks(html).length, 1);
});

test("both navigations hide the workout shortcut when there is no active mesocycle", async () => {
    setPage("/mesocycles", false);
    for (const Component of [SideNav, MobileNav]) {
        const html = await render(Component);
        assert.doesNotMatch(html, /href="https:\/\/spotacus.test\/mesocycles\/current-day"/);
        assert.equal(currentLinks(html).length, 1);
        assert.match(currentLinks(html)[0], /href="https:\/\/spotacus.test\/mesocycles"/);
    }
});

test("active workout pages highlight the workout shortcut instead of the mesocycle list", async () => {
    setPage("/mesocycles/4/days/12?tab=sets", true, 1);
    for (const Component of [SideNav, MobileNav]) {
        const links = currentLinks(await render(Component));
        assert.equal(links.length, 1);
        assert.match(links[0], /\/mesocycles\/current-day/);
    }
});

test("historical workout pages highlight mesocycles even when another mesocycle is active", async () => {
    setPage("/mesocycles/3/days/9", true, 0);
    const links = currentLinks(await render(MobileNav));
    assert.equal(links.length, 1);
    assert.match(links[0], /href="https:\/\/spotacus.test\/mesocycles"/);
});

test("creating and editing plans keep the mesocycles destination selected", async () => {
    for (const url of ["/mesocycles/create", "/mesocycles/4/edit", "/mesocycles/ai/create"]) {
        setPage(url);
        assert.equal(navigation.exports.useAppNavigation().links.value.find((item) => item.active)?.id, "mesocycles");
    }
});

test("navigation selection reacts to Inertia page changes and account routes", async () => {
    setPage("/dashboard");
    const { links } = navigation.exports.useAppNavigation();
    assert.equal(links.value.find((item) => item.active)?.id, "dashboard");
    page.url = "/profile/?tab=password";
    assert.equal(links.value.find((item) => item.active)?.id, "account");
    page.props.auth.flags.hasActiveMeso = false;
    assert.equal(links.value.length, 3);
});
