<template>
    <section>
        <div class="page-heading">
            <span class="badge rounded-pill text-bg-primary-subtle text-primary mb-2">MON ESPACE</span>
            <h1 class="h2 mb-1">Mon compte</h1>
            <p>Retrouvez les informations associées à votre accès LogiCar.</p>
        </div>
        <div class="row g-4">
            <div class="col-12 col-lg-7">
                <article class="surface-card p-4 p-md-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="avatar-circle fs-3"><i class="bi bi-person"></i></span>
                        <div><h2 class="h4 fw-bold mb-1">{{ user?.name || "Utilisateur" }}</h2><span class="badge text-bg-primary-subtle text-primary">{{ user?.service || "Service non défini" }}</span></div>
                    </div>
                    <hr class="my-4">
                    <div class="row g-3">
                        <div class="col-sm-6"><div class="small text-secondary mb-1">Nom d’utilisateur</div><div class="fw-semibold">{{ user?.name || "—" }}</div></div>
                        <div class="col-sm-6"><div class="small text-secondary mb-1">Service</div><div class="fw-semibold">{{ user?.service || "—" }}</div></div>
                    </div>
                    <div class="alert alert-light border mt-4 mb-0 small"><i class="bi bi-info-circle text-primary me-2"></i>Pour modifier vos informations de compte, contactez votre administrateur.</div>
                </article>
            </div>
            <div class="col-12 col-lg-5">
                <article class="surface-card p-4 h-100">
                    <span class="stat-icon bg-primary-subtle text-primary mb-3"><i class="bi bi-shield-check"></i></span>
                    <h2 class="h5 fw-bold">Accès sécurisé</h2>
                    <p class="text-secondary mb-0">Votre espace est réservé aux membres de votre service. Pensez à vous déconnecter lorsque vous avez terminé.</p>
                </article>
            </div>
        </div>
    </section>
</template>

<script setup>
import { storeToRefs } from "pinia";
import { useStore } from "../stores/authStore.js";

const { user } = storeToRefs(useStore());
</script>
