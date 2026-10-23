import { compileScript, parse } from "@vue/compiler-sfc";
import { renderToString } from "@vue/server-renderer";
import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import { createRequire } from "node:module";
import test from "node:test";
import ts from "typescript";
import { createSSRApp, defineComponent, h } from "vue";

const require = createRequire(import.meta.url);
const source = readFileSync(new URL("../../resources/js/Pages/mesocycles/index.vue", import.meta.url), "utf8");
const { descriptor } = parse(source);
const script = compileScript(descriptor, { id: "mesocycle-list-test", inlineTemplate: true });
const compiled = ts.transpileModule(script.content, {
    compilerOptions: { module: ts.ModuleKind.CommonJS, esModuleInterop: true },
}).outputText;
const module = { exports: {} };
const emptyComponent = defineComponent({ render: () => null });
const load = (name) => {
    if (name.startsWith("@/Components/") || name === "@iconify/vue") {
        return name === "@iconify/vue" ? { Icon: emptyComponent } : emptyComponent;
    }
    if (name === "@/Composables/useMesocycle") return { useMesocycle: () => ({ copyMeso() {} }) };
    if (name === "@inertiajs/vue3") {
        return {
            Head: emptyComponent,
            Link: defineComponent({
                setup:
                    (_, { slots }) =>
                    () =>
                        h("a", slots.default?.()),
            }),
            router: {},
            useForm: () => ({ processing: false }),
        };
    }
    return require(name);
};
new Function("require", "module", "exports", "route", compiled)(load, module, module.exports, () => "/workout");

async function renderList(stats) {
    const app = createSSRApp(module.exports.default, {
        title: "Mesocycles",
        mesocycles: [
            {
                id: 1,
                name: "My routine",
                status: 1,
                weeks_duration: 4,
                days_per_week: 3,
                last_day: 1,
                planned_stats: stats,
            },
        ],
    });
    app.config.globalProperties.route = () => "/workout";
    return renderToString(app);
}

test("the list renders saved weekly totals and each muscle group's planned sets", async () => {
    const html = await renderList({
        muscle_groups_count: 2,
        sets_per_week: 21,
        muscle_groups: [
            { id: 1, name: "Chest", sets: 9 },
            { id: 2, name: "Back", sets: 12 },
        ],
    });
    assert.match(html, /2 muscle groups/);
    assert.match(html, /21 planned sets \/ week/);
    assert.match(html, /Planned sets \/ week/);
    assert.match(html, /Chest/);
    assert.match(html, /9 sets/);
    assert.match(html, /Back/);
    assert.match(html, /12 sets/);
    assert.doesNotMatch(html, /Sample data/);
});

test("legacy mesocycles without saved stats display an unavailable state", async () => {
    const html = await renderList(null);
    assert.match(html, /Planned volume is not available for this mesocycle yet/);
    assert.doesNotMatch(html, /undefined|NaN|Sample data/);
    assert.doesNotMatch(html, /planned sets \/ week/);
});

test("zero saved counts remain visible rather than appearing as missing data", async () => {
    const html = await renderList({ muscle_groups_count: 0, sets_per_week: 0, muscle_groups: [] });
    assert.match(html, /0 muscle groups/);
    assert.match(html, /0 planned sets \/ week/);
    assert.doesNotMatch(html, /not available|NaN/);
});
