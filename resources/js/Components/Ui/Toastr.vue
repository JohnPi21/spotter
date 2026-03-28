<template>
    <div class="pointer-events-none fixed inset-0 z-50 flex items-start justify-center px-4 py-4 sm:justify-end sm:p-6">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="translate-y-2 opacity-0 sm:translate-x-2 sm:translate-y-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-2 opacity-0 sm:translate-x-2 sm:translate-y-0"
        >
            <div
                v-if="activeToast"
                class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-xl border border-layer-border bg-layer shadow-[0_18px_45px_rgba(0,0,0,0.28)] ring-1 ring-black/5"
                role="alert"
                aria-live="polite"
            >
                <div class="flex items-start gap-3 p-4">
                    <div
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-main-border bg-input"
                        :class="activeToast.iconTextClass"
                    >
                        <Icon :icon="activeToast.icon" width="16" />
                    </div>

                    <div class="min-w-0 flex-1 pt-0.5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-primary">{{ activeToast.title }}</p>
                                <p class="mt-1 break-words text-sm leading-5 text-secondary">{{ activeToast.message }}</p>
                            </div>

                            <button
                                type="button"
                                :aria-label="activeToast.dismissLabel"
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-helper transition hover:bg-layer-light hover:text-primary"
                                @click="dismiss"
                            >
                                <Icon icon="mdi:close" width="18" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script setup lang="ts">
import type { PageProps } from "@/types";
import { Icon } from "@iconify/vue";
import { usePage } from "@inertiajs/vue3";
import { onBeforeUnmount, ref, watch } from "vue";

type ToastLevel = "error" | "success" | "warning" | "info";

type ToastPayload = {
    level: ToastLevel;
    label: string;
    title: string;
    message: string;
    accentClass: string;
    iconTextClass: string;
    icon: string;
    dismissLabel: string;
};

type CustomFlashPayload = {
    message: string;
    level?: ToastLevel;
    title?: string;
    label?: string;
};

const page = usePage<PageProps>();
const activeToast = ref<ToastPayload | null>(null);

let dismissTimer: number | null = null;

function clearDismissTimer(): void {
    if (dismissTimer === null) {
        return;
    }

    window.clearTimeout(dismissTimer);
    dismissTimer = null;
}

function dismiss(): void {
    clearDismissTimer();
    activeToast.value = null;
}

function buildToast(level: ToastLevel, message: string, overrides: Partial<ToastPayload> = {}): ToastPayload {
    const defaults: Record<ToastLevel, Omit<ToastPayload, "message">> = {
        error: {
            level: "error",
            label: "Error",
            title: "Couldn’t finish that request",
            accentClass: "bg-red",
            iconTextClass: "text-red",
            icon: "mdi:alert-circle-outline",
            dismissLabel: "Dismiss error",
        },
        success: {
            level: "success",
            label: "Success",
            title: "Done",
            accentClass: "bg-green",
            iconTextClass: "text-green",
            icon: "mdi:check-circle-outline",
            dismissLabel: "Dismiss success message",
        },
        warning: {
            level: "warning",
            label: "Warning",
            title: "Check this before continuing",
            accentClass: "bg-orange",
            iconTextClass: "text-orange",
            icon: "mdi:alert-outline",
            dismissLabel: "Dismiss warning",
        },
        info: {
            level: "info",
            label: "Info",
            title: "Update",
            accentClass: "bg-accent",
            iconTextClass: "text-accent",
            icon: "mdi:information-outline",
            dismissLabel: "Dismiss notification",
        },
    };

    return {
        ...defaults[level],
        message,
        ...overrides,
    };
}

function resolveCustomFlashPayload(payload: unknown): ToastPayload | null {
    if (typeof payload === "string") {
        return buildToast("info", payload);
    }

    if (!payload || typeof payload !== "object") {
        return null;
    }

    const candidate = payload as Partial<CustomFlashPayload>;

    if (typeof candidate.message !== "string") {
        return null;
    }

    const level = candidate.level ?? "info";

    return buildToast(level, candidate.message, {
        label: candidate.label,
        title: candidate.title,
    });
}

function resolveToast(): ToastPayload | null {
    const errorMessage = page.props.errors?.error;
    const flash = page.props.flash;

    if (typeof errorMessage === "string") {
        return buildToast("error", errorMessage);
    }

    if (typeof flash?.error === "string") {
        return buildToast("error", flash.error);
    }

    if (typeof flash?.warning === "string") {
        return buildToast("warning", flash.warning);
    }

    if (typeof flash?.success === "string") {
        return buildToast("success", flash.success);
    }

    if (typeof flash?.info === "string") {
        return buildToast("info", flash.info);
    }

    return resolveCustomFlashPayload(flash?.custom);
}

function show(toast: ToastPayload): void {
    activeToast.value = toast;
    clearDismissTimer();

    dismissTimer = window.setTimeout(() => {
        activeToast.value = null;
        dismissTimer = null;
    }, 4500);
}

watch(
    () => [page.url, page.props.flash, page.props.errors],
    () => {
        const toast = resolveToast();

        if (!toast) {
            dismiss();

            return;
        }

        show(toast);
    },
    { deep: true, immediate: true },
);

onBeforeUnmount(() => {
    clearDismissTimer();
});
</script>
