import resolveConfig from "tailwindcss/resolveConfig";
import tailwindConfig from "../../../tailwind.config";

const fullConfig = resolveConfig(tailwindConfig);
const theme = fullConfig.theme;

export function useTailwindTheme() {
    return theme;
}

function withOpacity(hex, opacity = 0.2) {
    const alpha = Math.round(opacity * 255)
        .toString(16)
        .padStart(2, "0");
    return `${hex}${alpha}`;
}

export function useTailwindColors() {
    return {
        accent: theme.colors.accent,
        amber: withOpacity(theme.colors.accent, 0.2),
        danger: theme.colors.red,
        success: theme.colors.green,
    };
}
