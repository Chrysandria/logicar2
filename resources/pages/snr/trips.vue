<template>
    <section>
        <div class="page-heading d-flex flex-column flex-sm-row justify-content-between align-items-sm-end gap-3">
            <div>
                <span class="badge rounded-pill bg-primary-subtle text-primary mb-2">ADMINISTRATION</span>
                <h1 class="h2 mb-1">Gestion des trajets</h1>
                <p>Créez et maintenez les itinéraires et leurs points de ramassage.</p>
            </div>
            <button class="btn btn-primary" type="button" @click="openCreate">
                <i class="bi bi-plus-lg me-2"></i>Nouveau trajet
            </button>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-4">
                <div class="surface-card stat-card d-flex align-items-center gap-3">
                    <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-signpost-split"></i></span>
                    <div><div class="small text-secondary">Trajets enregistrés</div><div class="fs-4 fw-bold">{{ trips.length }}</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="surface-card stat-card d-flex align-items-center gap-3">
                    <span class="stat-icon bg-success-subtle text-success"><i class="bi bi-geo-alt"></i></span>
                    <div><div class="small text-secondary">Points référencés</div><div class="fs-4 fw-bold">{{ pointCount }}</div></div>
                </div>
            </div>
        </div>

        <div class="surface-card overflow-hidden">
            <div class="p-3 p-md-4 border-bottom d-flex flex-column flex-md-row justify-content-between gap-3">
                <div>
                    <h2 class="h5 fw-bold mb-1">Itinéraires</h2>
                    <p class="small text-secondary mb-0">Consultez, modifiez ou supprimez un trajet.</p>
                </div>
                <div class="input-group search-control">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-secondary"></i></span>
                    <input v-model.trim="search" class="form-control" type="search" aria-label="Rechercher un trajet" placeholder="Nom du trajet">
                </div>
            </div>

            <div v-if="loading" class="empty-state">
                <span class="spinner-border spinner-border-sm text-primary me-2"></span>Chargement des trajets...
            </div>
            <div v-else-if="!filteredTrips.length" class="empty-state">
                <i class="bi bi-signpost"></i>
                <strong>{{ trips.length ? "Aucun résultat" : "Aucun trajet enregistré" }}</strong>
                <div class="small mt-1">{{ trips.length ? "Modifiez votre recherche." : "Créez un trajet pour le rendre disponible lors du traitement des plannings." }}</div>
            </div>
            <div v-else class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Trajet</th><th>Points de passage</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="trip in filteredTrips" :key="trip.id">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-signpost-2"></i></span>
                                    <strong>{{ trip.designation }}</strong>
                                </div>
                            </td>
                            <td>
                                <span v-if="trip.trajet_items?.length" class="d-flex flex-wrap gap-1">
                                    <span v-for="(item, index) in trip.trajet_items" :key="item.id" class="badge text-bg-light">
                                        {{ index + 1 }}. {{ item.point }}
                                    </span>
                                </span>
                                <span v-else class="text-secondary small">Aucun point défini</span>
                            </td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-sm btn-outline-primary me-1" type="button" :aria-label="`Modifier ${trip.designation}`" @click="openEdit(trip)">
                                    <i class="bi bi-pencil-square me-md-1"></i><span class="d-none d-md-inline">Modifier</span>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" type="button" :aria-label="`Supprimer ${trip.designation}`" @click="deleteTrip(trip)">
                                    <i class="bi bi-trash me-md-1"></i><span class="d-none d-md-inline">Supprimer</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div v-if="errorMessage" class="alert alert-danger mt-3" role="alert">{{ errorMessage }}</div>

        <div class="modal fade" id="tripModal" tabindex="-1" aria-labelledby="tripModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <form class="modal-content" @submit.prevent="saveTrip">
                    <div class="modal-header">
                        <div>
                            <h2 id="tripModalTitle" class="modal-title h5 fw-bold">{{ form.id ? "Modifier le trajet" : "Créer un trajet" }}</h2>
                            <p class="small text-secondary mb-0">Définissez un nom et ordonnez ses points de passage.</p>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-4">
                            <label for="trip-designation" class="form-label fw-semibold">Nom du trajet</label>
                            <input id="trip-designation" v-model.trim="form.designation" class="form-control" type="text" maxlength="255" required placeholder="Ex. Centre-ville — Dépôt">
                        </div>
                        <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                            <div>
                                <h3 class="h6 fw-bold mb-1">Points de passage</h3>
                                <p class="small text-secondary mb-0">Les points suivent l’ordre défini ci-dessous.</p>
                            </div>
                            <button class="btn btn-sm btn-outline-primary text-nowrap" type="button" @click="addPoint">
                                <i class="bi bi-plus-lg me-1"></i>Ajouter un point
                            </button>
                        </div>
                        <div v-if="form.points.length" class="d-grid gap-2">
                            <button class="btn btn-sm btn-light text-primary align-self-center" type="button" @click="addPoint(0)">
                                <i class="bi bi-plus-circle me-1"></i>Insérer avant le premier point
                            </button>
                            <template v-for="(point, index) in form.points" :key="point.key">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary-subtle text-primary">{{ index + 1 }}</span>
                                    <input
                                        :ref="(element) => (pointInputs[index] = element)"
                                        v-model.trim="point.value"
                                        class="form-control"
                                        type="text"
                                        maxlength="255"
                                        :aria-label="`Point de passage ${index + 1}`"
                                        :placeholder="`Point de passage ${index + 1}`"
                                        @keydown.enter.prevent="addPoint(index + 1)"
                                    >
                                    <div class="btn-group">
                                        <button class="btn btn-outline-secondary" type="button" :disabled="index === 0" :aria-label="`Déplacer le point ${index + 1} vers le haut`" @click="movePoint(index, -1)"><i class="bi bi-arrow-up"></i></button>
                                        <button class="btn btn-outline-secondary" type="button" :disabled="index === form.points.length - 1" :aria-label="`Déplacer le point ${index + 1} vers le bas`" @click="movePoint(index, 1)"><i class="bi bi-arrow-down"></i></button>
                                    </div>
                                    <button class="btn btn-outline-danger" type="button" :disabled="form.points.length === 1" :aria-label="`Retirer le point ${index + 1}`" @click="removePoint(index)"><i class="bi bi-x-lg"></i></button>
                                </div>
                                <button
                                    v-if="index < form.points.length - 1"
                                    class="btn btn-sm btn-light text-primary align-self-center"
                                    type="button"
                                    :aria-label="`Insérer un point entre les points ${index + 1} et ${index + 2}`"
                                    @click="addPoint(index + 1)"
                                >
                                    <i class="bi bi-plus-circle me-1"></i>Insérer ici
                                </button>
                            </template>
                        </div>
                        <div v-else class="alert alert-light border mb-0">Ajoutez au moins un point de passage.</div>
                        <div v-if="formError" class="alert alert-danger mt-3 mb-0" role="alert">{{ formError }}</div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                        <button class="btn btn-primary" type="submit" :disabled="saving">
                            <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                            <i v-else class="bi bi-check2-circle me-2"></i>{{ form.id ? "Enregistrer les modifications" : "Créer le trajet" }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>

<script setup>
import axios from "axios";
import Swal from "sweetalert2";
import { Modal } from "bootstrap";
import { computed, nextTick, onMounted, reactive, ref } from "vue";
import { useAlert } from "../../services/alert.js";

const trips = ref([]);
const loading = ref(true);
const saving = ref(false);
const search = ref("");
const errorMessage = ref("");
const formError = ref("");
const nextKey = ref(0);
const pointInputs = ref([]);
const form = reactive({ id: null, designation: "", points: [] });
const pointCount = computed(() => trips.value.reduce((total, trip) => total + (trip.trajet_items?.length || 0), 0));
const filteredTrips = computed(() => {
    const query = search.value.toLocaleLowerCase("fr");
    return trips.value.filter((trip) => trip.designation.toLocaleLowerCase("fr").includes(query));
});

function blankPoint(value = "") {
    return { key: nextKey.value++, value };
}

function openCreate() {
    form.id = null;
    form.designation = "";
    form.points = [blankPoint()];
    formError.value = "";
    Modal.getOrCreateInstance(document.getElementById("tripModal")).show();
}

async function openEdit(trip) {
    formError.value = "";
    try {
        const { data } = await axios.get(`/trajets/${trip.id}`);
        form.id = data.id;
        form.designation = data.designation;
        form.points = (data.trajet_items || []).map((item) => blankPoint(item.point));
        if (!form.points.length) {
            form.points = [blankPoint()];
        }
        Modal.getOrCreateInstance(document.getElementById("tripModal")).show();
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "Impossible de charger les détails du trajet.";
        useAlert("Erreur", errorMessage.value, "error");
    }
}

async function addPoint(index = form.points.length) {
    const newPointIndex = index;
    form.points.splice(newPointIndex, 0, blankPoint());
    await nextTick();
    pointInputs.value[newPointIndex]?.focus();
}

function removePoint(index) {
    if (form.points.length > 1) {
        form.points.splice(index, 1);
    }
}

function movePoint(index, direction) {
    const destination = index + direction;
    if (destination < 0 || destination >= form.points.length) {
        return;
    }
    [form.points[index], form.points[destination]] = [form.points[destination], form.points[index]];
}

async function loadTrips() {
    loading.value = true;
    errorMessage.value = "";
    try {
        const { data } = await axios.get("/trajets");
        if (!Array.isArray(data)) {
            throw new Error("Les trajets reçus ne sont pas au format attendu.");
        }
        trips.value = data;
    } catch (error) {
        errorMessage.value = error.response?.data?.message || error.message || "Le chargement des trajets a échoué.";
    } finally {
        loading.value = false;
    }
}

async function saveTrip() {
    if (saving.value) {
        return;
    }
    const designation = form.designation.trim();
    const nonEmptyPoints = form.points.filter((point) => point.value.trim());
    form.points.splice(0, form.points.length, ...nonEmptyPoints);
    const points = form.points.map((point) => point.value.trim());
    if (!designation || !points.length) {
        formError.value = "Le nom du trajet et au moins un point de passage sont obligatoires.";
        return;
    }

    saving.value = true;
    formError.value = "";
    try {
        const payload = {
            designation,
            trajet_items: points.map((point) => ({ point })),
        };
        if (form.id) {
            await axios.put(`/trajets/${form.id}`, payload);
        } else {
            await axios.post("/trajets", payload);
        }
        Modal.getOrCreateInstance(document.getElementById("tripModal")).hide();
        useAlert("Succès", form.id ? "Le trajet a été modifié." : "Le trajet a été créé.", "success");
        await loadTrips();
    } catch (error) {
        formError.value = error.response?.data?.message || "L’enregistrement du trajet a échoué.";
    } finally {
        saving.value = false;
    }
}

async function deleteTrip(trip) {
    const confirmation = await Swal.fire({
        title: "Supprimer ce trajet ?",
        text: `« ${trip.designation} » et ses points de passage seront supprimés. Cette action est définitive.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Supprimer",
        cancelButtonText: "Annuler",
        confirmButtonColor: "#dc3545",
        customClass: {
            popup: "logicar-swal-popup logicar-swal-alert logicar-swal-alert--warning",
        },
    });
    if (!confirmation.isConfirmed) {
        return;
    }

    try {
        await axios.delete(`/trajets/${trip.id}`);
        useAlert("Trajet supprimé", "Le trajet a été supprimé.", "success");
        await loadTrips();
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "La suppression du trajet a échoué.";
        useAlert("Erreur", errorMessage.value, "error");
    }
}

onMounted(loadTrips);
</script>
