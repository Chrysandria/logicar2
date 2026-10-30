<template>
    <section>
        <div class="page-heading d-flex flex-column flex-sm-row justify-content-between align-items-sm-end gap-3">
            <div><span class="badge rounded-pill bg-primary-subtle text-primary mb-2">ADMINISTRATION</span><h1 class="h2 mb-1">Gestion de la flotte</h1><p>Véhicules disponibles, immobilisés et signalements.</p></div>
            <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#addMileage"><i class="bi bi-speedometer2 me-2"></i>Saisir un kilométrage</button>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addCar"><i class="bi bi-plus-lg me-2"></i>Ajouter un véhicule</button>
            </div>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-4"><div class="surface-card stat-card d-flex align-items-center gap-3"><span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-bus-front"></i></span><div><div class="small text-secondary">Total flotte</div><div class="fs-4 fw-bold">{{ cars.length }}</div></div></div></div>
            <div class="col-6 col-lg-4"><div class="surface-card stat-card d-flex align-items-center gap-3"><span class="stat-icon bg-success-subtle text-success"><i class="bi bi-check-circle"></i></span><div><div class="small text-secondary">Disponibles</div><div class="fs-4 fw-bold">{{ availableCars.length }}</div></div></div></div>
            <div class="col-12 col-lg-4"><div class="surface-card stat-card d-flex align-items-center gap-3"><span class="stat-icon bg-danger-subtle text-danger"><i class="bi bi-tools"></i></span><div><div class="small text-secondary">Immobilisés</div><div class="fs-4 fw-bold">{{ cars.length - availableCars.length }}</div></div></div></div>
        </div>
        <div class="surface-card overflow-hidden">
            <div class="p-3 p-md-4 border-bottom d-flex flex-column flex-md-row justify-content-between gap-3">
                <div><h2 class="h5 fw-bold mb-1">Véhicules</h2><p class="small text-secondary mb-0">État actuel et dernières anomalies actives.</p></div>
                <div class="input-group search-control"><span class="input-group-text bg-white"><i class="bi bi-search text-secondary"></i></span><input v-model.trim="search" class="form-control" type="search" aria-label="Rechercher un véhicule" placeholder="Immatriculation"></div>
            </div>
            <div v-if="loading" class="empty-state"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Chargement de la flotte...</div>
            <div v-else-if="!filteredCars.length" class="empty-state"><i class="bi bi-bus-front"></i><strong>{{ cars.length ? "Aucun résultat" : "Votre flotte est vide" }}</strong><div class="small mt-1">Ajoutez un véhicule ou modifiez votre recherche.</div></div>
            <div v-else class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Immatriculation</th><th>Disponibilité</th><th class="d-none d-md-table-cell">Anomalie active</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                        <tr v-for="car in filteredCars" :key="car.id">
                            <td><div class="d-flex align-items-center gap-2"><span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-bus-front"></i></span><strong>{{ car.immatriculation }}</strong></div></td>
                            <td><span class="badge rounded-pill" :class="car.is_available ? 'text-bg-success' : 'text-bg-danger'"><i class="bi me-1" :class="car.is_available ? 'bi-check-circle' : 'bi-tools'"></i>{{ car.is_available ? "Disponible" : "Hors service" }}</span></td>
                            <td class="d-none d-md-table-cell text-secondary">{{ activeAnomaly(car.id)?.description || "—" }}</td>
                            <td class="text-end">
                                <button v-if="!car.is_available && activeAnomaly(car.id)" class="btn btn-sm btn-outline-success" :disabled="savingCar === car.id" @click="restoreCar(car, activeAnomaly(car.id))">
                                    <span v-if="savingCar === car.id" class="spinner-border spinner-border-sm me-1"></span><i v-else class="bi bi-check2-circle me-1"></i>Remettre en service
                                </button>
                                <button v-else class="btn btn-sm btn-outline-danger" :disabled="!car.is_available" @click="selectedCar = car" data-bs-toggle="modal" data-bs-target="#reportIssue"><i class="bi bi-exclamation-triangle me-1"></i>Signaler</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="surface-card overflow-hidden mt-4">
            <div class="p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center gap-2">
                <div><h2 class="h5 fw-bold mb-1">Kilométrage du jour</h2><p class="small text-secondary mb-0">Relevés enregistrés pour aujourd’hui.</p></div>
                <span class="badge rounded-pill text-bg-light">{{ dailyMovements.length }} relevés</span>
            </div>
            <div v-if="!dailyMovements.length" class="empty-state"><i class="bi bi-speedometer"></i><strong>Aucun relevé aujourd’hui</strong><div class="small mt-1">Saisissez le kilométrage d’un véhicule après son retour.</div></div>
            <div v-else class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Véhicule</th><th class="text-end">Kilométrage saisi</th></tr></thead>
                    <tbody><tr v-for="movement in dailyMovements" :key="movement.id || movement.immatriculation"><td class="fw-semibold">{{ movement.immatriculation }}</td><td class="text-end">{{ Number(movement.km).toLocaleString("fr-FR") }} km</td></tr></tbody>
                </table>
            </div>
        </div>
        <div class="surface-card overflow-hidden mt-4">
            <div class="p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center gap-2">
                <div><h2 class="h5 fw-bold mb-1">Kilométrage mensuel</h2><p class="small text-secondary mb-0">Total parcouru par véhicule ce mois-ci.</p></div>
                <span class="badge rounded-pill bg-primary-subtle text-primary">{{ currentMonth }}</span>
            </div>
            <div v-if="!cars.length" class="empty-state"><i class="bi bi-graph-up"></i><strong>Aucun véhicule à afficher</strong></div>
            <div v-else class="p-3 p-md-4">
                <Chart
                    :labels="monthlyChart.labels"
                    :values="monthlyChart.values"
                    label="Kilométrage mensuel"
                />
            </div>
            <div v-if="monthlyMovements.length" class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Véhicule</th><th class="text-end">Total parcouru</th></tr></thead>
                    <tbody><tr v-for="movement in monthlyMovements" :key="movement.id"><td class="fw-semibold">{{ movement.immatriculation }}</td><td class="text-end">{{ Number(movement.total).toLocaleString("fr-FR") }} km</td></tr></tbody>
                </table>
            </div>
            <div v-else-if="cars.length" class="px-3 px-md-4 pb-3 small text-secondary">Aucun relevé kilométrique enregistré ce mois-ci.</div>
        </div>
        <div v-if="errorMessage" class="alert alert-danger mt-3">{{ errorMessage }}</div>

        <div class="modal fade" id="addCar" tabindex="-1" aria-labelledby="addCarTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered"><form class="modal-content" @submit.prevent="createCar">
                <div class="modal-header"><h2 class="modal-title h5 fw-bold" id="addCarTitle"><i class="bi bi-plus-circle text-primary me-2"></i>Ajouter un véhicule</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Fermer"></button></div>
                <div class="modal-body"><label class="form-label fw-semibold" for="registration">Immatriculation</label><input id="registration" v-model.trim="newRegistration" class="form-control text-uppercase" required maxlength="30" placeholder="Ex. AB-123-CD"></div>
                <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary" type="submit" :disabled="saving"><span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>Enregistrer</button></div>
            </form></div>
        </div>
        <div class="modal fade" id="reportIssue" tabindex="-1" aria-labelledby="issueTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered"><form class="modal-content" @submit.prevent="reportAnomaly">
                <div class="modal-header"><h2 class="modal-title h5 fw-bold" id="issueTitle"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Signaler une anomalie</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Fermer"></button></div>
                <div class="modal-body"><p class="small text-secondary">Le véhicule <strong>{{ selectedCar?.immatriculation }}</strong> sera placé hors service.</p><label for="issue-description" class="form-label fw-semibold">Description</label><textarea id="issue-description" v-model.trim="description" class="form-control" rows="4" maxlength="255" required placeholder="Décrivez le problème constaté"></textarea></div>
                <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Annuler</button><button class="btn btn-danger" type="submit" :disabled="saving"><span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>Confirmer le signalement</button></div>
            </form></div>
        </div>
        <div class="modal fade" id="addMileage" tabindex="-1" aria-labelledby="mileageTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered"><form class="modal-content" @submit.prevent="createMovement">
                <div class="modal-header"><h2 class="modal-title h5 fw-bold" id="mileageTitle"><i class="bi bi-speedometer2 text-primary me-2"></i>Relevé kilométrique</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Fermer"></button></div>
                <div class="modal-body">
                    <div class="alert alert-light border small"><i class="bi bi-calendar3 text-primary me-2"></i>La date du relevé est aujourd’hui.</div>
                    <label class="form-label fw-semibold" for="movement-car">Véhicule</label>
                    <select id="movement-car" v-model="movementCarId" class="form-select mb-3" required>
                        <option value="">Choisir un véhicule</option>
                        <option v-for="car in carsWithoutMovement" :key="car.id" :value="car.id">{{ car.immatriculation }}</option>
                    </select>
                    <label class="form-label fw-semibold" for="movement-km">Kilométrage</label>
                    <div class="input-group"><input id="movement-km" v-model="movementKm" class="form-control" type="number" min="0" step="0.1" required><span class="input-group-text">km</span></div>
                    <div v-if="!carsWithoutMovement.length" class="form-text text-warning mt-2">Tous les véhicules disponibles ont déjà un relevé aujourd’hui.</div>
                </div>
                <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary" type="submit" :disabled="saving || !carsWithoutMovement.length"><span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>Enregistrer le relevé</button></div>
            </form></div>
        </div>
    </section>
