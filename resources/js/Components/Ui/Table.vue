<script setup lang="ts" generic="Row extends object">
export type TableColumn = {
    key: string;
    label: string;
    headerClass?: string;
    cellClass?: string;
};

const props = withDefaults(
    defineProps<{
        columns: TableColumn[];
        rows: Row[];
        rowKey?: keyof Row | ((row: Row) => string | number);
        emptyMessage?: string;
    }>(),
    {
        emptyMessage: "No records found.",
    }
);

function getRowKey(row: Row): string | number {
    if (typeof props.rowKey === "function") {
        return props.rowKey(row);
    }

    const key = props.rowKey ?? ("id" as keyof Row);

    return String(row[key]);
}

function getCellValue(row: Row, key: string): unknown {
    return (row as Record<string, unknown>)[key];
}
</script>

<template>
    <div class="overflow-hidden rounded-lg border border-layer-border bg-layer shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-layer-border">
                <thead class="bg-layer-light">
                    <tr>
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            scope="col"
                            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-secondary"
                            :class="column.headerClass"
                        >
                            {{ column.label }}
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-layer-border">
                    <tr v-for="row in rows" :key="getRowKey(row)" class="transition-colors hover:bg-layer-light">
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            class="whitespace-nowrap px-4 py-3 text-sm text-primary"
                            :class="column.cellClass"
                        >
                            <slot :name="`cell-${column.key}`" :row="row" :value="getCellValue(row, column.key)">
                                {{ getCellValue(row, column.key) ?? "—" }}
                            </slot>
                        </td>
                    </tr>

                    <tr v-if="rows.length === 0">
                        <td :colspan="columns.length" class="px-4 py-10 text-center text-sm text-secondary">
                            <slot name="empty">{{ emptyMessage }}</slot>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
