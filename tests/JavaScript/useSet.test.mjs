import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import { createRequire } from "node:module";
import test from "node:test";
import ts from "typescript";

const require = createRequire(import.meta.url);
const source = readFileSync(new URL("../../resources/js/Composables/useSet.ts", import.meta.url), "utf8");
const compiled = ts.transpileModule(source, {
    compilerOptions: { module: ts.ModuleKind.CommonJS, esModuleInterop: true },
}).outputText;

function setup(patch) {
    const module = { exports: {} };
    const load = (name) => {
        if (name === "axios") return { patch, isAxiosError: (error) => error.isAxiosError === true };
        if (name === "@inertiajs/vue3") {
            return { router: { patch: () => assert.fail("Set saves must not initiate an Inertia visit") } };
        }
        return require(name);
    };
    const route = (name, params) => `/day-exercises/${params.dayExercise}/sets/${params.set}`;
    new Function("require", "module", "exports", "route", compiled)(load, module, module.exports, route);
    return module.exports.useSet();
}

test("saving one set leaves other typed sets untouched and requests JSON", async () => {
    const sets = [1, 2, 3].map((id) => ({ id, reps: "3", weight: "25", status: id === 1 }));
    const otherDrafts = structuredClone(sets.slice(1));
    const state = setup(async (url, payload, config) => {
        assert.equal(url, "/day-exercises/10/sets/1");
        assert.deepEqual(payload, { reps: "3", weight: "25", status: true });
        assert.equal(config.headers.Accept, "application/json");
        return { data: { set: { ...sets[0], reps: 3, weight: "25.00", finished_at: "2026-10-18T12:00:00Z" } } };
    });

    await state.updateSet(sets[0], 10);

    assert.equal(sets[0].status, true);
    assert.equal(sets[0].weight, "25.00");
    assert.deepEqual(sets.slice(1), otherDrafts);
    assert.equal(state.isSavingSet(1), false);
});

test("duplicate saves are blocked while the same set is pending", async () => {
    let complete;
    let requests = 0;
    const set = { id: 1, reps: 3, weight: 25, status: true };
    const state = setup(() => {
        requests++;
        return new Promise((resolve) => { complete = resolve; });
    });

    const pending = state.updateSet(set, 10);
    assert.equal(state.isSavingSet(1), true);
    await state.updateSet(set, 10);
    assert.equal(requests, 1);
    complete({ data: { set: { ...set, finished_at: "2026-10-18T12:00:00Z" } } });
    await pending;
    assert.equal(state.isSavingSet(1), false);
});

test("validation failure keeps entered values, restores checkbox, and allows retry", async () => {
    const set = { id: 1, reps: "999", weight: "25", status: true };
    const state = setup(async () => {
        throw { isAxiosError: true, response: { data: { errors: { reps: ["Too many reps."] } } } };
    });

    await state.updateSet(set, 10);

    assert.deepEqual(set, { id: 1, reps: "999", weight: "25", status: false });
    assert.deepEqual(state.setErrors.value, { reps: "Too many reps." });
    assert.equal(state.isSavingSet(1), false);
});

test("network failure displays an error without losing the input values", async () => {
    const set = { id: 1, reps: "3", weight: "25", status: true };
    const state = setup(async () => { throw new Error("Offline"); });

    await state.updateSet(set, 10);

    assert.equal(set.reps, "3");
    assert.equal(set.weight, "25");
    assert.equal(set.status, false);
    assert.match(state.setErrors.value.set, /Could not save/);
    assert.equal(state.isSavingSet(1), false);
});
