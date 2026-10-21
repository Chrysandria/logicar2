<template>
    <div v-if="router.isReady()">
        <router-view/>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useLoading, close } from '../services/alert';
import { useRouter } from 'vue-router';
import { useStore } from '../stores/authStore';

const store = useStore();
const router = useRouter();

onMounted(async() => {
    await store.getConnected();
    useLoading();
    await router.isReady();
    close();
})
</script>
