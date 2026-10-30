<template>
    <div class="min-vh-100 d-flex flex-column">
        <header class="public-header shadow-lg px-4">
            <nav class="navbar container navbar-expand-lg navbar-light">
                <router-link :to="{ name: 'public.index' }" class="navbar-brand fs-3 fw-semibold text-decoration-none">
                    <Logo/>
                </router-link>
                <button
                    class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarNav"
                    aria-controls="navbarNav"
                    aria-expanded="false"
                    aria-label="Ouvrir la navigation"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>
                <section class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav gap-2 ms-auto">
                        <li class="nav-item">
                            <router-link
                                :to="{
                                    name: 'public.index',
                                }"
                                class="btn btn-outline-primary"
                            >
                                <i class="bi bi-house me-2"></i>
                                Accueil
                            </router-link>
                        </li>
                        <li class="nav-item">
                            <router-link
                                :to="{
                                    name: 'public.login',
                                }"
                                class="btn btn-primary"
                            >
                                <i class="bi bi-box-arrow-in-left me-2"></i>
                                Se connecter
                            </router-link>
                        </li>
                    </ul>
                </section>
            </nav>
        </header>
        <router-view />
        <footer class="public-footer bg-white border-top p-4 text-center text-muted small">
            © Logicar {{ new Date().getFullYear() }}
        </footer>
        <ThemeToggle class="public-theme-toggle m-4" />
    </div>
</template>

<script setup>
import Logo from '../components/logo.vue';
import ThemeToggle from '../components/theme-toggle.vue';
</script>
