<template>
    <div class="container-fluid p-0">
        <div class="page-heading d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
            <div>
                <span class="badge rounded-pill bg-primary-subtle text-primary mb-2">ESPACE SERVICE</span>
                <h1 class="h2 mb-1">Préparer un planning</h1>
                <p>Ajoutez les employés attendus et définissez leur heure de prise de service.</p>
            </div>
            <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                <div class="surface-card px-3 py-2 d-flex align-items-center gap-2">
                    <span class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-calendar-check"></i></span>
                    <div><span class="small text-secondary d-block">Date du planning</span><strong>{{ fd }}</strong></div>
                </div>
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#newPlanning">
                    <i class="bi bi-plus-lg me-2"></i>Nouveau planning
                </button>
            </div>
        </div>

        <div v-if="loading" class="surface-card empty-state">
            <span class="spinner-border spinner-border-sm text-primary me-2"></span>Chargement du planning...
        </div>
        <div v-else-if="plannings.length === 0" class="surface-card empty-state">
            <i class="bi bi-calendar2-x"></i>
            <strong>Aucun planning enregistré pour le moment</strong>
            <div class="small mt-1">Créez un planning pour commencer à organiser votre équipe.</div>
        </div>

        <div
            class="surface-card table-responsive mb-3"
            v-for="(p, index) in plannings"
            :key="index"
        >
            <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                <strong class="small text-secondary">Équipe planifiée · {{ p.planning_items.length }} employés</strong>
                <div class="badge rounded-pill text-bg-primary">
                    <i class="bi bi-clock me-2"></i>
                    {{ p.hps.substring(0, 5) }}
                </div>
            </div>
            <table class="table table-hover align-middle small mb-0">
                <colgroup>
                    <col style="width: 10%" />
                    <col style="width: 30%" />
                    <col style="width: 15%" />
                    <col style="width: 15%" />
                    <col style="width: 15%" />
                    <col style="width: 15%" />
                </colgroup>
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom</th>
                        <th>Téléphone</th>
                        <th>Lieu ramassage</th>
                        <th>Point ADM</th>
                        <th>Point décalé</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, i) in p.planning_items" :key="i">
                        <td>{{ item.employe.matricule }}</td>
                        <td>{{ item.employe.nom }}</td>
                        <td>{{ item.employe.telephone }}</td>
                        <td>{{ item.employe.lieu_ramassage }}</td>
                        <td>{{ item.employe.pnt_adm }}</td>
                        <td>{{ item.employe.pnt_dec }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div
        class="modal fade"
        id="newPlanning"
        tabindex="-1"
        aria-hidden="true"
        aria-labelledby="title"
    >
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="title">
                        <i class="bi bi-plus-lg text-primary fw-bold me-2"></i>
                        Nouveau planning
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="fermer"
                    ></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div
                            class="col-md-6 d-flex align-items-center mb-3 mb-md-0"
                        >
                            <u class="fw-semibold">Date:</u>
                            {{ fd }}
                        </div>
                        <div
                            class="col-md-6 d-flex justify-content-end flex-column flex-md-row"
                        >
                            <div class="input-group">
                                <span class="input-group-text fw-semibold small"
                                    >Heure de prise de service</span
                                >
                                <input
                                    type="time"
                                    name="hps"
                                    id="hps"
                                    v-model="hps"
                                    class="form-control"
                                />
                            </div>
                        </div>
                    </div>
                    <p class="badge mb-0 bg-primary text-white text-wrap my-3">
                        Saisissez le matricule d'un employé, puis cliquez sur la
                        touche "Entrée"
                    </p>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr class="border-bottom">
                                    <th>Matricule</th>
                                    <th>Nom</th>
                                    <th>Telephone</th>
                                    <th>Lieu de ramassage</th>
                                    <th>Point ADM</th>
                                    <th>Point décalé</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="border-bottom small align-middle"
                                    v-for="(item, index) in newPlanning"
                                    :key="index"
                                >
                                    <td>
                                        <input
                                            type="text"
                                            :ref="(el) => (inputs[index] = el)"
                                            @keydown.enter.prevent="
                                                autoComplete(index)
                                            "
                                            v-model="item.matricule"
                                            inputmode="numeric"
                                            placeholder="Matricule"
                                            class="form-control form-control-sm"
                                        />
                                    </td>
                                    <td>
                                        {{ item.nom }}
                                    </td>
                                    <td>
                                        {{ item.telephone }}
                                    </td>
                                    <td>
                                        {{ item.lieu_ramassage }}
                                    </td>
                                    <td>
                                        {{ item.pnt_adm }}
                                    </td>
                                    <td>
                                        {{ item.pnt_dec }}
                                    </td>
                                    <td class="text-end">
                                        <button v-if="item.id" class="btn btn-sm btn-outline-secondary" type="button" aria-label="Retirer cet employé" @click="clearEmployee(index)">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-if="errorMessage" class="alert alert-danger py-2 mb-0" role="alert">{{ errorMessage }}</div>
                </div>
                <div class="modal-footer">
                    <button
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                        @click="reset"
                    >
                        Annuler
                    </button>
                    <button class="btn btn-primary" type="button" :disabled="saving" @click="save">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import { onMounted, ref, nextTick, computed, watch } from "vue";
import { dateToFr } from "../../services/dayManager";
import { useFetch, get } from "../../services/fetch";
import { useRoute } from "vue-router";

const employes = ref([]);
const plannings = ref([]);
const errorMessage = ref("");
const newPlanning = ref([
    {
        matricule: "",
        nom: "",
        telephone: "",
        lieu_ramassage: "",
        pnt_adm: "",
        pnt_dec: "",
    },
]);
const route = useRoute();
const date = computed(() => String(route.params.date || ""));
const fd = computed(() => dateToFr(new Date(`${date.value}T12:00:00`)));
const inputs = ref([]);
const hps = ref("");
const saving = ref(false);
const loading = ref(true);

async function autoComplete(index) {
    const isUniq = verify();

    if (!isUniq) return;

    const mtrl = newPlanning.value[index].matricule.trim();

    const emp = employes.value.find((e) => e.matricule === mtrl);

    if (emp) {
        let trigger = await get("/trigger", {
            dps: date.value,
            employe_id: emp.id,
        });

        if (!trigger) {
            errorMessage.value = "La vérification du planning a échoué. Réessayez.";
            return;
        }
        if (trigger?.found) {
            errorMessage.value = `Le matricule ${mtrl} est deja inclus dans le planning de ${trigger.planning.hps.substring(0, 5)}.`;
            return;
        }

        newPlanning.value[index] = emp;
        errorMessage.value = "";
    } else {
        errorMessage.value = `Aucun employé de votre service ne possède le matricule ${mtrl}.`;
    }

    increment();
}

function verify() {
    for (let i = 0; i < newPlanning.value.length; i++) {
        let count = 0;
        for (let j = 0; j < newPlanning.value.length; j++) {
            if (
                newPlanning.value[i].matricule ===
                    newPlanning.value[j].matricule &&
                newPlanning.value[i].matricule !== ""
            )
                count++;
            if (count > 1) {
                errorMessage.value = `Matricule ${newPlanning.value[i].matricule} dupliqué.`;
                return false;
            }
        }
    }
    errorMessage.value = "";
    return true;
}

async function increment() {
    const last = newPlanning.value.length - 1;

    if (newPlanning.value[last]?.id) {
        newPlanning.value.push({
            matricule: "",
            nom: "",
            telephone: "",
            lieu_ramassage: "",
            pnt_adm: "",
            pnt_dec: "",
        });
    }
    await nextTick();

    inputs.value[newPlanning.value.length - 1]?.focus();
}

async function save() {
    if (saving.value) return;
    if (hps.value === "") {
        errorMessage.value = "Veuillez saisir l'heure de prise de service.";
        return;
    }

    errorMessage.value = "";

    if (!newPlanning.value.some((employee) => employee.id)) {
        errorMessage.value = "Veuillez saisir au moins un employé.";
        return;
    }

    const employeesToSave = newPlanning.value.filter((employee) => employee.id);

    let request = {
        method: "post",
        url: "/plannings",
        data: {
            dps: date.value,
            hps: hps.value,
            list: employeesToSave,
        },
    };

    saving.value = true;
    try {
        const ok = await useFetch(request);
        if (ok) {
            await fetchPlanning();
            reset();
        }
    } finally {
        saving.value = false;
    }
}

function clearEmployee(index) {
    newPlanning.value[index] = {
        matricule: "",
        nom: "",
        telephone: "",
        lieu_ramassage: "",
        pnt_adm: "",
        pnt_dec: "",
    };
}

function reset() {
    errorMessage.value = "";
    hps.value = "";
    newPlanning.value = [
        {
            matricule: "",
            nom: "",
            telephone: "",
            lieu_ramassage: "",
            pnt_adm: "",
            pnt_dec: "",
        },
    ];
}

async function fetchPlanning(){
    if (!date.value) {
        return;
    }

    loading.value = true;
    try {
        const data = await get("/plannings", {
            dps: date.value,
        });
        plannings.value = Array.isArray(data) ? data : [];
    } finally {
        loading.value = false;
    }
}

onMounted(async () => {
    const employeeData = await get("/emps");
    employes.value = Array.isArray(employeeData) ? employeeData : [];

    await fetchPlanning();

});

watch(date, (newDate) => {
    if (newDate) {
        fetchPlanning();
    }
});
</script>
