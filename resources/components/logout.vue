<template>
    <button
        class="btn btn-danger"
        data-bs-toggle="modal"
        data-bs-target="#logout"
    >
        <i class="bi bi-arrow-left me-2"></i>
        Se déconnecter
    </button>
    <teleport to="body">
        <div
            class="modal fade"
            id="logout"
            tabindex="-1"
            aria-labelledby="title"
            aria-hidden="true"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="title">
                            <i
                                class="bi bi-exclamation-triangle text-warning me-2"
                            ></i>
                            Attention
                        </h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="fermer"
                        ></button>
                    </div>
                    <div class="modal-body">
                        Souhaitez-vous réellement vous déconnecter ??
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Annuler
                        </button>
                        <button
                            type="button"
                            class="btn btn-danger"
                            data-bs-dismiss="modal"
                            @click="logout()"
                        >
                            Confirmer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>
<script setup>
import { useStore } from "../stores/authStore";
import { useAlert } from "../services/alert";

const store = useStore();

async function logout() {
    try {
        await store.logout();
    } catch (error) {
        useAlert(
            "Erreur",
            error.response?.data?.message || "La déconnexion n’a pas pu être effectuée.",
            "error",
        );
    }
}
</script>
