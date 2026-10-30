<template>
    <main class="flex-grow-1 d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center align-items-center g-5">
                <div class="col-lg-5 d-none d-lg-block">
                    <span class="badge rounded-pill text-bg-primary-subtle text-primary mb-3">ESPACE PROFESSIONNEL</span>
                    <h1 class="display-5 fw-bold">Bienvenue sur votre espace LogiCar.</h1>
                    <p class="lead text-secondary mt-3">Retrouvez vos plannings et les informations essentielles de vos opérations transport.</p>
                    <div class="d-flex gap-3 align-items-center mt-4 text-secondary">
                        <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-lock"></i></span>
                        <span class="small">Connexion sécurisée pour les équipes LogiCar</span>
                    </div>
                </div>
                <div class="col-12 col-sm-10 col-md-8 col-lg-5">
                    <div class="surface-card p-4 p-md-5">
                        <div class="text-center mb-4">
                            <span class="stat-icon bg-primary-subtle text-primary mx-auto mb-3"><i class="bi bi-person-circle"></i></span>
                            <h2 class="h3 fw-bold">Connexion</h2>
                            <p class="text-secondary mb-0">Identifiez-vous pour continuer.</p>
                        </div>
                        <form @submit.prevent="login">
                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">Nom d’utilisateur</label>
                                <input id="name" v-model.trim="name" type="text" name="name" class="form-control" autocomplete="username" required autofocus />
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">Mot de passe</label>
                                <div class="input-group">
                                    <input id="password" v-model="password" :type="showPassword ? 'text' : 'password'" name="password" class="form-control" autocomplete="current-password" required />
                                    <button class="btn btn-outline-secondary" type="button" :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'" @click="showPassword = !showPassword"><i class="bi" :class="showPassword ? 'bi-eye-slash' : 'bi-eye'"></i></button>
                                </div>
                            </div>
                            <div class="form-check mb-4">
                                <input id="remember" v-model="remember" type="checkbox" class="form-check-input" />
                                <label for="remember" class="form-check-label small">Se souvenir de moi</label>
                            </div>
                            <button class="btn btn-primary btn-lg w-100" type="submit" :disabled="submitting">
                                <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                                {{ submitting ? "Connexion..." : "Se connecter" }}
                            </button>
                        </form>
                    </div>
                    <div class="text-center mt-3">
                        <router-link :to="{ name: 'public.index' }" class="link-secondary text-decoration-none small"><i class="bi bi-arrow-left me-2"></i>Revenir à l’accueil</router-link>
                    </div>
                </div>
            </div>
        </div>
    </main>
</template>

<script setup>
import { ref } from "vue";
import { useAlert } from "../../services/alert";
import { useFetch } from "../../services/fetch";
import { useStore } from "../../stores/authStore";

const name = ref("");
const password = ref("");
const remember = ref(false);
const showPassword = ref(false);
const submitting = ref(false);

async function login() {
    if (!name.value || !password.value || submitting.value) {
        return;
    }

    submitting.value = true;
    try {
        const authenticated = await useFetch({
            url: "/auth/login",
            method: "post",
            data: {
                name: name.value,
                password: password.value,
                remember: remember.value,
            },
        });
        if (authenticated) {
            await useStore().getConnected();
        }
    } catch (error) {
        useAlert("Erreur", "La connexion n’a pas pu être effectuée.", "error");
    } finally {
        submitting.value = false;
    }
}
</script>
