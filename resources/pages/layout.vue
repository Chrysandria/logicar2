<template>
    <div class="min-vh-100 d-flex flex-column">
        <header class="mobile-header d-lg-none">
            <nav class="navbar container-fluid px-3">
                <router-link :to="homeLink" class="navbar-brand fs-3 fw-semibold">
                    <Logo />
                </router-link>
                <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Ouvrir le menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </nav>
        </header>
        <div class="d-flex flex-grow-1">
            <aside v-if="user" id="sidebar" class="offcanvas-lg offcanvas-end app-sidebar text-light d-flex flex-column bg-dark" tabindex="-1" aria-labelledby="sidebarLabel">
                <div class="offcanvas-header border-bottom border-secondary px-4 py-4">
                    <h1 class="offcanvas-title fs-3 fw-semibold mb-0" id="sidebarLabel"><Logo /></h1>
                    <button class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Fermer le menu"></button>
                </div>
                <div class="offcanvas-body p-3 d-flex flex-column">
                    <div class="user-summary rounded-3 p-3 mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <span class="avatar-circle"><i class="bi bi-person-fill"></i></span>
                            <div class="min-w-0">
                                <div class="fw-semibold text-truncate">{{ user.name }}</div>
                                <span class="small text-white-50">{{ user.service }}</span>
                            </div>
                        </div>
                    </div>
                    <nav class="p-2 py-5 d-flex flex-column gap-2 flex-grow-1">
                        <template v-if="user.service === 'SNR'">
                            <router-link
                                :to="{ name: 'snr.index' }"
                                class="sidebar-link mb-3"
                                exact-active-class="active"
                                >Aujourd'hui</router-link
                            >
                            <router-link
                                v-for="day in nextWeek"
                                :key="day.date"
                                :to="{
                                    name: 'snr.treat',
                                    params: {
                                        date: day.date,
                                    },
                                }"
                                class="sidebar-link"
                                exact-active-class="active"
                                >{{ day.jour }}</router-link
                            >
                        </template>
                        <template v-else>
                            <router-link
                                :to="{ name: 'services.index' }"
                                class="sidebar-link mb-3"
                                exact-active-class="active"
                                >Aujourd'hui</router-link
                            >
                            <router-link
                                v-for="day in nextWeek"
                                :key="day.date"
                                :to="{
                                    name: 'services.edit',
                                    params: {
                                        date: day.date,
                                    },
                                }"
                                class="sidebar-link"
                                exact-active-class="active"
                                >{{ day.jour }}</router-link
                            >
                            <router-link
                                :to="{ name: 'services.search' }"
                                class="sidebar-link mt-3"
                                exact-active-class="active"
                            >
                                <i class="bi bi-search"></i>
                                Recherche
                            </router-link>
                        </template>
                    </nav>
                    <div v-if="user.service === 'SNR'" class="p-2 d-flex flex-column gap-2 mb-3">
                        <router-link
                            :to="{ name: 'snr.users' }"
                            class="sidebar-link"
                            exact-active-class="active"
                        >
                            <i class="bi bi-people"></i>
                            Utilisateurs
                        </router-link>
                        <router-link
                            :to="{ name: 'snr.cars' }"
                            class="sidebar-link"
                            exact-active-class="active"
                        >
                            <i class="bi bi-bus-front me-2"></i>
                            Véhicules
                        </router-link>
                        <router-link
                            :to="{ name: 'snr.trips' }"
                            class="sidebar-link"
                            exact-active-class="active"
                        >
                            <i class="bi bi-signpost-2"></i>
                            Trajets
                        </router-link>
                    </div>
                    <div class="p-2 d-flex flex-column gap-2 mt-auto">
                        <router-link
                            :to="{ name: accountRoute }"
                            class="sidebar-link"
                            exact-active-class="active"
                        >
                            <i class="bi bi-person-circle"></i>
                            Mon compte
                        </router-link>
                        <Logout />
                    </div>
                </div>
            </aside>
            <main class="app-content flex-grow-1">
                <router-view v-slot="{ Component }">
                    <KeepAlive>
                        <component :is="Component" />
                    </KeepAlive>
                </router-view>
            </main>
        </div>
        <footer class="d-lg-none py-2 text-muted text-center small">© LogiCar {{ year }}</footer>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { storeToRefs } from "pinia";
import Logo from "../components/logo.vue";
import Logout from "../components/logout.vue";
import { nextWeek } from "../services/dayManager.js";
import { useStore } from "../stores/authStore.js";

const store = useStore();
const { user } = storeToRefs(store);
const homeLink = computed(() => ({
    name: user.value?.service === "SNR" ? "snr.index" : "services.index",
}));
const accountRoute = computed(() => user.value?.service === "SNR" ? "snr.account" : "services.account");
const year = new Date().getFullYear();
</script>
