<template>
    <button
        class="btn btn-outline-secondary theme-toggle d-flex align-items-center gap-2"
        type="button"
        :aria-label="`Activer le thème ${nextThemeLabel}`"
        :title="`Activer le thème ${nextThemeLabel}`"
        @click="themeStore.toggleTheme"
    >
        <span>{{ nextThemeLabel }}</span>
        <i class="bi" :class="themeStore.theme === 'dark' ? 'bi-sun' : 'bi-moon-fill'"></i>
    </button>
</template>

<script setup>
import { computed } from "vue";
import { useThemeStore } from "../stores/themeStore.js";

const themeStore = useThemeStore();
const nextThemeLabel = computed(() => themeStore.theme === "dark" ? "clair" : "sombre");
</script>
