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

    function toggleTheme() {
        setTheme(theme.value === "dark" ? "light" : "dark");
    }

    applyTheme();

    return { theme, toggleTheme };
});
