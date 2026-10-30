<template>
    <section>
        <div class="page-heading">
            <span class="badge rounded-pill bg-primary-subtle text-primary mb-2">ADMINISTRATION</span>
            <h1 class="h2 mb-1">Utilisateurs</h1>
            <p>Créez et supprimez les comptes d’accès aux services LogiCar.</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-4">
                <div class="surface-card stat-card d-flex align-items-center gap-3">
                    <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-people"></i></span>
                    <div>
                        <div class="small text-secondary">Comptes</div>
                        <div class="fs-4 fw-bold">{{ users.length }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-4">
                <div class="surface-card stat-card d-flex align-items-center gap-3">
                    <span class="stat-icon bg-success-subtle text-success"><i class="bi bi-diagram-3"></i></span>
                    <div>
                        <div class="small text-secondary">Services</div>
                        <div class="fs-4 fw-bold">{{ services.length }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="surface-card overflow-hidden">
            <div class="p-3 p-md-4 border-bottom d-flex flex-column flex-md-row justify-content-between gap-3">
                <div>
                    <h2 class="h5 fw-bold mb-1">Comptes d’accès</h2>
                    <p class="small text-secondary mb-0">Les comptes peuvent être créés ou supprimés depuis cette page.</p>
                </div>
                <div class="d-flex flex-column flex-sm-row gap-2">
                    <div class="input-group search-control">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-secondary"></i></span>
                        <input v-model.trim="search" class="form-control" type="search" aria-label="Rechercher un utilisateur" placeholder="Nom ou service">
                    </div>
                    <button type="button" class="btn btn-primary" @click="openCreateModal">
                        <i class="bi bi-person-plus me-2"></i>Créer
                    </button>
                </div>
            </div>

            <div v-if="loading" class="empty-state">
                <span class="spinner-border spinner-border-sm text-primary me-2"></span>Chargement des comptes...
            </div>
            <div v-else-if="!filteredUsers.length" class="empty-state">
                <i class="bi bi-person-x"></i>
                <strong>{{ users.length ? "Aucun résultat" : "Aucun compte créé" }}</strong>
                <div class="small mt-1">Ajoutez un utilisateur pour commencer.</div>
            </div>
            <div v-else class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Service</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="userItem in filteredUsers" :key="userItem.id">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="avatar-circle text-primary bg-primary-subtle"><i class="bi bi-person"></i></span>
                                    <strong>{{ userItem.name }}</strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge text-bg-light">{{ userItem.service }}</span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-danger btn-sm" @click="deleteUser(userItem)">
                                    <i class="bi bi-trash me-1"></i>Supprimer
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="errorMessage" class="alert alert-danger mt-3">{{ errorMessage }}</div>
    </section>

    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form @submit.prevent="submitUser">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalLabel">Créer un utilisateur</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-12">
                            <div class="alert alert-info mb-0">
                                Mot de passe temporaire : <strong>0000</strong>. L’utilisateur devra le modifier dès sa première connexion.
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="user-name">Nom d’utilisateur</label>
                            <input id="user-name" v-model="form.name" class="form-control" type="text" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold" for="user-service">Service</label>
                            <select id="user-service" v-model="form.service" class="form-select" required>
                                <option value="">Sélectionner un service</option>
                                <option v-for="service in services" :key="service.service || service" :value="service.service || service">
                                    {{ service.service || service }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" :disabled="saving">
                            <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import axios from "axios";
import { Modal } from "bootstrap";
import { useAlert } from "../../services/alert.js";

const users = ref([]);
const services = ref([]);
const search = ref("");
const loading = ref(true);
const saving = ref(false);
const errorMessage = ref("");
const modal = ref(null);
const form = reactive({
    name: "",
    service: "",
});

const filteredUsers = computed(() => {
    const query = search.value.toLowerCase();
    return users.value.filter((userItem) => `${userItem.name} ${userItem.service}`.toLowerCase().includes(query));
});

function openCreateModal() {
    form.name = "";
    form.service = "";
    errorMessage.value = "";
    modal.value = Modal.getOrCreateInstance(document.getElementById("userModal"));
    modal.value.show();
}

async function loadUsers() {
    loading.value = true;
    errorMessage.value = "";

    try {
        const [usersResponse, servicesResponse] = await Promise.all([
            axios.get("/users"),
            axios.get("/services"),
        ]);

        users.value = Array.isArray(usersResponse.data) ? usersResponse.data : [];
        services.value = Array.isArray(servicesResponse.data) ? servicesResponse.data : [];
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "Impossible de charger les comptes.";
    } finally {
        loading.value = false;
    }
}

async function submitUser() {
    if (!form.name || !form.service) {
        errorMessage.value = "Tous les champs sont obligatoires.";
        return;
    }

    saving.value = true;
    errorMessage.value = "";

    try {
        const { data } = await axios.post("/users", {
            name: form.name,
            service: form.service,
        });

        users.value = [data.user, ...users.value];
        Modal.getOrCreateInstance(document.getElementById("userModal")).hide();
        useAlert("Succès", data.message || "Utilisateur créé.", "success");
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "La création de l’utilisateur a échoué.";
    } finally {
        saving.value = false;
    }
}

async function deleteUser(userItem) {
    if (!window.confirm(`Supprimer l’utilisateur ${userItem.name} ?`)) {
        return;
    }

    try {
        const { data } = await axios.delete(`/users/${userItem.id}`);
        users.value = users.value.filter((item) => item.id !== userItem.id);
        useAlert("Succès", data.message || "Utilisateur supprimé.", "success");
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "La suppression a échoué.";
    }
}

onMounted(loadUsers);
</script>
