import { defineStore } from "pinia";
import { ref } from "vue";

export type ToastLevel = "error" | "success" | "warning" | "info";

export type ToastMessage = {
    level: ToastLevel;
    message: string;
    title?: string;
    label?: string;
};

export const useToastStore = defineStore("toast", () => {
    const current = ref<ToastMessage | null>(null);

    function show(toast: ToastMessage): void {
        current.value = toast;
    }

    function success(message: string): void {
        show({ level: "success", message });
    }

    function error(message: string): void {
        show({ level: "error", message });
    }

    function warning(message: string): void {
        show({ level: "warning", message });
    }

    function info(message: string): void {
        show({ level: "info", message });
    }

    function dismiss(): void {
        current.value = null;
    }

    return {
        current,
        show,
        success,
        error,
        warning,
        info,
        dismiss,
    };
});
