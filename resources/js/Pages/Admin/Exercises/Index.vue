<script setup lang="ts">
import ButtonPrimary from "@/Components/Button/Primary.vue";
import ButtonSecondary from "@/Components/Button/Secondary.vue";
import InputError from "@/Components/Input/InputError.vue";
import InputLabel from "@/Components/Input/InputLabel.vue";
import InputSelect from "@/Components/Input/Select.vue";
import InputText from "@/Components/Input/Text.vue";
import Modal from "@/Components/Modal.vue";
import ModalHeader from "@/Components/Modals/Header.vue";
import IndexFilters, {
    type FilterOption,
    type IndexFilterField,
    type IndexFilterPayload,
    type IndexSortField,
} from "@/Components/Ui/IndexFilters.vue";
import UiTable, { type TableColumn } from "@/Components/Ui/Table.vue";
import { Icon } from "@iconify/vue";
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import { computed, ref } from "vue";

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedExercises = {
    data: Exercise[];
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
};

const props = defineProps<{
    exercises: PaginatedExercises;
    exerciseTypes: FilterOption[];
}>();

const page = usePage();

const muscleGroupOptions = computed<FilterOption[]>(() =>
    (page.props.admin?.muscleGroups ?? []).map((muscleGroup) => ({
        value: String(muscleGroup.id),
        label: muscleGroup.name,
    }))
);

const columns: TableColumn[] = [
    { key: "id", label: "ID" },
    { key: "name", label: "Name" },
    { key: "muscle_group", label: "Muscle group" },
    { key: "exercise_type", label: "Type" },
    { key: "user", label: "Owner" },
    { key: "youtube_id", label: "Video", headerClass: "text-right", cellClass: "text-right" },
];

const filterFields = computed(
    () =>
        [
            {
                key: "id",
                label: "ID",
                type: "number",
                placeholder: "Exercise ID",
                min: 1,
                step: 1,
            },
            {
                key: "name",
                label: "Name",
                type: "text",
                placeholder: "Exercise name",
            },
            {
                key: "muscle_group",
                label: "Muscle group",
                type: "select",
                options: [{ value: "", label: "Any muscle group" }, ...muscleGroupOptions.value],
            },
            {
                key: "user",
                label: "Owner",
                type: "user",
                placeholder: "Any owner",
            },
            {
                key: "exercise_type",
                label: "Type",
                type: "select",
                options: [{ value: "", label: "Any type" }, ...props.exerciseTypes],
            },
            {
                key: "youtube_id",
                label: "YouTube ID",
                type: "text",
                placeholder: "Video ID",
            },
        ] as const satisfies readonly IndexFilterField[]
);

const sortFields = [
    { value: "id", label: "ID" },
    { value: "muscle_group", label: "Muscle group" },
    { value: "user", label: "Owner" },
    { value: "exercise_type", label: "Type" },
    { value: "created_at", label: "Created date" },
] as const satisfies readonly IndexSortField[];

const isFiltering = ref(false);
const createModalOpen = ref(false);

const form = useForm({
    name: "",
    muscle_group_id: "",
    exercise_type: "",
    youtube_id: "",
});

function openCreateModal(): void {
    form.reset();
    form.clearErrors();
    createModalOpen.value = true;
}

function closeCreateModal(): void {
    createModalOpen.value = false;
    form.clearErrors();
}

function submit(): void {
    form.post(route("admin.exercises.store"), {
        preserveScroll: true,
        onSuccess: closeCreateModal,
    });
}

function formatExerciseType(type: string): string {
    return type
        .split(/[_-]/)
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(" ");
}

function applyFilters(payload: IndexFilterPayload = {}): void {
    router.get(route("admin.exercises.view"), payload, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onStart: () => {
            isFiltering.value = true;
        },
        onFinish: () => {
            isFiltering.value = false;
        },
    });
}

function clearFilters(): void {
    applyFilters({});
}
</script>

