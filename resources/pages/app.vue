<template>
    <div v-if="initialized">
        <router-view/>
    </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { useLoading, close } from "../services/alert";
import { useRouter } from "vue-router";
import { useStore } from "../stores/authStore";
import { useThemeStore } from "../stores/themeStore.js";

const store = useStore();
useThemeStore();
const router = useRouter();
const initialized = ref(false);

onMounted(async () => {
    useLoading();
    await store.getConnected();
    await router.isReady();
    initialized.value = true;
    close();
});
</script>
