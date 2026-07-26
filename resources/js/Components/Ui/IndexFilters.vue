<script setup lang="ts">
import UserLookup from "@/Components/Admin/UserLookup.vue";
import ButtonSecondary from "@/Components/Button/Secondary.vue";
import InputLabel from "@/Components/Input/InputLabel.vue";
import InputSelect from "@/Components/Input/Select.vue";
import InputText from "@/Components/Input/Text.vue";
import { Icon } from "@iconify/vue";
import { usePage } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, reactive, ref, watch } from "vue";

type FilterFieldBase = {
    key: string;
    label: string;
};

type FilterInputField = FilterFieldBase & {
    type: "text" | "email" | "number" | "date";
    placeholder?: string;
    pattern?: string;
    inputmode?: "email" | "numeric" | "search" | "text";
    min?: number;
    step?: number;
};

type FilterSelectField = FilterFieldBase & {
    type: "select";
    options: readonly FilterOption[];
};

type FilterUserField = FilterFieldBase & {
    type: "user";
    placeholder?: string;
};

export type FilterOption = {
    value: string;
    label: string;
};

export type IndexFilterField = FilterInputField | FilterSelectField | FilterUserField;

export type IndexSortField = {
    value: string;
    label: string;
};

export type SortDirection = "asc" | "desc";

export type IndexFilterPayload = {
    filter?: Record<string, string>;
    sort?: string;
    direction?: SortDirection;
};

const props = withDefaults(
    defineProps<{
        fields: readonly IndexFilterField[];
        sorts: readonly IndexSortField[];
        defaultDirection?: SortDirection;
        debounce?: number;
        processing?: boolean;
    }>(),
    {
        defaultDirection: "desc",
        debounce: 300,
        processing: false,
    }
);

const emit = defineEmits<{
    apply: [payload: IndexFilterPayload];
    clear: [];
}>();

const page = usePage();
const query = new URLSearchParams(page.url.split("?")[1] ?? "");

const values = reactive<Record<string, string>>(
    Object.fromEntries(props.fields.map((field) => [field.key, query.get(`filter[${field.key}]`) ?? ""]))
);

const sort = ref(query.get("sort") ?? "");
const direction = ref<SortDirection>(
    query.get("direction") === "asc" || query.get("direction") === "desc"
        ? (query.get("direction") as SortDirection)
        : props.defaultDirection
);

const sortOptions = computed(() => [{ value: "", label: "Default order" }, ...props.sorts]);

const directionOptions: readonly FilterOption[] = [
    { value: "asc", label: "Ascending" },
    { value: "desc", label: "Descending" },
];

let submitTimer: number | undefined;
let suppressAutoSubmit = false;

function submit(): void {
    window.clearTimeout(submitTimer);

    const activeFilters = Object.fromEntries(Object.entries(values).filter(([, value]) => value !== "")) as Record<
        string,
        string
    >;

    emit("apply", {
        filter: Object.keys(activeFilters).length > 0 ? activeFilters : undefined,
        sort: sort.value || undefined,
        direction: sort.value ? direction.value : undefined,
    });
}

function queueSubmit(): void {
    if (suppressAutoSubmit) {
        return;
    }

    window.clearTimeout(submitTimer);
    submitTimer = window.setTimeout(submit, props.debounce);
}

function clear(): void {
    window.clearTimeout(submitTimer);
    suppressAutoSubmit = true;

    for (const field of props.fields) {
        values[field.key] = "";
    }

    sort.value = "";
    direction.value = props.defaultDirection;
    suppressAutoSubmit = false;
    emit("clear");
}

watch(values, queueSubmit, { deep: true, flush: "sync" });
watch([sort, direction], queueSubmit, { flush: "sync" });

onBeforeUnmount(() => window.clearTimeout(submitTimer));
</script>

<template>
    <form class="flex flex-col gap-4 rounded-lg border border-layer-border bg-layer p-4" @submit.prevent="submit">
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
            <div v-for="field in fields" :key="field.key" class="flex min-w-0 flex-col gap-2">
                <InputLabel :for="`index-filter-${field.key}`" :value="field.label" />

                <UserLookup
                    v-if="field.type === 'user'"
                    :id="`index-filter-${field.key}`"
                    v-model="values[field.key]"
                    :placeholder="field.placeholder"
                />

                <InputSelect
                    v-else-if="field.type === 'select'"
                    :id="`index-filter-${field.key}`"
                    v-model="values[field.key]"
                    :options="field.options"
                />

                <InputText
                    v-else
                    :id="`index-filter-${field.key}`"
                    v-model="values[field.key]"
                    :type="field.type"
                    :placeholder="field.placeholder"
                    :pattern="field.pattern"
                    :inputmode="field.inputmode"
                    :min="field.min"
                    :step="field.step"
                />
            </div>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div v-if="sorts.length > 0" class="grid gap-3 sm:grid-cols-2">
                <div class="flex flex-col gap-2 sm:w-52">
                    <InputLabel for="index-filter-sort" value="Sort by" />
                    <InputSelect id="index-filter-sort" v-model="sort" :options="sortOptions" />
                </div>

                <div class="flex flex-col gap-2 sm:w-44">
                    <InputLabel for="index-filter-direction" value="Direction" />
                    <InputSelect
                        id="index-filter-direction"
                        v-model="direction"
                        :options="directionOptions"
                        :disabled="!sort"
                    />
                </div>
            </div>

            <div class="flex gap-2 sm:ml-auto">
                <ButtonSecondary type="button" :disabled="processing" @click="clear">
                    <Icon icon="material-symbols:filter-alt-off-outline" class="size-5" aria-hidden="true" />
                    Clear
                </ButtonSecondary>
            </div>
        </div>
    </form>
</template>
