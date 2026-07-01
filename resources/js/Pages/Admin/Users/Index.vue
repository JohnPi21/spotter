<script setup lang="ts">
import ButtonPrimary from "@/Components/Button/Primary.vue";
import ButtonSecondary from "@/Components/Button/Secondary.vue";
import InputError from "@/Components/Input/InputError.vue";
import InputLabel from "@/Components/Input/InputLabel.vue";
import InputSelect from "@/Components/Input/Select.vue";
import InputText from "@/Components/Input/Text.vue";
import Modal from "@/Components/Modal.vue";
import ModalHeader from "@/Components/Modals/Header.vue";
import UiTable, { type TableColumn } from "@/Components/Ui/Table.vue";
import type { User } from "@/types";
import { Icon } from "@iconify/vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { computed, onBeforeUnmount, ref } from "vue";

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedUsers = {
    data: User[];
    meta: {
        links: PaginationLink[];
        from: number | null;
        to: number | null;
        total: number;
    };
};

type UserFilters = {
    search?: string;
    demo?: boolean;
    sort?: "id" | "name" | "email";
    direction?: "asc" | "desc";
};

const props = defineProps<{
    users: PaginatedUsers;
    filters?: UserFilters;
}>();

const columns: TableColumn[] = [
    { key: "id", label: "ID" },
    { key: "name", label: "Name" },
    { key: "email", label: "Email" },
    { key: "email_verified_at", label: "Verified" },
    { key: "actions", label: "", headerClass: "text-right", cellClass: "text-right" },
];

const sortOptions = [
    { value: "id_asc", label: "ID: ascending" },
    { value: "id_desc", label: "ID: descending" },
    { value: "name_asc", label: "Name: A–Z" },
    { value: "name_desc", label: "Name: Z–A" },
    { value: "email_asc", label: "Email: A–Z" },
    { value: "email_desc", label: "Email: Z–A" },
] as const;

const search = ref(props.filters?.search ?? "");
const demoOnly = ref(props.filters?.demo ?? false);
const sort = ref(`${props.filters?.sort ?? "id"}_${props.filters?.direction ?? "asc"}`);
let searchTimer: number | undefined;

const modalOpen = ref(false);
const selectedUser = ref<User | null>(null);
const modalTitle = computed(() => (selectedUser.value ? "Edit user" : "Create user"));

const form = useForm({
    name: "",
    email: "",
});

function openCreateModal(): void {
    selectedUser.value = null;
    form.reset();
    form.clearErrors();
    modalOpen.value = true;
}

function openEditModal(user: User): void {
    selectedUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.clearErrors();
    modalOpen.value = true;
}

function closeModal(): void {
    modalOpen.value = false;
    form.clearErrors();
}

function submit(): void {
    const options = {
        preserveScroll: true,
        onSuccess: closeModal,
    };

    if (selectedUser.value) {
        form.put(`/panel/users/${selectedUser.value.id}`, options);
        return;
    }

    form.post("/panel/users", options);
}

function isVerified(user: User): boolean {
    return Boolean(user.email_verified_at);
}

