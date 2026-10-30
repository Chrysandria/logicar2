import { defineStore } from "pinia";
import { ref } from "vue";

function getInitialTheme() {
    const savedTheme = localStorage.getItem("logicar-theme");

    if (savedTheme === "light" || savedTheme === "dark") {
        return savedTheme;
    }

    return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
}

export const useThemeStore = defineStore("theme", () => {
    const systemTheme = window.matchMedia("(prefers-color-scheme: dark)");
    const theme = ref(getInitialTheme());

    function applyTheme() {
        document.documentElement.setAttribute("data-bs-theme", theme.value);
        document.documentElement.setAttribute("data-swal2-theme", theme.value);
    }

    function setTheme(nextTheme) {
        theme.value = nextTheme;
        localStorage.setItem("logicar-theme", nextTheme);
        applyTheme();
    }

    systemTheme.addEventListener("change", (event) => {
        const savedTheme = localStorage.getItem("logicar-theme");

        if (savedTheme === "light" || savedTheme === "dark") {
            return;
        }

        theme.value = event.matches ? "dark" : "light";
        applyTheme();
    });

    function toggleTheme() {
        setTheme(theme.value === "dark" ? "light" : "dark");
    }

    applyTheme();

    return { theme, toggleTheme };
});
