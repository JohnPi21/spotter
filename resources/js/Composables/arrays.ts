function get(haystack: unknown, path: string, defaultValue?: unknown): unknown {
    const levels = path.includes(".") ? path.split(".") : [path];

    let obj: unknown = haystack;

    for (const level of levels) {
        if (
            obj === null ||
            (typeof obj !== "object" && typeof obj !== "function") ||
            !(level in obj)
        ) {
            return defaultValue ?? null;
        }
        obj = (obj as Record<string, unknown>)[level];
    }

    return obj;
}

export const useArray = { get };
