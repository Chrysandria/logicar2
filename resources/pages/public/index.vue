<template>
    <main class="flex-grow-1">
        <section class="container py-5">
            <div class="row align-items-center g-5 py-lg-5">
                <div class="col-lg-6">
                    <span class="badge rounded-pill text-bg-primary-subtle text-primary px-3 py-2 mb-3">
                        <i class="bi bi-geo-alt me-1"></i> Mobilité, simplement
                    </span>
                    <h1 class="display-4 fw-bold lh-sm mb-3">Le transport de vos équipes, <span class="text-primary">mieux organisé.</span></h1>
                    <p class="lead text-secondary mb-4">LogiCar accompagne les équipes dans la préparation des plannings, le suivi des départs et la gestion de la flotte.</p>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <router-link :to="{ name: 'public.login' }" class="btn btn-primary btn-lg px-4">
                            Accéder à mon espace <i class="bi bi-arrow-right ms-2"></i>
                        </router-link>
                        <a href="#services" class="btn btn-outline-secondary btn-lg px-4">Découvrir LogiCar</a>
                    </div>
                    <div class="d-flex gap-4 mt-4 text-secondary small">
                        <span><i class="bi bi-check-circle-fill text-success me-1"></i>Planning centralisé</span>
                        <span><i class="bi bi-check-circle-fill text-success me-1"></i>Suivi de flotte</span>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="home-visual surface-card p-3 p-md-4">
                        <div class="bg-primary rounded-4 p-4 p-md-5 text-white">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="badge text-bg-light text-primary">TABLEAU DE BORD</span>
                                <i class="bi bi-bus-front fs-2"></i>
                            </div>
                            <div class="mt-5">
                                <div class="small text-white-50">Une vue claire de vos opérations</div>
                                <h2 class="h3 fw-bold mt-1">Du planning au départ.</h2>
                            </div>
                            <div class="row g-2 mt-4">
                                <div class="col-6"><div class="bg-white bg-opacity-10 rounded-3 p-3"><i class="bi bi-calendar-check fs-4"></i><div class="small mt-2">Plannings</div></div></div>
                                <div class="col-6"><div class="bg-white bg-opacity-10 rounded-3 p-3"><i class="bi bi-truck fs-4"></i><div class="small mt-2">Véhicules</div></div></div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between px-2 pt-3">
                            <span class="small text-secondary"><i class="bi bi-shield-check text-success me-1"></i>Un espace dédié à vos équipes</span>
                            <Logo />
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section id="services" class="container pb-5">
            <div class="text-center mx-auto mb-4">
                <h2 class="h2 fw-bold">Tout au même endroit</h2>
                <p class="text-secondary">Des outils simples pour coordonner les services et assurer le bon déroulement des trajets.</p>
            </div>
            <div class="row g-3">
                <div v-for="feature in features" :key="feature.title" class="col-md-4">
                    <article class="surface-card h-100 p-4">
                        <span class="stat-icon bg-primary-subtle text-primary mb-3"><i :class="`bi ${feature.icon}`"></i></span>
                        <h3 class="h5 fw-bold">{{ feature.title }}</h3>
                        <p class="text-secondary mb-0">{{ feature.description }}</p>
                    </article>
                </div>
            </div>
        </section>
    </main>
</template>

<script setup>
import Logo from "../../components/logo.vue";

const features = [
    { title: "Planifier les équipes", description: "Préparez les prises de service et consultez les listes de passagers par date.", icon: "bi-calendar2-week" },
    { title: "Coordonner les départs", description: "Affectez un itinéraire et un véhicule à chaque départ depuis une vue dédiée.", icon: "bi-signpost-split" },
    { title: "Suivre les véhicules", description: "Consultez la disponibilité de votre flotte et signalez les anomalies.", icon: "bi-bus-front" },
];
</script>
