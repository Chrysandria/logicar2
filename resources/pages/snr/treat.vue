<template>
    <section>
        <div class="page-heading d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div>
                <span class="badge rounded-pill bg-primary-subtle text-primary mb-2">EXPLOITATION</span>
                <h1 class="h2 mb-1">Traitement des plannings</h1>
                <p>Composez les expéditions à partir des passagers prévus à chaque prise de service.</p>
            </div>
            <div class="surface-card px-3 py-2 d-flex align-items-center gap-2">
                <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-calendar-check"></i></span>
                <div><span class="small text-secondary d-block">Date des départs</span><strong>{{ frenchDate }}</strong></div>
            </div>
        </div>

        <div v-if="loading" class="surface-card empty-state">
            <span class="spinner-border spinner-border-sm text-primary me-2"></span>
            Chargement des plannings...
        </div>
        <div v-else-if="errorMessage" class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>{{ errorMessage }}
            <button class="btn btn-sm btn-outline-danger ms-2" @click="loadPlannings">Réessayer</button>
        </div>
        <div v-else-if="!sortedPlannings.length" class="surface-card empty-state">
            <i class="bi bi-calendar2-x"></i>
            <strong>Aucun planning prévu</strong>
            <div class="small mt-1">Les plannings de cette date apparaîtront ici.</div>
        </div>

        <template v-else>
            <article v-for="planning in sortedPlannings" :key="planning.id" class="surface-card overflow-hidden mb-4">
                <div class="p-3 p-lg-4 border-bottom d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-clock"></i></span>
                        <div>
                            <span class="small text-secondary">Prise de service</span>
                            <h2 class="h4 mb-0">{{ planning.hps?.slice(0, 5) }}</h2>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge rounded-pill" :class="isPlanningComplete(planning) ? 'text-bg-success' : unassignedItems(planning).length ? 'bg-primary-subtle text-primary' : 'text-bg-light'">
                            {{ isPlanningComplete(planning) ? 'Planning traité' : unassignedItems(planning).length ? `${unassignedItems(planning).length} à traiter` : 'Aucun employé' }}
                        </span>
                        <a v-if="isPlanningComplete(planning)" class="btn btn-sm btn-outline-success" :href="`/plannings/${planning.id}/report`" target="_blank" rel="noopener">
                            <i class="bi bi-file-earmark-pdf me-1"></i>Récapitulatif PDF
                        </a>
                        <button class="btn btn-sm btn-outline-primary" type="button" @click="openDispatchModal(planning)" :disabled="!unassignedItems(planning).length">
                            <i class="bi bi-plus-lg me-1"></i>Nouvelle expédition
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" type="button" @click="toggleRecap(planning.id)" :aria-expanded="recapIsVisible(planning.id)">
                            <i class="bi me-1" :class="recapIsVisible(planning.id) ? 'bi-eye-slash' : 'bi-list-check'"></i>
                            {{ recapIsVisible(planning.id) ? 'Masquer le récapitulatif' : 'Afficher le récapitulatif' }}
                        </button>
                    </div>
                </div>

                <div v-if="recapIsVisible(planning.id)" class="p-3 p-lg-4 border-bottom">
                    <h3 class="h6 fw-bold mb-3"><i class="bi bi-list-check text-success me-2"></i>Récapitulatif par expédition</h3>
                    <div v-if="recapGroups(planning).length" class="row g-3">
                        <div v-for="group in recapGroups(planning)" :key="group.expeditionId" class="col-12 col-xl-6">
                            <article class="border rounded-3 p-3 h-100">
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                    <div class="fw-semibold">Expédition #{{ group.expeditionId }}</div>
                                    <span v-if="expeditionInfo(group)?.trajetId" class="badge bg-primary-subtle text-primary">
                                        {{ routeName(expeditionInfo(group).trajetId) }}
                                    </span>
                                </div>
                                <div class="small text-secondary mb-2">{{ group.items.length }} passager{{ group.items.length > 1 ? 's' : '' }}</div>
                                <button
                                    v-if="unassignedItems(planning).length && group.items.length < 20"
                                    class="btn btn-sm btn-outline-primary mb-2"
                                    type="button"
                                    @click="openAddPassengers(planning, group)"
                                >
                                    <i class="bi bi-person-plus me-1"></i>Ajouter des employés
                                </button>
                                <ul class="list-group list-group-flush">
                                    <li v-for="item in group.items" :key="item.id" class="list-group-item px-0 py-2" style="background-color: transparent !important;">
                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                            <span class="fw-semibold">{{ item.employe?.nom || 'Employé' }}</span>
                                            <span class="text-secondary small">{{ item.employe?.matricule }}</span>
                                            <button
                                                v-if="correctingPlanningItemId !== item.id"
                                                class="btn btn-sm btn-outline-secondary ms-auto"
                                                type="button"
                                                @click="openAssignmentCorrection(item)"
                                            >
                                                <i class="bi bi-arrow-left-right me-1"></i>Corriger
                                            </button>
                                        </div>
                                        <form
                                            v-if="correctingPlanningItemId === item.id"
                                            class="row g-2 align-items-end mt-1"
                                            @submit.prevent="saveAssignmentCorrection(planning, item)"
                                        >
                                            <div class="col-12 col-md">
                                                <label class="form-label small mb-1" :for="`expedition-correction-${item.id}`">Affecter à</label>
                                                <select
                                                    :id="`expedition-correction-${item.id}`"
                                                    v-model="correctionExpeditionId"
                                                    class="form-select form-select-sm"
                                                    :disabled="savingCorrection"
                                                >
                                                    <option value="">Passagers en attente</option>
                                                    <option
                                                        v-for="optionGroup in recapGroups(planning)"
                                                        :key="optionGroup.expeditionId"
                                                        :value="optionGroup.expeditionId"
                                                        :disabled="optionGroup.items.length >= 20 && Number(optionGroup.expeditionId) !== Number(item.expedition_id)"
                                                    >
                                                        Expédition #{{ optionGroup.expeditionId }} · {{ routeName(expeditionInfo(optionGroup)?.trajetId) }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-auto d-flex gap-2">
                                                <button class="btn btn-sm btn-primary" type="submit" :disabled="savingCorrection">
                                                    <span v-if="savingCorrection" class="spinner-border spinner-border-sm me-1"></span>
                                                    Enregistrer
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary" type="button" :disabled="savingCorrection" @click="cancelAssignmentCorrection">
                                                    Annuler
                                                </button>
                                            </div>
                                            <div v-if="correctionError" class="col-12">
                                                <div class="alert alert-danger py-2 mb-0" role="alert">{{ correctionError }}</div>
                                            </div>
                                        </form>
                                    </li>
                                </ul>
                                <div v-if="expeditionInfo(group)" class="small text-secondary mt-2">
                                    <span v-if="expeditionInfo(group).carId"><i class="bi bi-bus-front me-1"></i>{{ carName(expeditionInfo(group).carId) }}</span>
                                    <span v-if="expeditionInfo(group).carId" class="mx-1">·</span>
                                    <i class="bi bi-clock me-1"></i>Début ramassage {{ expeditionInfo(group).hdr }}
                                </div>
                            </article>
                        </div>
                    </div>
                    <div v-else class="small text-secondary">Aucune expédition n’a encore été enregistrée pour ce planning.</div>
                </div>

                <div v-if="unassignedItems(planning).length" class="p-3 p-lg-4">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                        <div>
                            <h3 class="h6 fw-bold mb-1">Passagers en attente</h3>
                            <p class="small text-secondary mb-0">Les employés déjà associés à une expédition ne sont plus proposés ici.</p>
                        </div>
                        <span class="badge rounded-pill text-bg-light">{{ unassignedItems(planning).length }} employé{{ unassignedItems(planning).length > 1 ? 's' : '' }}</span>
                    </div>
                    <div class="table-responsive border rounded-3">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Matricule</th>
                                    <th>Nom</th>
                                    <th class="d-none d-md-table-cell">Lieu de ramassage</th>
                                    <th class="d-none d-lg-table-cell">Téléphone</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in unassignedItems(planning)" :key="item.id">
                                    <td><span class="badge text-bg-light">{{ item.employe?.matricule || '—' }}</span></td>
                                    <td class="fw-semibold">{{ item.employe?.nom || 'Employé' }}</td>
                                    <td class="d-none d-md-table-cell">{{ item.employe?.lieu_ramassage || '—' }}</td>
                                    <td class="d-none d-lg-table-cell">{{ item.employe?.telephone || '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div v-else-if="planning.planning_items?.length" class="p-4 text-center text-success">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Ce planning a été entièrement traité.</strong>
                    <div class="small mt-1 text-secondary">Tous les employés sont associés à une expédition.</div>
                </div>
                <div v-else class="p-4 text-center text-secondary">
                    <i class="bi bi-people fs-4 d-block mb-2"></i>
                    <strong>Aucun employé n’est inscrit à ce planning.</strong>
                </div>
            </article>
        </template>

        <div class="modal fade" id="dispatchModal" tabindex="-1" aria-labelledby="dispatchModalTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                <form class="modal-content" @submit.prevent="saveExpedition">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title h5 fw-bold" id="dispatchModalTitle">{{ dispatchMode === 'add' ? 'Ajouter des employés à l’expédition' : 'Préparer une expédition' }}</h2>
                            <p class="small text-secondary mb-0" v-if="activePlanning">Prise de service {{ activePlanning.hps?.slice(0, 5) }} · {{ frenchDate }}</p>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body">
                        <div v-if="dispatchMode === 'create'" class="row g-3 mb-4">
                            <div class="col-12 col-md-4">
                                <label for="dispatch-time" class="form-label fw-semibold">Début du ramassage</label>
                                <input id="dispatch-time" v-model="dispatchForm.hdr" class="form-control" type="time" required>
                                <div class="form-text">Saisissez l’heure pour rechercher les véhicules disponibles.</div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="dispatch-car" class="form-label fw-semibold">Véhicule</label>
                                <select id="dispatch-car" v-model="dispatchForm.carId" class="form-select" :disabled="!dispatchForm.hdr || loadingAvailableCars" required>
                                    <option value="">
                                        {{ !dispatchForm.hdr ? 'Saisissez d’abord une heure' : loadingAvailableCars ? 'Recherche en cours...' : availabilityError ? 'Recherche impossible' : eligibleCars.length ? 'Choisir un véhicule' : 'Aucun véhicule disponible' }}
                                    </option>
                                    <option v-for="car in eligibleCars" :key="car.id" :value="car.id">{{ car.immatriculation }}</option>
                                </select>
                                <div v-if="availabilityError" class="form-text text-danger" role="alert">{{ availabilityError }}</div>
                                <div v-else-if="dispatchForm.hdr && !loadingAvailableCars && eligibleCars.length" class="form-text">
                                    {{ eligibleCars.length }} véhicule{{ eligibleCars.length > 1 ? 's' : '' }} disponible{{ eligibleCars.length > 1 ? 's' : '' }} à cette heure.
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="dispatch-route" class="form-label fw-semibold">Trajet</label>
                                <select id="dispatch-route" v-model="dispatchForm.trajetId" class="form-select" required>
                                    <option value="">Choisir un trajet</option>
                                    <option v-for="trip in trips" :key="trip.id" :value="trip.id">{{ trip.designation }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-2">
                            <div>
                                <h3 class="h6 fw-bold mb-1">{{ dispatchMode === 'add' ? 'Ajouter des employés' : 'Employés concernés' }}</h3>
                                <p class="small text-secondary mb-0">
                                    {{ dispatchMode === 'add' ? `Sélectionnez les passagers à ajouter (${maxSelectableItems} place${maxSelectableItems > 1 ? 's' : ''} restante${maxSelectableItems > 1 ? 's' : ''}).` : 'Sélectionnez les passagers de cette expédition (maximum 20).' }}
                                </p>
                            </div>
                            <span class="badge rounded-pill bg-primary-subtle text-primary">{{ dispatchForm.itemIds.length }} sélectionné{{ dispatchForm.itemIds.length > 1 ? 's' : '' }}</span>
                        </div>
                        <div v-if="activePlanning && unassignedItems(activePlanning).length" class="table-responsive border rounded-3">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center"><input class="form-check-input" type="checkbox" :checked="allItemsSelected" :disabled="!canSelectMore && !allItemsSelected" aria-label="Sélectionner tous les employés" @change="toggleAllItems"></th>
                                        <th>Matricule</th><th>Nom</th><th class="d-none d-md-table-cell">Lieu de ramassage</th><th class="d-none d-lg-table-cell">Téléphone</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in unassignedItems(activePlanning)" :key="item.id">
                                        <td class="text-center"><input v-model="dispatchForm.itemIds" class="form-check-input" type="checkbox" :value="item.id" :disabled="!dispatchForm.itemIds.includes(item.id) && !canSelectMore" :aria-label="`Sélectionner ${item.employe?.nom || 'cet employé'}`"></td>
                                        <td><span class="badge text-bg-light">{{ item.employe?.matricule || '—' }}</span></td>
                                        <td class="fw-semibold">{{ item.employe?.nom || 'Employé' }}</td>
                                        <td class="d-none d-md-table-cell">{{ item.employe?.lieu_ramassage || '—' }}</td>
                                        <td class="d-none d-lg-table-cell">{{ item.employe?.telephone || '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="alert alert-light border mb-0">Aucun employé n’est disponible pour une nouvelle expédition.</div>
                        <div v-if="dispatchMode === 'add' && activeExpeditionGroup" class="small text-secondary mt-3">
                            Ajout à l’expédition #{{ activeExpeditionGroup.expeditionId }}
                            <span v-if="expeditionInfo(activeExpeditionGroup)?.carId"> · {{ carName(expeditionInfo(activeExpeditionGroup).carId) }}</span>
                        </div>
                        <div v-if="modalError" class="alert alert-danger mt-3 mb-0" role="alert">{{ modalError }}</div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Annuler</button>
                        <button class="btn btn-primary" type="submit" :disabled="saving || !dispatchForm.itemIds.length || dispatchForm.itemIds.length > maxSelectableItems">
                            <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                            <i v-else class="bi bi-check2-circle me-2"></i>{{ dispatchMode === 'add' ? 'Ajouter les employés' : 'Enregistrer l’expédition' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>

<script setup>
import axios from "axios";
import { Modal } from "bootstrap";
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { dateToFr } from "../../services/dayManager.js";
import { useAlert } from "../../services/alert.js";

const route = useRoute();
const plans = ref([]);
const trips = ref([]);
const cars = ref([]);
const eligibleCars = ref([]);
const loading = ref(true);
const loadingAvailableCars = ref(false);
const saving = ref(false);
const savingCorrection = ref(false);
const errorMessage = ref("");
const modalError = ref("");
const correctionError = ref("");
const availabilityError = ref("");
const recapIds = ref(new Set());
const activePlanningId = ref(null);
const activeExpeditionId = ref(null);
const correctingPlanningItemId = ref(null);
const correctionExpeditionId = ref("");
const dispatchMode = ref("create");
const expeditionDetails = reactive({});
const retryExpeditionId = ref(null);
const dispatchForm = reactive({ carId: "", trajetId: "", hdr: "", itemIds: [] });
let availabilityRequestId = 0;
const dateIso = computed(() => String(route.params.date || ""));
const frenchDate = computed(() => dateIso.value ? dateToFr(new Date(`${dateIso.value}T12:00:00`)) : "");
const sortedPlannings = computed(() => [...plans.value].sort((a, b) => String(a.hps).localeCompare(String(b.hps))));
const activePlanning = computed(() => plans.value.find((planning) => planning.id === activePlanningId.value));
const activeExpeditionGroup = computed(() => activePlanning.value
    ? recapGroups(activePlanning.value).find((group) => Number(group.expeditionId) === Number(activeExpeditionId.value))
    : null);
const selectableItems = computed(() => activePlanning.value ? unassignedItems(activePlanning.value) : []);
const maxSelectableItems = computed(() => dispatchMode.value === "add"
    ? Math.max(0, 20 - (activeExpeditionGroup.value?.items.length || 0))
    : 20);
const canSelectMore = computed(() => dispatchForm.itemIds.length < maxSelectableItems.value);
const allItemsSelected = computed(() => selectableItems.value.length > 0
    && selectableItems.value.slice(0, maxSelectableItems.value).length > 0
    && selectableItems.value.slice(0, maxSelectableItems.value).every((item) => dispatchForm.itemIds.includes(item.id)));

function sortedItems(planning) {
    return [...(planning.planning_items || [])].sort((a, b) => {
        const nameOrder = String(a.employe?.nom || "").localeCompare(String(b.employe?.nom || ""), "fr");
        return nameOrder || String(a.employe?.matricule || "").localeCompare(String(b.employe?.matricule || ""), "fr");
    });
}

function unassignedItems(planning) {
    return sortedItems(planning).filter((item) => !item.expedition_id);
}

function isPlanningComplete(planning) {
    return Boolean(planning.planning_items?.length) && unassignedItems(planning).length === 0;
}

function recapGroups(planning) {
    const groups = new Map();
    for (const item of sortedItems(planning).filter((planningItem) => planningItem.expedition_id)) {
        const expeditionId = item.expedition_id;
        if (!groups.has(expeditionId)) {
            groups.set(expeditionId, { expeditionId, expedition: item.expedition, items: [] });
        }
        groups.get(expeditionId).items.push(item);
    }

    return [...groups.values()].sort((a, b) => Number(a.expeditionId) - Number(b.expeditionId));
}

function recapIsVisible(planningId) {
    return recapIds.value.has(planningId);
}

function toggleRecap(planningId) {
    const next = new Set(recapIds.value);
    if (next.has(planningId)) {
        next.delete(planningId);
    } else {
        next.add(planningId);
    }
    recapIds.value = next;
}

function routeName(tripId) {
    return trips.value.find((trip) => Number(trip.id) === Number(tripId))?.designation || `Trajet #${tripId}`;
}

function carName(carId) {
    return cars.value.find((car) => Number(car.id) === Number(carId))?.immatriculation || `Véhicule #${carId}`;
}

function expeditionInfo(group) {
    const savedDetails = expeditionDetails[group.expeditionId];
    if (savedDetails) {
        return savedDetails;
    }

    if (!group.expedition) {
        return null;
    }

    return {
        carId: group.expedition.car_id,
        trajetId: group.expedition.trajet_id,
        hdr: group.expedition.hdr?.slice(0, 5),
    };
}

function openAssignmentCorrection(item) {
    correctingPlanningItemId.value = item.id;
    correctionExpeditionId.value = item.expedition_id;
    correctionError.value = "";
}

function cancelAssignmentCorrection() {
    correctingPlanningItemId.value = null;
    correctionExpeditionId.value = "";
    correctionError.value = "";
}

async function saveAssignmentCorrection(planning, item) {
    if (savingCorrection.value) {
        return;
    }

    savingCorrection.value = true;
    correctionError.value = "";
    try {
        await axios.put(`/planning-items/${item.id}/expedition`, {
            expedition_id: correctionExpeditionId.value ? Number(correctionExpeditionId.value) : null,
        });

        recapIds.value = new Set([...recapIds.value, planning.id]);
        cancelAssignmentCorrection();
        await loadPlannings();
        useAlert("Affectation corrigée", "L’employé a été déplacé vers l’expédition sélectionnée.", "success");
    } catch (error) {
        correctionError.value = error.response?.data?.message || error.message || "La correction de l’affectation a échoué.";
        useAlert("Erreur", correctionError.value, "error");
    } finally {
        savingCorrection.value = false;
    }
}

async function loadPlannings() {
    loading.value = true;
    errorMessage.value = "";
    try {
        const [planningResponse, tripResponse, carResponse] = await Promise.all([
            axios.get("/plannings", { params: { dps: dateIso.value } }),
            axios.get("/trajets"),
            axios.get("/cars"),
        ]);
        if (!Array.isArray(planningResponse.data) || !Array.isArray(tripResponse.data) || !Array.isArray(carResponse.data)) {
            throw new Error("Les données reçues ne sont pas au format attendu.");
        }
        plans.value = planningResponse.data;
        trips.value = tripResponse.data;
        cars.value = carResponse.data;
    } catch (error) {
        errorMessage.value = error.response?.data?.message || error.message || "Impossible de charger les plannings, trajets et véhicules.";
    } finally {
        loading.value = false;
    }
}

function openDispatchModal(planning) {
    activePlanningId.value = planning.id;
    activeExpeditionId.value = null;
    dispatchMode.value = "create";
    dispatchForm.carId = "";
    dispatchForm.trajetId = "";
    dispatchForm.hdr = "";
    dispatchForm.itemIds = [];
    modalError.value = "";
    availabilityError.value = "";
    eligibleCars.value = [];
    retryExpeditionId.value = null;
    Modal.getOrCreateInstance(document.getElementById("dispatchModal")).show();
}

function openAddPassengers(planning, group) {
    activePlanningId.value = planning.id;
    activeExpeditionId.value = group.expeditionId;
    dispatchMode.value = "add";
    dispatchForm.carId = "";
    dispatchForm.trajetId = "";
    dispatchForm.hdr = "";
    dispatchForm.itemIds = [];
    modalError.value = "";
    availabilityError.value = "";
    eligibleCars.value = [];
    retryExpeditionId.value = null;
    Modal.getOrCreateInstance(document.getElementById("dispatchModal")).show();
}

watch(() => dispatchForm.hdr, async (hdr) => {
    const requestId = ++availabilityRequestId;
    dispatchForm.carId = "";
    eligibleCars.value = [];
    availabilityError.value = "";

    if (!dateIso.value || !/^\d{2}:\d{2}$/.test(hdr)) {
        loadingAvailableCars.value = false;
        return;
    }

    loadingAvailableCars.value = true;
    try {
        const { data } = await axios.get("/expeditions/available-cars", {
            params: { date: dateIso.value, hdr },
        });
        if (requestId !== availabilityRequestId) {
            return;
        }
        if (!Array.isArray(data)) {
            throw new Error("La liste des véhicules reçue est invalide.");
        }
        eligibleCars.value = data;
    } catch (error) {
        if (requestId === availabilityRequestId) {
            availabilityError.value = error.response?.data?.message || error.message || "Impossible de rechercher les véhicules disponibles.";
        }
    } finally {
        if (requestId === availabilityRequestId) {
            loadingAvailableCars.value = false;
        }
    }
});

function toggleAllItems(event) {
    if (event.target.checked) {
        dispatchForm.itemIds = selectableItems.value.slice(0, maxSelectableItems.value).map((item) => item.id);
        return;
    }
    dispatchForm.itemIds = [];
}

async function saveExpedition() {
    if (saving.value || !activePlanning.value) {
        return;
    }
    if (dispatchMode.value === "create" && (!dispatchForm.carId || !dispatchForm.trajetId || !dispatchForm.hdr)) {
        modalError.value = "Choisissez un véhicule, un trajet et une heure de début de ramassage.";
        return;
    }
    if (!dispatchForm.itemIds.length || dispatchForm.itemIds.length > maxSelectableItems.value) {
        modalError.value = dispatchMode.value === "add"
            ? "Sélectionnez au moins un employé sans dépasser les places restantes."
            : "Sélectionnez entre 1 et 20 employés.";
        return;
    }

    const planning = activePlanning.value;
    const selectedIds = new Set(dispatchForm.itemIds);
    const selectedItems = unassignedItems(planning).filter((item) => selectedIds.has(item.id));
    if (selectedItems.length !== selectedIds.size) {
        modalError.value = "La sélection a changé. Fermez la fenêtre et rechargez le planning.";
        return;
    }

    saving.value = true;
    modalError.value = "";
    try {
        if (dispatchMode.value === "add") {
            await axios.put(`/expeditions/${activeExpeditionId.value}`, {
                planning_id: planning.id,
                add_list: selectedItems.map((item) => ({ planning_item_id: item.id })),
            });

            recapIds.value = new Set([...recapIds.value, planning.id]);
            Modal.getOrCreateInstance(document.getElementById("dispatchModal")).hide();
            useAlert("Employés ajoutés", "Les employés sélectionnés ont été ajoutés à l’expédition.", "success");
            await loadPlannings();
            return;
        }

        let expeditionId = retryExpeditionId.value;
        if (!expeditionId) {
            const { data: expedition } = await axios.post("/expeditions", {
                date: dateIso.value,
                hdr: dispatchForm.hdr,
                trajet_id: dispatchForm.trajetId,
                list: selectedItems.map((item) => ({ planning_item_id: item.id })),
            });
            if (!expedition?.id) {
                throw new Error("L’expédition a été créée, mais son identifiant est introuvable.");
            }
            expeditionId = expedition.id;
            retryExpeditionId.value = expeditionId;
        }

        await axios.put(`/expeditions/${expeditionId}`, {
            car_id: dispatchForm.carId,
        });

        expeditionDetails[expeditionId] = {
            carId: Number(dispatchForm.carId),
            trajetId: Number(dispatchForm.trajetId),
            hdr: dispatchForm.hdr,
        };
        recapIds.value = new Set([...recapIds.value, planning.id]);
        const modal = Modal.getOrCreateInstance(document.getElementById("dispatchModal"));
        modal.hide();
        useAlert("Expédition enregistrée", "Les employés sélectionnés ont été affectés au trajet.", "success");
        await loadPlannings();
    } catch (error) {
        modalError.value = error.response?.data?.message || error.message || "L’enregistrement de l’expédition a échoué.";
        if (retryExpeditionId.value) {
            modalError.value += " Vous pouvez réessayer sans créer une autre expédition.";
        }
        useAlert("Erreur", modalError.value, "error");
    } finally {
        saving.value = false;
    }
}

onMounted(loadPlannings);
watch(dateIso, () => {
    if (dateIso.value) {
        loadPlannings();
    }
});
</script>
