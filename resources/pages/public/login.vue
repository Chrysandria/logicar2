<template>
    <main class="flex-grow-1 d-flex justify-content-center align-items-center">
        <div class="container">
            <div class="row">
                <div class="col d-none d-lg-flex">Section de gauche</div>
                <div class="col">
                    <div class="card shadow-sm p-4">
                        <div class="d-flex flex-column">
                            <div class="text-center text-primary mb-3">
                                <h1 class="display-1">
                                    <i class="bi bi-person-circle"></i>
                                </h1>
                                <p class="display-3">Connexion</p>
                            </div>
                            <div class="form-floating mb-3">
                                <input
                                    type="text"
                                    name="name"
                                    id="name"
                                    v-model="name"
                                    class="form-control"
                                    placeholder=""
                                />
                                <label for="name" class="form-label"
                                    >Nom d'utilisateur</label
                                >
                            </div>
                            <div class="d-flex flex-column mb-3 text-end">
                                <a
                                    href="#"
                                    class="text-decoration-none small mb-2"
                                    @click.prevent
                                    >Mot de passe oublié ?</a
                                >
                                <div class="input-group">
                                    <div class="form-floating">
                                        <input
                                            :type="showPassword ? 'text':'password'"
                                            name="password"
                                            id="password"
                                            v-model="password"
                                            class="form-control"
                                            placeholder=""
                                        />
                                        <label for="password" class="form-label"
                                            >Mot de passe</label
                                        >
                                    </div>
                                    <button
                                        class="btn btn-outline-secondary btn-lg"
                                        @click="showPassword = !showPassword"
                                    >
                                        <i class="bi" :class="showPassword ? 'bi-eye-slash':'bi-eye'"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-check mb-3">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    id="remember"
                                    v-model="remember"
                                    class="form-check-input"
                                />
                                <label for="remember" class="form-label small"
                                    >Se souvenir de moi</label
                                >
                            </div>
                            <button class="btn btn-primary" type="submit" @click="login">
                                Se connecter
                            </button>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <router-link to="/" class="text-muted">
                            <i class="bi bi-arrow-left me-3"></i>
                            Revenir à l'accueil
                        </router-link>
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

const name = ref("");
const password = ref("");
const remember = ref(false);
const showPassword = ref(false);

async function login() {
    name.value = name.value.trim();
    password.value = password.value.trim();

    if (name.value === "" || password.value === "") {
        useAlert(
            "Attention",
            "Tout les champs sont obligatoire, veuillez revérifier",
            "warning",
        );
        return;
    }

    const request = {
        url: "/auth/login",
        method: "post",
        data: {
            name: name.value,
            password: password.value,
            remember: remember.value,
        },
    };

    await useFetch(request);
}
</script>
