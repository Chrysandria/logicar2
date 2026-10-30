<template>
    <section>
        <div class="page-heading d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div>
                <span class="badge rounded-pill text-bg-primary-subtle text-primary mb-2">EXPLOITATION</span>
                <h1 class="h2 mb-1">{{ isToday ? "Départs du jour" : "Préparer les départs" }}</h1>
                <p>Organisez les véhicules et les passagers par heure de prise de service.</p>
            </div>
            <div class="surface-card px-3 py-2 d-flex align-items-center gap-2">
                <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-calendar-check"></i></span>
                <div><span class="small text-secondary d-block">Date des départs</span><strong>{{ frenchDate }}</strong></div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-4">
                <div class="surface-card stat-card d-flex align-items-center gap-3">
                    <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-clock-history"></i></span>
                    <div><div class="small text-secondary">Horaires prévus</div><div class="fs-4 fw-bold">{{ plans.length }}</div></div>
                </div>
            </div>
            <div class="col-6 col-xl-4">
                <div class="surface-card stat-card d-flex align-items-center gap-3">
                    <span class="stat-icon bg-success-subtle text-success"><i class="bi bi-people"></i></span>
                    <div><div class="small text-secondary">Passagers planifiés</div><div class="fs-4 fw-bold">{{ passengerCount }}</div></div>
                </div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="surface-card stat-card d-flex align-items-center gap-3">
                    <span class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-bus-front"></i></span>
                    <div><div class="small text-secondary">Véhicules disponibles</div><div class="fs-4 fw-bold">{{ availableCars.length }}</div></div>
                </div>
            </div>
        </div>

        <div v-if="loading" class="surface-card empty-state"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Chargement des départs...</div>
        <div v-else-if="!plans.length" class="surface-card empty-state">
            <i class="bi bi-calendar2-x"></i>
            <strong>Aucun planning prévu</strong>
            <div class="small mt-1">Les plannings enregistrés pour cette date apparaîtront ici.</div>
        </div>

        <article v-for="plan in plans" :key="plan.id" class="surface-card mb-3 overflow-hidden">
            <div class="p-3 p-lg-4 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-clock"></i></span>
                    <div><div class="small text-secondary">Prise de service</div><h2 class="h4 mb-0">{{ plan.hps?.slice(0, 5) }}</h2></div>
                    <span class="badge rounded-pill text-bg-light">{{ plan.planning_items?.length || 0 }} passagers</span>
                </div>
                <span v-if="dispatchedPlans.has(plan.id)" class="badge text-bg-success align-self-start align-self-md-center"><i class="bi bi-check-circle me-1"></i>Départ enregistré</span>
            </div>
            <div class="p-3 p-lg-4">
                <div class="row g-3 align-items-end mb-3">
                    <div class="col-12 col-md-5">
                        <label class="form-label fw-semibold" :for="`route-${plan.id}`">Itinéraire</label>
                        <select :id="`route-${plan.id}`" v-model="forms[plan.id].trajetId" class="form-select">
                            <option value="">Choisir un itinéraire</option>
                            <option v-for="trip in trips" :key="trip.id" :value="trip.id">{{ trip.designation }}</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-5">
                        <label class="form-label fw-semibold" :for="`car-${plan.id}`">Véhicule</label>
                        <select :id="`car-${plan.id}`" v-model="forms[plan.id].carId" class="form-select">
                            <option value="">Choisir un véhicule disponible</option>
                            <option v-for="car in availableCars" :key="car.id" :value="car.id">{{ car.immatriculation }}</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2 d-grid">
                        <button class="btn btn-primary" :disabled="savingPlan === plan.id || dispatchedPlans.has(plan.id)" @click="dispatch(plan)">
                            <span v-if="savingPlan === plan.id" class="spinner-border spinner-border-sm me-1"></span>
                            <i v-else class="bi bi-send me-1"></i>Envoyer
                        </button>
                    </div>
                </div>
                <div v-if="!availableCars.length" class="alert alert-warning py-2 small"><i class="bi bi-exclamation-circle me-2"></i>Aucun véhicule disponible. Consultez la flotte avant d’enregistrer ce départ.</div>
                <div class="small text-secondary fw-semibold mb-2">Passagers prévus <span class="fw-normal">— cochez les absences</span></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Matricule</th><th>Nom</th><th class="d-none d-md-table-cell">Lieu de ramassage</th><th class="d-none d-lg-table-cell">Téléphone</th><th class="text-end">Absent</th></tr></thead>
                        <tbody>
                            <tr v-for="item in plan.planning_items || []" :key="item.id">
                                <td><span class="badge text-bg-light">{{ item.employe?.matricule }}</span></td>
                                <td class="fw-semibold">{{ item.employe?.nom }}</td>
                                <td class="d-none d-md-table-cell">{{ item.employe?.lieu_ramassage || "—" }}</td>
                                <td class="d-none d-lg-table-cell">{{ item.employe?.telephone || "—" }}</td>
                                <td class="text-end"><input v-model="forms[plan.id].absentIds" class="form-check-input" type="checkbox" :value="item.id" :aria-label="`Signaler ${item.employe?.nom} absent`"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </article>
        <div v-if="loadError" class="alert alert-danger mt-3">{{ loadError }}</div>
    </section>
