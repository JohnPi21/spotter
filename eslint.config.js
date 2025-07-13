import css from "@eslint/css";
import js from "@eslint/js";
import json from "@eslint/json";
import pluginVue from "eslint-plugin-vue";
import { defineConfig } from "eslint/config";
import globals from "globals";
import tseslint from "typescript-eslint";

export default defineConfig([
    // JS/TS
    {
        files: ["**/*.{js,mjs,cjs,ts}"],
        plugins: { js },
        extends: ["js/recommended"],
        languageOptions: {
            globals: globals.browser,
        },
    },
    // TypeScript
    tseslint.configs.recommended,
    // Vue
    {
        files: ["**/*.vue"],
        languageOptions: {
            parser: await import("vue-eslint-parser"),
            parserOptions: {
                parser: tseslint.parser,
                ecmaVersion: 2020,
                sourceType: "module",
            },
        },
        plugins: {
            vue: pluginVue,
        },
        rules: {
            ...pluginVue.configs["flat/strongly-recommended"].rules,
        },
    },
    // JSON
    {
        files: ["**/*.json"],
        plugins: { json },
        language: "json/json",
        extends: ["json/recommended"],
        rules: {
            "json/no-empty-keys": "off",
        },
    },
    {
        files: ["**/*.jsonc"],
        plugins: { json },
        language: "json/jsonc",
        extends: ["json/recommended"],
    },
    // CSS
    {
        files: ["**/*.css"],
        plugins: { css },
        language: "css/css",
        extends: ["css/recommended"],
        rules: {
            "css/no-invalid-at-rules": "off",
        },
    },
    // Ignores
    {
        ignores: ["composer.json", "vendor/**", "public/**", "eslint.config.js", ".devcontainer/**"],
    },
]);