<template>
    <div class="flex flex-col gap-6">
        <Head title="Exercises" />

        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-medium text-accent">Administration</p>
                <h1 class="mt-1 text-2xl font-semibold text-primary">Exercises</h1>
            </div>

            <ButtonPrimary type="button" @click="openCreateModal">Create exercise</ButtonPrimary>
        </header>

        <IndexFilters
            :fields="filterFields"
            :sorts="sortFields"
            :processing="isFiltering"
            @apply="applyFilters"
            @clear="clearFilters"
        />

        <UiTable :columns="columns" :rows="props.exercises.data" empty-message="No exercises found.">
            <template #cell-muscle_group="{ row }">
                <span>{{ row.muscle_group.name }}</span>
            </template>

            <template #cell-exercise_type="{ row }">
                {{ formatExerciseType(row.exercise_type) }}
            </template>

            <template #cell-user="{ row }">
                <span v-if="row.user">{{ row.user.name }}</span>
                <span v-else>System</span>
            </template>

            <template #cell-youtube_id="{ row }">
                <a
                    v-if="row.youtube_id"
                    :href="`https://www.youtube.com/watch?v=${row.youtube_id}`"
                    target="_blank"
                    rel="noreferrer"
                    class="inline-flex rounded-md border border-input-border bg-input p-2 text-primary transition hover:bg-layer-light"
                    aria-label="Open exercise video"
                >
                    <Icon icon="material-symbols:open-in-new" class="size-5" aria-hidden="true" />
                </a>
                <span v-else class="text-secondary">—</span>
            </template>
        </UiTable>

        <footer class="flex flex-col gap-3 text-sm text-secondary sm:flex-row sm:items-center sm:justify-between">
            <p>
                Showing {{ props.exercises.from ?? 0 }} to {{ props.exercises.to ?? 0 }} of
                {{ props.exercises.total }} exercises
            </p>

            <nav v-if="props.exercises.links.length > 3" aria-label="Exercise pagination" class="flex flex-wrap gap-2">
                <template v-for="link in props.exercises.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        class="rounded border border-layer-border px-3 py-2 transition hover:bg-layer-light"
                        :class="link.active ? 'bg-orange text-primary' : 'bg-layer text-secondary'"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="cursor-not-allowed rounded border border-layer-border px-3 py-2 opacity-40"
                        v-html="link.label"
                    />
                </template>
            </nav>
        </footer>

        <Modal :show="createModalOpen" max-width="md" @close="closeCreateModal">
            <form @submit.prevent="submit">
                <ModalHeader
                    title="Create exercise"
                    class="border-b border-layer-border p-4"
                    @close="closeCreateModal"
                />

                <div class="flex flex-col gap-4 p-4">
                    <div class="flex flex-col gap-2">
                        <InputLabel for="exercise-name" value="Name" />
                        <InputText
                            id="exercise-name"
                            v-model="form.name"
                            type="text"
                            minlength="1"
                            maxlength="255"
                            pattern="[A-Za-z\(\) \-]+"
                            autofocus
                            required
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <InputLabel for="exercise-muscle-group" value="Muscle group" />
                        <InputSelect
                            id="exercise-muscle-group"
                            v-model="form.muscle_group_id"
                            :options="muscleGroupOptions"
                            placeholder="Select a muscle group"
                            required
                        />
                        <InputError :message="form.errors.muscle_group_id" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <InputLabel for="exercise-type" value="Type" />
                        <InputSelect
                            id="exercise-type"
                            v-model="form.exercise_type"
                            :options="props.exerciseTypes"
                            placeholder="Select an exercise type"
                            required
                        />
                        <InputError :message="form.errors.exercise_type" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <InputLabel for="exercise-youtube-id" value="YouTube ID" />
                        <InputText
                            id="exercise-youtube-id"
                            v-model="form.youtube_id"
                            type="text"
                            maxlength="255"
                            placeholder="Optional"
                        />
                        <InputError :message="form.errors.youtube_id" />
                    </div>
                </div>

                <footer class="flex justify-end gap-3 border-t border-layer-border p-4">
                    <ButtonSecondary type="button" @click="closeCreateModal">Cancel</ButtonSecondary>
                    <ButtonPrimary type="submit" :disabled="form.processing">
                        {{ form.processing ? "Creating…" : "Create exercise" }}
                    </ButtonPrimary>
                </footer>
            </form>
        </Modal>
    </div>
</template>
