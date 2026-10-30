<template>
    <section>
        <div class="page-heading">
            <span class="badge rounded-pill bg-primary-subtle text-primary mb-2">MON ESPACE</span>
            <h1 class="h2 mb-1">Mon compte</h1>
            <p>Modifiez votre mot de passe pour sécuriser l’accès à LogiCar.</p>
        </div>

        <div class="row g-4">
            <div class="col-12 col-lg-7">
                <article class="surface-card p-4 p-md-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <span class="avatar-circle fs-3"><i class="bi bi-person"></i></span>
                        <div>
                            <h2 class="h4 fw-bold mb-1">{{ user?.name || "Utilisateur" }}</h2>
                            <span class="badge bg-primary-subtle text-primary">{{ user?.service || "Service non défini" }}</span>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="small text-secondary mb-1">Nom d’utilisateur</div>
                            <div class="fw-semibold">{{ user?.name || "—" }}</div>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-secondary mb-1">Service</div>
                            <div class="fw-semibold">{{ user?.service || "—" }}</div>
                        </div>
                    </div>
                </article>
            </div>

            <div class="col-12 col-lg-5">
                <article class="surface-card p-4 h-100">
                    <span class="stat-icon bg-success-subtle text-success mb-3"><i class="bi bi-shield-lock"></i></span>
                    <h2 class="h5 fw-bold mb-3">Modifier le mot de passe</h2>
                    <div v-if="user?.must_change_password" class="alert alert-warning small">
                        Cette modification est obligatoire avant d’utiliser l’application. Le mot de passe temporaire est <strong>0000</strong>.
                    </div>

                    <form @submit.prevent="submitPassword" class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="current-password">Mot de passe actuel</label>
                            <input id="current-password" v-model="form.currentPassword" :type="showPasswords ? 'text' : 'password'" class="form-control" autocomplete="current-password" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" for="new-password">Nouveau mot de passe</label>
                            <input id="new-password" v-model="form.password" :type="showPasswords ? 'text' : 'password'" class="form-control" autocomplete="new-password" required minlength="8">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold" for="confirm-password">Confirmer le mot de passe</label>
                            <input id="confirm-password" v-model="form.passwordConfirmation" :type="showPasswords ? 'text' : 'password'" class="form-control" autocomplete="new-password" required>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input id="show-passwords" v-model="showPasswords" class="form-check-input" type="checkbox">
                                <label class="form-check-label" for="show-passwords">Afficher les mots de passe</label>
                            </div>
                        </div>

                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-primary w-100" :disabled="submitting">
                                <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </article>
            </div>
        </div>
    </section>
</template>

<script setup>
import { reactive, ref } from "vue";
import { storeToRefs } from "pinia";
import axios from "axios";
import { useAlert } from "../services/alert.js";
import { useStore } from "../stores/authStore.js";
import { useRouter } from "vue-router";

const { user } = storeToRefs(useStore());
const router = useRouter();
const submitting = ref(false);
const showPasswords = ref(false);
const form = reactive({
    currentPassword: "",
    password: "",
    passwordConfirmation: "",
});

async function submitPassword() {
    if (!form.currentPassword || !form.password || !form.passwordConfirmation) {
        useAlert("Erreur", "Tous les champs sont obligatoires.", "error");
        return;
    }

    if (form.password.length < 8) {
        useAlert("Erreur", "Le nouveau mot de passe doit contenir au moins 8 caractères.", "error");
        return;
    }

    if (form.password !== form.passwordConfirmation) {
        useAlert("Erreur", "La confirmation du mot de passe ne correspond pas.", "error");
        return;
    }

    submitting.value = true;

    try {
        const { data } = await axios.put("/auth/password", {
            current_password: form.currentPassword,
            password: form.password,
            password_confirmation: form.passwordConfirmation,
        });

        user.value.must_change_password = false;
        form.currentPassword = "";
        form.password = "";
        form.passwordConfirmation = "";
        useAlert("Succès", data.message || "Votre mot de passe a bien été mis à jour.", "success");

        await router.replace(user.value.service === "SNR"
            ? { name: "snr.index" }
            : { name: "services.index", params: { service: user.value.service } });
    } catch (error) {
        useAlert("Erreur", error.response?.data?.message || "La modification du mot de passe a échoué.", "error");
    } finally {
        submitting.value = false;
    }
}
</script>
