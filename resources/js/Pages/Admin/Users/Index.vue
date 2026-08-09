<script setup lang="ts">
import ButtonPrimary from "@/Components/Button/Primary.vue";
import ButtonSecondary from "@/Components/Button/Secondary.vue";
import InputError from "@/Components/Input/InputError.vue";
import InputLabel from "@/Components/Input/InputLabel.vue";
import InputText from "@/Components/Input/Text.vue";
import Modal from "@/Components/Modal.vue";
import ModalHeader from "@/Components/Modals/Header.vue";
import IndexFilters, {
    type IndexFilterField,
    type IndexFilterPayload,
    type IndexSortField,
} from "@/Components/Ui/IndexFilters.vue";
import UiTable, { type TableColumn } from "@/Components/Ui/Table.vue";
import type { User } from "@/types";
import { Icon } from "@iconify/vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type PaginatedUsers = {
    data: User[];
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
};

const props = defineProps<{
    users: PaginatedUsers;
}>();

const columns: TableColumn[] = [
    { key: "id", label: "ID" },
    { key: "name", label: "Name" },
    { key: "email", label: "Email" },
    { key: "email_verified_at", label: "Verified" },
    { key: "actions", label: "", headerClass: "text-right", cellClass: "text-right" },
];

const filterFields = [
    {
        key: "id",
        label: "ID",
        type: "number",
        placeholder: "User ID",
        min: 1,
        step: 1,
    },
    {
        key: "name",
        label: "Name",
        type: "text",
        placeholder: "Name",
        pattern: "[A-Za-z]+",
    },
    {
        key: "email",
        label: "Email",
        type: "text",
        inputmode: "email",
        placeholder: "Email address",
    },
    {
        key: "verified",
        label: "Verification",
        type: "select",
        options: [
            { value: "", label: "Any status" },
            { value: "1", label: "Verified" },
            { value: "0", label: "Unverified" },
        ],
    },
    {
        key: "created_at",
        label: "Created date",
        type: "date",
    },
] as const satisfies readonly IndexFilterField[];

const sortFields = [
    { value: "id", label: "ID" },
    { value: "name", label: "Name" },
    { value: "email", label: "Email" },
    { value: "verified", label: "Verified" },
    { value: "created_at", label: "Created date" },
] as const satisfies readonly IndexSortField[];

const isFiltering = ref(false);

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

function applyFilters(payload: IndexFilterPayload = {}): void {
    router.get(route("admin.users.view"), payload, {
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
        <Head title="Users" />

        <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-medium text-accent">Administration</p>
                <h1 class="mt-1 text-2xl font-semibold text-primary">Users</h1>
            </div>

            <ButtonPrimary type="button" @click="openCreateModal">Create user</ButtonPrimary>
        </header>

        <IndexFilters
            :fields="filterFields"
            :sorts="sortFields"
            :processing="isFiltering"
            @apply="applyFilters"
            @clear="clearFilters"
        />

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
            <p>Showing {{ props.users.from ?? 0 }} to {{ props.users.to ?? 0 }} of {{ props.users.total }} users</p>

            <nav v-if="props.users.links.length > 3" aria-label="User pagination" class="flex flex-wrap gap-2">
                <template v-for="link in props.users.links" :key="link.label">
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