</template>

<script setup>
import { Modal } from "bootstrap";
import axios from "axios";
import { computed, onMounted, ref } from "vue";
import { get } from "../../services/fetch.js";
import { useAlert } from "../../services/alert.js";
import Chart from "../../components/chart.vue";

const cars = ref([]);
const anomalies = ref([]);
const dailyMovements = ref([]);
const monthlyMovements = ref([]);
const loading = ref(true);
const saving = ref(false);
const savingCar = ref(null);
const search = ref("");
const newRegistration = ref("");
const description = ref("");
const movementCarId = ref("");
const movementKm = ref("");
const selectedCar = ref(null);
const errorMessage = ref("");
const availableCars = computed(() => cars.value.filter((car) => car.is_available));
const carsWithoutMovement = computed(() => availableCars.value.filter((car) => !dailyMovements.value.some((movement) => Number(movement.id) === car.id)));
const filteredCars = computed(() => cars.value.filter((car) => car.immatriculation.toLowerCase().includes(search.value.toLowerCase())));
const currentMonth = new Intl.DateTimeFormat("fr-FR", { month: "long", year: "numeric" }).format(new Date());
const monthlyChart = computed(() => {
    const monthlyTotals = new Map(
        monthlyMovements.value.map((movement) => [Number(movement.id), Number(movement.total) || 0]),
    );
    const orderedCars = [...cars.value].sort((a, b) => a.immatriculation.localeCompare(b.immatriculation));

    return {
        labels: orderedCars.map((car) => car.immatriculation),
        values: orderedCars.map((car) => monthlyTotals.get(Number(car.id)) || 0),
    };
});

