import { onBeforeUnmount, onMounted } from "vue";

function onClickOutside(refElement, callback, excludes = []) {
    if (!refElement) return;

    const handleClickOutside = (event) => {
        let target = event.target;

        // Check if refElement is a single HTML element
        if (refElement.value && refElement.value instanceof HTMLElement) {
            if (refElement.value && refElement.value.contains(target)) {
                return;
            }

            if (excludes.length) {
                if (excludes.indexOf(target.className) !== -1) return;
            }

            if (typeof callback === "function") {
                callback();
            }

            // Check if refElement is an array of HTML elements (v-for case)
        } else if (Array.isArray(refElement.value)) {
            if (!refElement.value.some((el) => el.contains(target))) {
                if (typeof callback === "function") {
                    callback();
                }
            }
        }
    };

    onMounted(() => {
        window.addEventListener("click", handleClickOutside);
        window.addEventListener("touchstart", handleClickOutside);
    });

    onBeforeUnmount(() => {
        window.removeEventListener("click", handleClickOutside);
        window.removeEventListener("touchstart", handleClickOutside);
    });
}

export const useUtils = {
    onClickOutside,
};