</template>

<script setup>
import axios from "axios";
import { computed, onMounted, reactive, ref, watch } from "vue";
import { useRoute } from "vue-router";
import { dateToFr } from "../services/dayManager.js";
import { get } from "../services/fetch.js";
import { useAlert } from "../services/alert.js";

const props = defineProps({ date: { type: String, default: "" } });
const route = useRoute();
const plans = ref([]);
const cars = ref([]);
const trips = ref([]);
const loading = ref(true);
const savingPlan = ref(null);
const loadError = ref("");
const forms = reactive({});
const dispatchedPlans = ref(new Set());
const expeditionIds = reactive({});
const dateIso = computed(() => props.date || String(route.params.date || localDate()));
const isToday = computed(() => dateIso.value === localDate());
const frenchDate = computed(() => dateToFr(new Date(`${dateIso.value}T12:00:00`)));
const availableCars = computed(() => cars.value.filter((car) => car.is_available));
const passengerCount = computed(() => plans.value.reduce((sum, plan) => sum + (plan.planning_items?.length || 0), 0));

function localDate() {
    const now = new Date();
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-${String(now.getDate()).padStart(2, "0")}`;
}

async function loadBoard() {
    loading.value = true;
    loadError.value = "";
    try {
        const [planningData, carData, tripData] = await Promise.all([
            get("/plannings", { dps: dateIso.value }),
            get("/cars"),
            get("/trajets"),
        ]);
        if (!Array.isArray(planningData) || !Array.isArray(carData) || !Array.isArray(tripData)) {
            throw new Error("Impossible de charger toutes les données du dispatch.");
        }
        plans.value = planningData;
        cars.value = carData;
        trips.value = tripData;
        for (const plan of plans.value) {
            if (!forms[plan.id]) {
                forms[plan.id] = { trajetId: "", carId: "", absentIds: [] };
            }
        }
    } catch (error) {
        loadError.value = error.message || "Le chargement des départs a échoué.";
    } finally {
        loading.value = false;
    }
}

async function dispatch(plan) {
    const form = forms[plan.id];
    if (!form.trajetId || !form.carId) {
        useAlert("Informations manquantes", "Choisissez un itinéraire et un véhicule avant d’enregistrer le départ.", "warning");
        return;
    }
    if (!plan.planning_items?.length) {
        useAlert("Planning vide", "Ce départ ne contient aucun passager à affecter.", "warning");
        return;
    }
    if (plan.planning_items.length > 20) {
        useAlert("Limite atteinte", "Un départ ne peut pas contenir plus de 20 passagers.", "warning");
        return;
    }
    savingPlan.value = plan.id;
    try {
        if (!expeditionIds[plan.id]) {
            const { data: expedition } = await axios.post("/expeditions", {
                date: dateIso.value,
                hdr: plan.hps.slice(0, 5),
                trajet_id: form.trajetId,
                list: plan.planning_items.map((item) => ({ planning_item_id: item.id })),
            });
            if (!expedition?.id) {
                throw new Error("Le départ a été créé, mais son identifiant est introuvable. Actualisez et vérifiez les données.");
            }
            expeditionIds[plan.id] = expedition.id;
        }
        await axios.put(`/expeditions/${expeditionIds[plan.id]}`, {
            car_id: form.carId,
            list: form.absentIds.map((id) => ({ planning_item_id: id })),
        });
        dispatchedPlans.value = new Set([...dispatchedPlans.value, plan.id]);
        useAlert("Départ enregistré", `Le départ de ${plan.hps.slice(0, 5)} a été affecté au véhicule choisi.`, "success");
    } catch (error) {
        loadError.value = error.response?.data?.message || (expeditionIds[plan.id]
            ? "Le départ existe déjà mais son affectation a échoué. Corrigez les informations puis réessayez."
            : "L’enregistrement du départ a échoué. Vérifiez la flotte et réessayez.");
        useAlert("Erreur", loadError.value, "error");
    } finally {
        savingPlan.value = null;
    }
}

onMounted(loadBoard);
watch(dateIso, loadBoard);
</script>
