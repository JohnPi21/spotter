import { compileScript, parse } from "@vue/compiler-sfc";
import { renderToString } from "@vue/server-renderer";
import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import { createRequire } from "node:module";
import test from "node:test";
import ts from "typescript";
import { createSSRApp, defineComponent, h } from "vue";

const require = createRequire(import.meta.url);
const emptyComponent = defineComponent({ render: () => null });

function component(path, load) {
    const { descriptor } = parse(readFileSync(new URL(path, import.meta.url), "utf8"));
    const script = compileScript(descriptor, { id: "dashboard-test", inlineTemplate: true });
    const compiled = ts.transpileModule(script.content, {
        compilerOptions: { module: ts.ModuleKind.CommonJS, esModuleInterop: true },
    }).outputText;
    const module = { exports: {} };
    new Function("require", "module", "exports", "route", compiled)(load, module, module.exports, () => "/workout");
    return module.exports.default;
}

const Dashboard = component("../../resources/js/Pages/Dashboard.vue", (name) => {
    if (name === "@/Components/Chart/WeeklySets.vue") return emptyComponent;
    if (name === "@iconify/vue") return { Icon: emptyComponent };
    if (name === "@inertiajs/vue3") {
        return {
            Head: emptyComponent,
            Link: defineComponent({
                setup:
                    (_, { slots }) =>
                    () =>
                        h("a", slots.default?.()),
            }),
        };
    }
    return require(name);
});

function training() {
    return {
        weeks: Array.from({ length: 8 }, (_, index) => ({
            start: `week-${index}`,
            label: `Week ${index}`,
            range: `Week ${index} range`,
            isCurrent: index === 7,
            sets: index === 7 ? 9 : 0,
            sessions: index === 7 ? 1 : 0,
        })),
        muscleGroups: [{ id: 1, name: "Chest", sets: [0, 0, 0, 0, 0, 0, 0, 9], plannedSets: 12 }],
        activePlan: { id: 1, name: "Upper / Lower", daysPerWeek: 4, setsPerWeek: 12, muscleGroupsCount: 1 },
        recentWorkouts: [
            {
                id: 1,
                mesocycleId: 1,
                mesocycleName: "Upper / Lower",
                label: "Upper A",
                finishedAt: "Yesterday",
                sets: 9,
                exercises: 3,
                muscleGroups: [{ name: "Chest", sets: 9 }],
                load: 450,
                volume: 3600,
                unit: "lb",
            },
        ],
        timezone: "UTC",
    };
}

async function renderDashboard(data) {
    const app = createSSRApp(Dashboard, { training: data });
    app.config.globalProperties.route = () => "/workout";
    return renderToString(app);
}

test("dashboard emphasises weekly completed sets and keeps the current plan separate", async () => {
    const html = await renderDashboard(training());
    assert.match(html, /Completed sets/);
    assert.match(html, /Where your sets went/);
    assert.match(html, /Chest/);
    assert.match(html, /9 sets/);
    assert.match(html, /12 sets/);
    assert.match(html, /Current plan/);
    assert.match(html, /Upper \/ Lower/);
    assert.doesNotMatch(html, /Best Lifts|Unlock Your Big 3|By Weight|By Volume/);
});

test("load and volume are secondary details and retain the session's own unit", async () => {
    const html = await renderDashboard(training());
    assert.match(html, /<details[^>]*>/);
    assert.doesNotMatch(html, /<details[^>]* open/);
    assert.match(html, /Load &amp; volume/);
    assert.match(html, /450 lb/);
    assert.match(html, /3,600 lb × reps/);
});

test("an empty account renders useful empty states without fabricated workouts", async () => {
    const data = training();
    data.weeks.forEach((week) => {
        week.sets = 0;
        week.sessions = 0;
    });
    data.muscleGroups = [];
    data.activePlan = null;
    data.recentWorkouts = [];
    const html = await renderDashboard(data);
    assert.match(html, /Your next set starts the story/);
    assert.match(html, /No completed sets for this selection/);
    assert.match(html, /Finished workouts will appear here/);
    assert.doesNotMatch(html, /Upper A|Deadlift|undefined|NaN/);
});

test("planned muscle groups remain visible before any sets are completed", async () => {
    const data = training();
    data.muscleGroups[0].sets = Array(8).fill(0);
    const html = await renderDashboard(data);
    assert.match(html, /Chest/);
    assert.match(html, /0 sets/);
    assert.match(html, /12 sets/);
});

test("the chart stacks integer weekly set counts and filters to the chosen muscle group", async () => {
    let received;
    const Chart = component("../../resources/js/Components/Chart/WeeklySets.vue", (name) => {
        if (name === "vue-chartjs")
            return {
                Bar: defineComponent({
                    props: ["data", "options"],
                    setup(props) {
                        received = props;
                        return () => h("canvas");
                    },
                }),
            };
        return require(name);
    });
    const data = training();
    const groups = [...data.muscleGroups, { id: 2, name: "Back", sets: Array(8).fill(3), plannedSets: 6 }];
    await renderToString(createSSRApp(Chart, { weeks: data.weeks, groups, selectedGroup: 2 }));
    assert.deepEqual(
        received.data.datasets.map((dataset) => dataset.label),
        ["Back"]
    );
    assert.deepEqual(received.data.datasets[0].data, Array(8).fill(3));
    assert.equal(received.options.scales.y.ticks.precision, 0);
    assert.equal(received.options.scales.y.stacked, true);
    assert.equal(received.options.scales.y.beginAtZero, true);
});
