<template>
    <section>
        <div class="page-heading d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div>
                <span class="badge rounded-pill bg-primary-subtle text-primary mb-2">ESPACE SERVICE</span>
                <h1 class="h2 mb-1">Planning du jour</h1>
                <p>Consultez les prises de service et les équipes prévues aujourd’hui.</p>
            </div>
            <div class="surface-card px-3 py-2 d-flex align-items-center gap-2">
                <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-calendar-check"></i></span>
                <div><span class="small text-secondary d-block">Aujourd’hui</span><strong>{{ frenchToday }}</strong></div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-4">
                <div class="surface-card stat-card d-flex align-items-center gap-3">
                    <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-clock"></i></span>
                    <div><div class="small text-secondary">Prises de service</div><div class="fs-4 fw-bold">{{ plannings.length }}</div></div>
                </div>
            </div>
            <div class="col-6 col-lg-4">
                <div class="surface-card stat-card d-flex align-items-center gap-3">
                    <span class="stat-icon bg-success-subtle text-success"><i class="bi bi-people"></i></span>
                    <div><div class="small text-secondary">Employés planifiés</div><div class="fs-4 fw-bold">{{ employeeCount }}</div></div>
                </div>
            </div>
        </div>

        <div v-if="loading" class="surface-card empty-state">
            <span class="spinner-border spinner-border-sm text-primary me-2"></span>Chargement du planning...
        </div>
        <div v-else-if="!plannings.length" class="surface-card empty-state">
            <i class="bi bi-calendar2-x"></i>
            <strong>Aucun planning aujourd’hui</strong>
            <div class="small mt-1">Les prises de service du jour apparaîtront ici.</div>
        </div>

        <article v-for="plan in plannings" :key="plan.id" class="surface-card overflow-hidden mb-3">
            <div class="p-3 p-md-4 border-bottom d-flex align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-clock"></i></span>
                    <div><span class="small text-secondary">Prise de service</span><h2 class="h5 fw-bold mb-0">{{ plan.hps?.slice(0, 5) }}</h2></div>
                </div>
                <span class="badge rounded-pill text-bg-light">{{ plan.planning_items?.length || 0 }} employés</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr><th>Matricule</th><th>Nom</th><th class="d-none d-md-table-cell">Téléphone</th><th class="d-none d-lg-table-cell">Lieu de ramassage</th><th>Statut</th></tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in plan.planning_items || []" :key="item.id">
                            <td><span class="badge text-bg-light">{{ item.employe?.matricule }}</span></td>
                            <td class="fw-semibold">{{ item.employe?.nom }}</td>
                            <td class="d-none d-md-table-cell">{{ item.employe?.telephone }}</td>
                            <td class="d-none d-lg-table-cell">{{ item.employe?.lieu_ramassage }}</td>
                            <td><span class="badge rounded-pill" :class="item.ok ? 'text-bg-success' : 'text-bg-danger'">{{ item.ok ? "Prévu" : "Non récupéré" }}</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </article>

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
const today = new Date();
const todayIso = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, "0")}-${String(today.getDate()).padStart(2, "0")}`;
const frenchToday = dateToFr(today);
const employeeCount = computed(() => plannings.value.reduce((sum, planning) => sum + (planning.planning_items?.length || 0), 0));

onMounted(async () => {
    try {
        const { data } = await axios.get("/plannings", { params: { dps: todayIso } });
        if (!Array.isArray(data)) {
            throw new Error("Impossible de charger le planning du jour.");
        }
        plannings.value = data;
    } catch (error) {
        errorMessage.value = error.response?.data?.message || error.message || "Le chargement du planning a échoué.";
    } finally {
        loading.value = false;
    }
});
</script>
