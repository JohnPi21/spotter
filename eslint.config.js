import js from "@eslint/js";
import pluginVue from "eslint-plugin-vue";
import globals from "globals";
import tseslint from "typescript-eslint";
import vueParser from "vue-eslint-parser";

export default [
    // JS
    {
        ...js.configs.recommended,
        files: ["**/*.{js,mjs,cjs}"],
        languageOptions: {
            ...js.configs.recommended.languageOptions,
            globals: {
                ...globals.browser,
                ...globals.node,
            },
        },
    },

    // TS (spread because it's an array)
    ...tseslint.configs.recommended.map((config) => ({
        ...config,
        files: ["**/*.ts"],
    })),

    // Vue
    {
        files: ["**/*.vue"],
        languageOptions: {
            parser: vueParser,
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

    // Ignores
    {
        ignores: ["composer.json", "vendor/**", "public/**", "eslint.config.js", ".devcontainer/**"],
    },
];
