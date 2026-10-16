<template>
    <router-view/>
</template>

<script setup>
import { onMounted } from 'vue';
import { useLoading, close } from '../services/alert';
import { useStore } from '../stores/authStore';
const store = useStore();

onMounted(async() => {
    await store.getConnected();
})
</script>
