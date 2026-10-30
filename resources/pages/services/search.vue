<template>
    <section>
        <div class="page-heading d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div><span class="badge rounded-pill bg-primary-subtle text-primary mb-2">ESPACE SERVICE</span><h1 class="h2 mb-1">{{ searchMode === "date" ? "Planning du service" : "Historique d’un employé" }}</h1><p>Consultez les plannings par date ou retracez le parcours d’un employé.</p></div>
            <div v-if="searchMode === 'date'" class="surface-card px-3 py-2 d-flex align-items-center gap-2"><span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-calendar-check"></i></span><div><span class="small text-secondary d-block">Date consultée</span><strong>{{ frenchSelectedDate }}</strong></div></div>
        </div>
        <div class="surface-card p-3 p-md-4 mb-4">
            <ul class="nav nav-pills gap-2 mb-3">
                <li class="nav-item">
                    <button class="nav-link" :class="{ active: searchMode === 'date' }" type="button" @click="searchMode = 'date'">
                        <i class="bi bi-calendar3 me-2"></i>Planning par date
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" :class="{ active: searchMode === 'employee' }" type="button" @click="searchMode = 'employee'">
                        <i class="bi bi-person-lines-fill me-2"></i>Historique employé
                    </button>
                </li>
            </ul>
            <form v-if="searchMode === 'date'" class="row g-3 align-items-end" @submit.prevent="searchPlannings">
                <div class="col-12 col-sm-7 col-lg-5">
                    <label class="form-label fw-semibold" for="planning-date">Date du planning</label>
                    <input id="planning-date" v-model="selectedDate" class="form-control" type="date" required>
                </div>
                <div class="col-12 col-sm-auto">
                    <button class="btn btn-primary" type="submit" :disabled="searching">
                        <span v-if="searching" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-search me-2"></i>Rechercher
                    </button>
                </div>
            </form>
            <form v-else class="row g-3 align-items-end" @submit.prevent="searchEmployeeHistory">
                <div class="col-12 col-md-5">
                    <label class="form-label fw-semibold" for="employee-filter">Rechercher un employé</label>
                    <input id="employee-filter" v-model.trim="employeeSearch" class="form-control" type="search" placeholder="Nom ou matricule">
                </div>
                <div class="col-12 col-md-5">
                    <label class="form-label fw-semibold" for="history-employee">Employé</label>
                    <select id="history-employee" v-model="selectedEmployeeId" class="form-select" required @change="clearEmployeeHistory">
                        <option value="">Sélectionner un employé</option>
                        <option v-for="employee in filteredEmployees" :key="employee.id" :value="employee.id">
                            {{ employee.matricule }} — {{ employee.nom }}
                        </option>
                    </select>
                </div>
                <div class="col-12 col-md-auto">
                    <button class="btn btn-primary" type="submit" :disabled="searching || !selectedEmployeeId">
                        <span v-if="searching" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-search me-2"></i>Voir l’historique
                    </button>
                </div>
            </form>
        </div>
        <div v-if="searchMode === 'date'" class="row g-3 mb-4">
            <div class="col-6 col-lg-4"><div class="surface-card stat-card d-flex align-items-center gap-3"><span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-clock"></i></span><div><div class="small text-secondary">Prises de service</div><div class="fs-4 fw-bold">{{ plannings.length }}</div></div></div></div>
            <div class="col-6 col-lg-4"><div class="surface-card stat-card d-flex align-items-center gap-3"><span class="stat-icon bg-success-subtle text-success"><i class="bi bi-people"></i></span><div><div class="small text-secondary">Employés planifiés</div><div class="fs-4 fw-bold">{{ employeeCount }}</div></div></div></div>
        </div>
        <div v-if="searchMode === 'date' && loading" class="surface-card empty-state"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Chargement du planning...</div>
        <div v-else-if="searchMode === 'date' && !plannings.length" class="surface-card empty-state"><i class="bi bi-calendar2-x"></i><strong>Aucun planning pour cette date</strong><div class="small mt-1">Essayez une autre date ou créez le planning correspondant.</div></div>
        <article v-for="(plan, index) in plannings" :key="plan.id" v-show="searchMode === 'date'" class="surface-card overflow-hidden mb-3">
            <div class="p-3 p-md-4 border-bottom d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-3"><span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-clock"></i></span><div><span class="small text-secondary">Prise de service</span><h2 class="h5 fw-bold mb-0">{{ plan.hps?.slice(0, 5) }}</h2></div></div>
                <span class="badge rounded-pill text-bg-light">{{ plan.planning_items?.length || 0 }} employés</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Matricule</th><th>Nom</th><th class="d-none d-md-table-cell">Téléphone</th><th class="d-none d-lg-table-cell">Lieu de ramassage</th><th>Statut</th></tr></thead>
                    <tbody><tr v-for="item in plan.planning_items || []" :key="item.id">
                        <td><span class="badge text-bg-light">{{ item.employe?.matricule }}</span></td>
                        <td class="fw-semibold">{{ item.employe?.nom }}</td>
                        <td class="d-none d-md-table-cell">{{ item.employe?.telephone }}</td>
                        <td class="d-none d-lg-table-cell">{{ item.employe?.lieu_ramassage }}</td>
                        <td><span v-if="item.ok" class="badge rounded-pill text-bg-success">Prévu</span><span v-else class="badge rounded-pill text-bg-danger">Non récupéré</span></td>
                    </tr></tbody>
                </table>
            </div>
        </article>
        <div v-if="searchMode === 'employee' && historyLoading" class="surface-card empty-state"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Chargement de l’historique...</div>
        <div v-else-if="searchMode === 'employee' && historySearched && !history.length" class="surface-card empty-state"><i class="bi bi-clock-history"></i><strong>Aucun planning trouvé pour cet employé</strong><div class="small mt-1">Vérifiez l’employé sélectionné ou réessayez plus tard.</div></div>
        <div v-else-if="searchMode === 'employee' && history.length" class="surface-card overflow-hidden">
            <div class="p-3 p-md-4 border-bottom d-flex justify-content-between align-items-center gap-3">
                <div><h2 class="h5 fw-bold mb-1">{{ selectedEmployee?.nom }}</h2><p class="small text-secondary mb-0">Matricule {{ selectedEmployee?.matricule }} · {{ historyTotal }} planning(s)</p></div>
                <span class="badge rounded-pill text-bg-light">Historique</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Date</th><th>Prise de service</th><th>Statut</th><th class="d-none d-md-table-cell">Remarque</th></tr></thead>
                    <tbody>
                        <tr v-for="item in history" :key="item.id">
                            <td>{{ formatDate(item.planning?.dps) }}</td>
                            <td><span class="badge text-bg-light">{{ item.planning?.hps?.slice(0, 5) || "—" }}</span></td>
                            <td><span class="badge rounded-pill" :class="item.ok ? 'text-bg-success' : 'text-bg-danger'">{{ item.ok ? "Prévu" : "Non récupéré" }}</span></td>
                            <td class="d-none d-md-table-cell text-secondary">{{ item.remarque || "—" }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <span class="small text-secondary">Page {{ historyPage }} sur {{ historyLastPage }}</span>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm" type="button" :disabled="historyPage <= 1 || searching" @click="loadEmployeeHistory(historyPage - 1)">Précédent</button>
                    <button class="btn btn-outline-primary btn-sm" type="button" :disabled="historyPage >= historyLastPage || searching" @click="loadEmployeeHistory(historyPage + 1)">Suivant</button>
                </div>
            </div>
        </div>
        <div v-if="errorMessage" class="alert alert-danger">{{ errorMessage }}</div>
    </section>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import axios from "axios";
import { dateToFr } from "../../services/dayManager.js";

const plannings = ref([]);
const loading = ref(true);
const errorMessage = ref("");
const now = new Date();
const todayIso = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, "0")}-${String(now.getDate()).padStart(2, "0")}`;
const selectedDate = ref(todayIso);
const searchMode = ref("date");
const searching = ref(false);
const employees = ref([]);
const employeeSearch = ref("");
const selectedEmployeeId = ref("");
const history = ref([]);
const historyLoading = ref(false);
const historySearched = ref(false);
const historyPage = ref(1);
const historyLastPage = ref(1);
const historyTotal = ref(0);
const frenchSelectedDate = computed(() => selectedDate.value ? dateToFr(new Date(`${selectedDate.value}T12:00:00`)) : "");
const employeeCount = computed(() => plannings.value.reduce((sum, plan) => sum + (plan.planning_items?.length || 0), 0));
const filteredEmployees = computed(() => {
    const query = employeeSearch.value.toLocaleLowerCase("fr");
    return employees.value.filter((employee) => `${employee.nom} ${employee.matricule}`.toLocaleLowerCase("fr").includes(query));
});
const selectedEmployee = computed(() => employees.value.find((employee) => Number(employee.id) === Number(selectedEmployeeId.value)));

function formatDate(date) {
    return date ? dateToFr(new Date(`${date}T12:00:00`)) : "—";
}

async function loadPlannings(date) {
    loading.value = true;
    errorMessage.value = "";
    try {
        const { data } = await axios.get("/plannings", { params: { dps: date } });
        if (!Array.isArray(data)) {
            throw new Error("Impossible de charger le planning.");
        }
        plannings.value = data;
    } catch (error) {
        errorMessage.value = error.response?.data?.message || error.message || "Le chargement du planning a échoué.";
        plannings.value = [];
    } finally {
        loading.value = false;
    }
}

async function searchPlannings() {
    if (!selectedDate.value || searching.value) {
        return;
    }
    searching.value = true;
    try {
        await loadPlannings(selectedDate.value);
    } finally {
        searching.value = false;
    }
}

async function loadEmployeeHistory(page = 1) {
    if (!selectedEmployeeId.value) {
        return;
    }
    searching.value = true;
    historyLoading.value = true;
    errorMessage.value = "";
    try {
        const { data } = await axios.get("/planning-history", {
            params: { employee_id: selectedEmployeeId.value, page },
        });
        history.value = data.data;
        historyPage.value = data.current_page;
        historyLastPage.value = data.last_page;
        historyTotal.value = data.total;
        historySearched.value = true;
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "Le chargement de l’historique a échoué.";
        history.value = [];
    } finally {
        historyLoading.value = false;
        searching.value = false;
    }
}

async function searchEmployeeHistory() {
    historySearched.value = false;
    await loadEmployeeHistory(1);
}

function clearEmployeeHistory() {
    history.value = [];
    historySearched.value = false;
    historyTotal.value = 0;
}

onMounted(async () => {
    try {
        const [planningResponse, employeeResponse] = await Promise.all([
            axios.get("/plannings", { params: { dps: todayIso } }),
            axios.get("/emps"),
        ]);
        if (!Array.isArray(planningResponse.data) || !Array.isArray(employeeResponse.data)) {
            throw new Error("Impossible de charger les plannings et les employés du service.");
        }
        plannings.value = planningResponse.data;
        employees.value = employeeResponse.data;
    } catch (error) {
        errorMessage.value = error.response?.data?.message || error.message || "Le chargement des données du service a échoué.";
    } finally {
        loading.value = false;
    }
});
</script>
