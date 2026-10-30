<template>
    <DispatchBoard :date="date" />
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import DispatchBoard from "../../components/dispatch-board.vue";

const route = useRoute();
const date = computed(() => String(route.params.date || ""));
</script>