function applyFilters(): void {
    const [sortField, sortDirection] = sort.value.split("_") as [UserFilters["sort"], UserFilters["direction"]];

    router.get(
        route("admin.users.view"),
        {
            search: search.value || undefined,
            demo: demoOnly.value ? 1 : undefined,
            sort: sortField,
            direction: sortDirection,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    );
}

function queueSearch(): void {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(applyFilters, 300);
}

function toggleDemoUsers(): void {
    demoOnly.value = !demoOnly.value;
    applyFilters();
}

onBeforeUnmount(() => window.clearTimeout(searchTimer));
</script>

<template>
    <div class="flex flex-col gap-6">
        <Head title="Users" />

        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-medium text-accent">Administration</p>
                <h1 class="mt-1 text-2xl font-semibold text-primary">Users</h1>
            </div>

            <ButtonPrimary type="button" @click="openCreateModal">Create user</ButtonPrimary>
        </header>

        <section
            class="flex flex-col gap-3 rounded-lg border border-layer-border bg-layer p-4 lg:flex-row lg:items-end"
        >
            <div class="flex min-w-0 flex-1 flex-col gap-2">
                <InputLabel for="user-search" value="Search users" />
                <div class="relative">
                    <Icon
                        icon="material-symbols:search"
                        class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-helper"
                        aria-hidden="true"
                    />
                    <InputText
                        id="user-search"
                        v-model="search"
                        type="search"
                        class="w-full pl-10"
                        placeholder="Search by name or email"
                        @input="queueSearch"
                    />
                </div>
            </div>

            <button
                type="button"
                class="inline-flex items-center justify-center gap-2 rounded-md border px-3 py-2 text-sm font-medium transition"
                :class="
                    demoOnly
                        ? 'border-border-orange bg-orange text-primary'
                        : 'border-input-border bg-input text-secondary hover:bg-layer-light hover:text-primary'
                "
                :aria-pressed="demoOnly"
                @click="toggleDemoUsers"
            >
                <Icon icon="material-symbols:science-outline" class="size-5" aria-hidden="true" />
                Demo users
            </button>

            <div class="flex flex-col gap-2 lg:w-52">
                <InputLabel for="user-sort" value="Sort by" />
                <InputSelect id="user-sort" v-model="sort" :options="sortOptions" @change="applyFilters" />
            </div>
        </section>

        <UiTable :columns="columns" :rows="props.users.data">
            <template #cell-email_verified_at="{ row }">
                <span>
                    <Icon
                        :icon="
                            isVerified(row)
                                ? 'material-symbols:check-circle-outline'
                                : 'material-symbols:cancel-outline'
                        "
                        class="size-5"
                        :class="isVerified(row) ? 'text-text-green' : 'text-text-red'"
                        aria-hidden="true"
                    />
                    <span class="sr-only">{{ isVerified(row) ? "Verified" : "Unverified" }}</span>
                </span>
            </template>

            <template #cell-actions="{ row }">
                <ButtonSecondary type="button" class="p-2" aria-label="Edit user" @click="openEditModal(row)">
                    <Icon icon="material-symbols:edit-outline" class="size-5" aria-hidden="true" />
                </ButtonSecondary>
            </template>
        </UiTable>

        <footer class="flex flex-col gap-3 text-sm text-secondary sm:flex-row sm:items-center sm:justify-between">
            <p>
                Showing {{ props.users.meta.from ?? 0 }} to {{ props.users.meta.to ?? 0 }} of
                {{ props.users.meta.total }} users
            </p>

            <nav v-if="props.users.meta.links.length > 3" aria-label="User pagination" class="flex flex-wrap gap-2">
                <template v-for="link in props.users.meta.links" :key="link.label">
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

        <Modal :show="modalOpen" max-width="md" @close="closeModal">
            <form @submit.prevent="submit">
                <ModalHeader :title="modalTitle" class="border-b border-layer-border p-4" @close="closeModal" />

                <div class="flex flex-col gap-4 p-4">
                    <div class="flex flex-col gap-2">
                        <InputLabel for="user-name" value="Name" />
                        <InputText id="user-name" v-model="form.name" type="text" autocomplete="name" autofocus />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="flex flex-col gap-2">
                        <InputLabel for="user-email" value="Email" />
                        <InputText id="user-email" v-model="form.email" type="email" autocomplete="email" />
                        <InputError :message="form.errors.email" />
                    </div>
                </div>

                <footer class="flex justify-end gap-3 border-t border-layer-border p-4">
                    <ButtonSecondary type="button" @click="closeModal">Cancel</ButtonSecondary>
                    <ButtonPrimary type="submit" :disabled="form.processing">
                        {{ selectedUser ? "Save changes" : "Create user" }}
                    </ButtonPrimary>
                </footer>
            </form>
        </Modal>
    </div>
</template>
