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
            <aside v-if="user" id="sidebar" class="offcanvas-lg offcanvas-start app-sidebar text-light d-flex flex-column" tabindex="-1" aria-labelledby="sidebarLabel">
                <div class="offcanvas-header border-bottom border-secondary px-4 py-4">
                    <h1 class="offcanvas-title fs-3 fw-semibold mb-0" id="sidebarLabel"><Logo /></h1>
                    <button class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" aria-label="Fermer le menu"></button>
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
                    <div class="small text-uppercase text-white-50 fw-semibold px-2 mb-2">Planning</div>
                    <nav class="d-grid gap-2">
                        <router-link :to="homeLink" class="sidebar-link" active-class="active">
                            <i class="bi bi-calendar-day"></i><span>Aujourd'hui</span>
                        </router-link>
                        <router-link v-for="day in nextWeek" :key="day.date" :to="dayLink(day.date)" class="sidebar-link" active-class="active">
                            <i class="bi bi-calendar2-week"></i><span>{{ day.jour }}</span>
                        </router-link>
                    </nav>
                    <div v-if="isSnr" class="mt-4">
                        <div class="small text-uppercase text-white-50 fw-semibold px-2 mb-2">Administration</div>
                        <nav class="d-grid gap-2">
                            <router-link :to="{ name: 'snr.users' }" class="sidebar-link" active-class="active"><i class="bi bi-people"></i><span>Équipe</span></router-link>
                            <router-link :to="{ name: 'snr.cars' }" class="sidebar-link" active-class="active"><i class="bi bi-bus-front"></i><span>Véhicules</span></router-link>
                        </nav>
                    </div>
                    <div class="mt-auto pt-4 d-grid gap-2">
                        <router-link :to="accountLink" class="sidebar-link" active-class="active"><i class="bi bi-person-circle"></i><span>Mon compte</span></router-link>
                        <Logout />
                        <p class="text-white-50 small text-center mt-2 mb-0">© LogiCar {{ year }}</p>
                    </div>
                </div>
            </aside>
            <main class="app-content flex-grow-1">
                <router-view />
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

const { user } = storeToRefs(useStore());
const year = new Date().getFullYear();
const isSnr = computed(() => user.value?.service === "SNR");
const homeLink = computed(() => ({ name: isSnr.value ? "snr.index" : "services.index", params: isSnr.value ? {} : { service: user.value?.service } }));
const accountLink = computed(() => ({ name: isSnr.value ? "snr.account" : "services.account", params: isSnr.value ? {} : { service: user.value?.service } }));

function dayLink(date) {
    return {
        name: isSnr.value ? "snr.treat" : "services.edit",
        params: isSnr.value ? { date } : { service: user.value?.service, date },
    };
}
</script>
