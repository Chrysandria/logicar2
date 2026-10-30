<template>
    <section>
        <div class="page-heading">
            <span class="badge rounded-pill text-bg-primary-subtle text-primary mb-2">ADMINISTRATION</span>
            <h1 class="h2 mb-1">Annuaire de l’équipe</h1>
            <p>Consultez les informations des employés de votre service.</p>
        </div>
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-4"><div class="surface-card stat-card d-flex align-items-center gap-3"><span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-people"></i></span><div><div class="small text-secondary">Employés référencés</div><div class="fs-4 fw-bold">{{ employees.length }}</div></div></div></div>
            <div class="col-sm-6 col-lg-4"><div class="surface-card stat-card d-flex align-items-center gap-3"><span class="stat-icon bg-success-subtle text-success"><i class="bi bi-diagram-3"></i></span><div><div class="small text-secondary">Services connus</div><div class="fs-4 fw-bold">{{ services.length }}</div></div></div></div>
        </div>
        <div class="surface-card overflow-hidden">
            <div class="p-3 p-md-4 border-bottom d-flex flex-column flex-md-row justify-content-between gap-3">
                <div><h2 class="h5 fw-bold mb-1">Employés</h2><p class="small text-secondary mb-0">Les informations sont limitées au périmètre de votre service.</p></div>
                <div class="input-group search-control"><span class="input-group-text bg-white"><i class="bi bi-search text-secondary"></i></span><input v-model.trim="search" class="form-control" type="search" aria-label="Rechercher un employé" placeholder="Nom ou matricule"></div>
            </div>
            <div v-if="loading" class="empty-state"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Chargement de l’annuaire...</div>
            <div v-else-if="!filteredEmployees.length" class="empty-state"><i class="bi bi-person-lines-fill"></i><strong>{{ employees.length ? "Aucun résultat" : "Aucun employé référencé" }}</strong><div class="small mt-1">Vérifiez votre recherche ou réessayez plus tard.</div></div>
            <div v-else class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Employé</th><th>Matricule</th><th class="d-none d-md-table-cell">Téléphone</th><th class="d-none d-lg-table-cell">Lieu de ramassage</th><th class="d-none d-xl-table-cell">Point ADM</th></tr></thead>
                    <tbody><tr v-for="employee in filteredEmployees" :key="employee.id">
                        <td><div class="d-flex align-items-center gap-2"><span class="avatar-circle text-primary bg-primary-subtle"><i class="bi bi-person"></i></span><strong>{{ employee.nom }}</strong></div></td>
                        <td><span class="badge text-bg-light">{{ employee.matricule }}</span></td>
                        <td class="d-none d-md-table-cell"><a class="link-secondary text-decoration-none" :href="`tel:${employee.telephone}`">{{ employee.telephone }}</a></td>
                        <td class="d-none d-lg-table-cell">{{ employee.lieu_ramassage }}</td>
                        <td class="d-none d-xl-table-cell">{{ employee.pnt_adm }}</td>
                    </tr></tbody>
                </table>
            </div>
        </div>
        <div v-if="errorMessage" class="alert alert-danger mt-3">{{ errorMessage }}</div>
    </section>
</template>

<script setup>
import { computed, onMounted, ref } from "vue";
import { get } from "../../services/fetch.js";

const employees = ref([]);
const services = ref([]);
const search = ref("");
const loading = ref(true);
const errorMessage = ref("");
const filteredEmployees = computed(() => {
    const query = search.value.toLowerCase();
    return employees.value.filter((employee) => `${employee.nom} ${employee.matricule}`.toLowerCase().includes(query));
});

onMounted(async () => {
    loading.value = true;
    try {
        const [employeeData, serviceData] = await Promise.all([get("/emps"), get("/services")]);
        if (!Array.isArray(employeeData) || !Array.isArray(serviceData)) {
            throw new Error("Impossible de charger l’annuaire.");
        }
        employees.value = employeeData;
        services.value = serviceData;
    } catch (error) {
        errorMessage.value = error.message || "Le chargement de l’annuaire a échoué.";
    } finally {
        loading.value = false;
    }
});
</script>