function activeAnomaly(carId) {
    return anomalies.value.find((anomaly) => anomaly.car_id === carId && !anomaly.end_at);
}

async function loadFleet() {
    loading.value = true;
    errorMessage.value = "";
    try {
        const [carData, anomalyData, movementData] = await Promise.all([get("/cars"), get("/anomalies"), get("/moves")]);
        if (!Array.isArray(carData) || !Array.isArray(anomalyData) || !Array.isArray(movementData?.daily) || !Array.isArray(movementData?.monthly)) {
            throw new Error("Impossible de charger la flotte et les relevés kilométriques.");
        }
        cars.value = carData;
        anomalies.value = anomalyData;
        dailyMovements.value = movementData.daily;
        monthlyMovements.value = movementData.monthly;
    } catch (error) {
        errorMessage.value = error.message || "Le chargement de la flotte a échoué.";
    } finally {
        loading.value = false;
    }
}

async function createCar() {
    saving.value = true;
    try {
        await axios.post("/cars", { immatriculation: newRegistration.value.toUpperCase() });
        newRegistration.value = "";
        Modal.getOrCreateInstance(document.getElementById("addCar")).hide();
        useAlert("Véhicule ajouté", "Le véhicule a été ajouté à la flotte.", "success");
        await loadFleet();
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "L’ajout du véhicule a échoué.";
        useAlert("Erreur", errorMessage.value, "error");
    } finally {
        saving.value = false;
    }
}

async function reportAnomaly() {
    if (!selectedCar.value) return;
    saving.value = true;
    try {
        await axios.post("/anomalies", { car_id: selectedCar.value.id, description: description.value });
        description.value = "";
        Modal.getOrCreateInstance(document.getElementById("reportIssue")).hide();
        useAlert("Signalement enregistré", "Le véhicule est désormais hors service.", "success");
        await loadFleet();
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "Le signalement n’a pas pu être enregistré.";
        useAlert("Erreur", errorMessage.value, "error");
    } finally {
        saving.value = false;
    }
}

async function restoreCar(car, anomaly) {
    savingCar.value = car.id;
    try {
        await axios.put(`/cars/${car.id}`, { anomalie_id: anomaly.id });
        useAlert("Véhicule rétabli", "Le véhicule est de nouveau disponible.", "success");
        await loadFleet();
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "La remise en service a échoué.";
        useAlert("Erreur", errorMessage.value, "error");
    } finally {
        savingCar.value = null;
    }
}

async function createMovement() {
    if (!movementCarId.value || movementKm.value === "") return;
    saving.value = true;
    try {
        await axios.post("/moves", {
            car_id: movementCarId.value,
            date: localDate(),
            km: movementKm.value,
        });
        movementCarId.value = "";
        movementKm.value = "";
        Modal.getOrCreateInstance(document.getElementById("addMileage")).hide();
        useAlert("Relevé enregistré", "Le kilométrage du véhicule a été enregistré.", "success");
        await loadFleet();
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "L’enregistrement du kilométrage a échoué.";
        useAlert("Erreur", errorMessage.value, "error");
    } finally {
        saving.value = false;
    }
}

function localDate() {
    const now = new Date();
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-${String(now.getDate()).padStart(2, "0")}`;
}

onMounted(loadFleet);
</script>
