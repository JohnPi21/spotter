function get<T = any>(haystack: T, path: string, defaultValue?: any): any {
    const levels = path.includes(".") ? path.split(".") : [path];

    let obj: any = haystack;

    for (const level of levels) {
        if (obj == null || !(level in obj)) {
            return defaultValue ?? null;
        }
        obj = obj[level];
    }

    return obj;
}

export const useArray = { get };
