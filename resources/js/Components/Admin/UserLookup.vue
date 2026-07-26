<script setup lang="ts">
import { Icon } from "@iconify/vue";
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";

type LookupUser = {
    id: number;
    name: string;
    email: string;
};

type LookupResponse = {
    users: {
        data: LookupUser[];
        next_page_url: string | null;
    };
};

const props = withDefaults(
    defineProps<{
        id?: string;
        placeholder?: string;
        debounce?: number;
    }>(),
    {
        id: undefined,
        placeholder: "Any user",
        debounce: 300,
    }
);

const model = defineModel<string>({ default: "" });

const root = ref<HTMLElement | null>(null);
const searchInput = ref<HTMLInputElement | null>(null);
const search = ref("");
const users = ref<LookupUser[]>([]);
const selectedUser = ref<LookupUser | null>(null);
const nextPageUrl = ref<string | null>(null);
const isOpen = ref(false);
const isLoading = ref(false);
const error = ref("");

let searchTimer: number | undefined;
let requestController: AbortController | null = null;

const selectedLabel = computed(() => {
    if (selectedUser.value) {
        return `${selectedUser.value.name} — ${selectedUser.value.email}`;
    }

    return model.value ? `User #${model.value}` : props.placeholder;
});

function syncSelectedUser(): void {
    if (!model.value) {
        selectedUser.value = null;
        return;
    }

    const matchingUser = users.value.find((user) => String(user.id) === model.value);

    if (matchingUser) {
        selectedUser.value = matchingUser;
    } else if (String(selectedUser.value?.id) !== model.value) {
        selectedUser.value = null;
    }
}

async function fetchUsers(url: string, append = false): Promise<void> {
    requestController?.abort();
    const controller = new AbortController();
    requestController = controller;
    isLoading.value = true;
    error.value = "";

    try {
        const response = await window.axios.get<LookupResponse>(url, {
            params: append ? undefined : { search: search.value.trim() || undefined },
            signal: controller.signal,
        });

        users.value = append ? [...users.value, ...response.data.users.data] : response.data.users.data;
        nextPageUrl.value = response.data.users.next_page_url;
        syncSelectedUser();
    } catch {
        if (!controller.signal.aborted) {
            error.value = "Unable to load users.";
        }
    } finally {
        if (requestController === controller) {
            isLoading.value = false;
        }
    }
}

async function open(): Promise<void> {
    isOpen.value = true;

    if (users.value.length === 0) {
        await fetchUsers(route("admin.users.lookup"));
    }

    await nextTick();
    searchInput.value?.focus();
}

function close(): void {
    isOpen.value = false;
}

function selectUser(user: LookupUser): void {
    selectedUser.value = user;
    model.value = String(user.id);
    close();
}

function clearSelection(): void {
    selectedUser.value = null;
    model.value = "";
    close();
}

function loadMore(): void {
    if (nextPageUrl.value) {
        fetchUsers(nextPageUrl.value, true);
    }
}

function handleOutsideClick(event: PointerEvent): void {
    if (root.value && !root.value.contains(event.target as Node)) {
        close();
    }
}

watch(search, () => {
    window.clearTimeout(searchTimer);
    searchTimer = window.setTimeout(() => fetchUsers(route("admin.users.lookup")), props.debounce);
});

watch(model, syncSelectedUser);

onMounted(() => document.addEventListener("pointerdown", handleOutsideClick));

onBeforeUnmount(() => {
    window.clearTimeout(searchTimer);
    requestController?.abort();
    document.removeEventListener("pointerdown", handleOutsideClick);
});
</script>

<template>
    <div ref="root" class="relative">
        <button
            :id="id"
            type="button"
            class="flex w-full items-center justify-between gap-2 rounded-md border border-input-border bg-input px-3 py-2 text-left text-sm text-primary outline-none transition focus:border-accent focus:ring-2 focus:ring-accent focus:ring-opacity-40"
            aria-haspopup="listbox"
            :aria-expanded="isOpen"
            @click="isOpen ? close() : open()"
            @keydown.esc="close"
        >
            <span class="min-w-0 truncate">{{ selectedLabel }}</span>
            <Icon
                icon="material-symbols:keyboard-arrow-down-rounded"
                class="size-5 shrink-0 transition"
                :class="{ 'rotate-180': isOpen }"
                aria-hidden="true"
            />
        </button>

        <div
            v-if="isOpen"
            class="absolute z-30 mt-2 flex max-h-80 w-full min-w-72 flex-col overflow-hidden rounded-md border border-layer-border bg-layer shadow-xl"
        >
            <div class="border-b border-layer-border p-2">
                <input
                    ref="searchInput"
                    v-model="search"
                    type="search"
                    placeholder="Search name or email"
                    class="w-full rounded-md border border-input-border bg-input px-3 py-2 text-sm text-primary outline-none transition placeholder:text-helper focus:border-accent focus:ring-2 focus:ring-accent focus:ring-opacity-40"
                    @keydown.esc="close"
                />
            </div>

            <div role="listbox" class="scrollbar overflow-y-auto p-1">
                <button
                    type="button"
                    class="flex w-full px-3 py-2 text-left text-sm text-secondary transition hover:bg-layer-light hover:text-primary"
                    @click="clearSelection"
                >
                    {{ placeholder }}
                </button>

                <button
                    v-for="user in users"
                    :key="user.id"
                    type="button"
                    role="option"
                    :aria-selected="String(user.id) === model"
                    class="flex w-full flex-col gap-0.5 rounded px-3 py-2 text-left transition hover:bg-layer-light"
                    :class="{ 'bg-layer-light': String(user.id) === model }"
                    @click="selectUser(user)"
                >
                    <span class="text-sm font-medium text-primary">{{ user.name }}</span>
                    <span class="truncate text-xs text-secondary">{{ user.email }}</span>
                </button>

                <p v-if="isLoading && users.length === 0" class="px-3 py-4 text-center text-sm text-secondary">
                    Loading users…
                </p>
                <p v-else-if="error" class="text-red-500 px-3 py-4 text-center text-sm">{{ error }}</p>
                <p v-else-if="users.length === 0" class="px-3 py-4 text-center text-sm text-secondary">
                    No users found.
                </p>

                <button
                    v-if="nextPageUrl"
                    type="button"
                    class="w-full rounded px-3 py-2 text-sm font-medium text-accent transition hover:bg-layer-light disabled:opacity-50"
                    :disabled="isLoading"
                    @click="loadMore"
                >
                    {{ isLoading ? "Loading…" : "Load more" }}
                </button>
            </div>
        </div>
    </div>
</template>
