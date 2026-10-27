<template>
    <div class="min-vh-100 d-flex flex-column">
        <header class="shadow-lg d-lg-none">
            <nav class="navbar navbar-light container px-2">
                <a href="#" class="navbar-brand fs-3 fw-semibold">
                    <logo />
                </a>
                <button
                    class="navbar-toggler"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#sidebar"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>
            </nav>
        </header>
        <main class="d-flex flex-grow-1">
            <aside
                class="offcanvas-lg offcanvas-end bg-dark text-light d-flex flex-column"
                tabindex="-1"
                id="sidebar"
                aria-labelledby="sidebarLabel"
                style="width: 260px"
            >
                <div
                    class="offcanvas-header border-bottom border-secondary d-lg-flex"
                >
                    <h5
                        class="offcanvas-title fs-3 fw-semibold"
                        id="sidebarLabel"
                    >
                        <logo />
                    </h5>
                    <button
                        class="btn-close btn-close-white d-lg-none"
                        data-bs-dismiss="offcanvas"
                        data-bs-target="#sidebar"
                    ></button>
                </div>
                <div
                    class="offcanvas-body flex-grow-1 text-white d-flex flex-column p-0"
                >
                    <div
                        class="py-2 text-center border-bottom border-secondary"
                    >
                        <h3>{{ user.name }}</h3>
                        <p class="mb-0 small">{{ user.service }}</p>
                    </div>
                    <nav class="p-2 py-5 d-flex flex-column gap-2 flex-grow-1">
                        <template v-if="user.service === 'SNR'">
                            <router-link
                                :to="{ name: 'snr.index' }"
                                class="btn btn-primary mb-5"
                                >Aujourd'hui</router-link
                            >
                            <router-link
                                v-for="day in nextWeek"
                                :to="{
                                    name: 'snr.treat',
                                    params: {
                                        date: day.date,
                                    },
                                }"
                                class="btn btn-primary"
                                >{{ day.jour }}</router-link
                            >
                        </template>
                        <template v-else>
                            <router-link
                                :to="{ name: 'services.index' }"
                                class="btn btn-primary mb-5"
                                >Aujourd'hui</router-link
                            >
                            <router-link
                                v-for="day in nextWeek"
                                :to="{
                                    name: 'services.edit',
                                    params: {
                                        date: day.date,
                                    },
                                }"
                                class="btn btn-primary"
                                >{{ day.jour }}</router-link
                            >
                        </template>
                    </nav>
                    <div class="p-2 d-flex flex-column gap-2 mb-5">
                        <template v-if="user.service === 'SNR'">
                            <router-link
                                :to="{ name: 'snr.users' }"
                                class="btn btn-secondary"
                            >
                                <i class="bi bi-people"></i>
                                Utilisateurs
                            </router-link>
                            <router-link
                                :to="{ name: 'snr.cars' }"
                                class="btn btn-secondary"
                            >
                                <i class="bi bi-bus-front me-2"></i>
                                Véhicules
                            </router-link>
                        </template>
                        <button v-else class="btn btn-secondary">
                            <i class="bi bi-search me-2"></i>
                            Faire une recherche
                        </button>
                    </div>
                </div>
                <div
                    class="offcanvas-footer border-top border-secondary p-2 text-center d-flex flex-column gap-2"
                >
                    <router-link
                        :to="{ name: 'snr.account' }"
                        class="btn btn-secondary"
                        v-if="user.service === 'SNR'"
                    >
                        <i class="bi bi-person-circle me-2"></i>
                        Mon compte
                    </router-link>
                    <router-link
                        :to="{ name: 'services.account' }"
                        class="btn btn-secondary"
                        v-else
                    >
                        <i class="bi bi-person-circle me-2"></i>
                        Mon compte
                    </router-link>
                    <logout />
                    <p class="mt-3 text-secondary">
                        © LogiCar {{ new Date().getFullYear() }}
                    </p>
                </div>
            </aside>
            <main class="flex-grow-1">
                <router-view></router-view>
            </main>
        </main>
        <footer class="p-2 shadow-lg d-lg-none text-muted text-center small">
            © LogiCar {{ new Date().getFullYear() }}
        </footer>
    </div>
</template>

<script setup>
import logo from "../components/logo.vue";
import logout from "../components/logout.vue";
import { nextWeek } from "../services/dayManager.js";
import { useStore } from "../stores/authStore.js";
import { storeToRefs } from "pinia";

const store = useStore();
const { user } = storeToRefs(store);
</script>
