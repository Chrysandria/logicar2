<template>
    <div class="container p-2">
        <div
            class="d-flex flex-column flex-md-row justify-content-end mb-3 mb-md-5"
        >
            <div class="card p-2">
                <div class="d-flex align-items-center gap-2">
                    <span
                        class="bg-primary-subtle text-primary p-2 rounded fs-3"
                    >
                        <i class="bi bi-calendar-check"></i>
                    </span>
                    <p class="mb-0">
                        <span class="small">Planning du:</span><br />
                        <span class="fs-5">{{ fd }}</span>
                    </p>
                </div>
            </div>
        </div>
        <div class="row mb-5">
            <div class="col d-flex flex-column flex-md-row justify-content-end">
                <button
                    class="btn btn-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#newPlanning"
                >
                    <i class="bi bi-plus-lg me-2"></i>
                    Nouveau
                </button>
            </div>
        </div>

        <div class="mt-5 pt-5 text-center" v-if="plannings.length === 0" style="opacity: 0.7;">
            <span class="display-1">
                ❌
            </span> <br>
            <span class="small">
                Aucun planning enregistré pour le moment.
            </span>
        </div>

        <div
            class="table-responsive mb-3 p-3"
            v-for="(p, index) in plannings"
            :key="index"
            :class="index % 2 === 0 ? 'bg-light' : 'bg-white'"
        >
            <div class="d-flex justify-content-end mb-3">
                <div class="badge text-light bg-primary">
                    <i class="bi bi-clock me-2"></i>
                    {{ p.hps.substring(0, 5) }}
                </div>
            </div>
            <table
                class="table table-fixed align-middle small"
                :class="index % 2 === 0 ? 'table-light' : 'table-white'"
            >
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
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div
                        class="d-flex flex-column flex-md-row justify-content-end"
                    >
                        <div
                            class="btn bg-danger-subtle border-danger disabled text-danger badge"
                            v-if="errorMessage"
                        >
                            {{ errorMessage }}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                        @click="reset"
                    >
                        Annuler
                    </button>
                    <button class="btn btn-primary" @click="save">
                        Enregistré
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
        name: "",
        telephone: "",
        lieu_ramassage: "",
        pnt_adm: "",
        pnt_dec: "",
    },
]);
const route = useRoute();
const date = computed(() => route.params.date);
const fd = computed(() => dateToFr(date.value));
const inputs = ref([]);
const hps = ref("");

async function autoComplete(index) {
    let isUniq = verify();

    if (!isUniq) return;

    const mtrl = newPlanning.value[index].matricule;

    const emp = employes.value.find((e) => e.matricule === mtrl);

    if (emp) {
        let trigger = await get("/trigger", {
            dps: date.value,
            employe_id: emp.id,
        });

        console.log(trigger);

        if (trigger.found) {
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

    if (newPlanning.value[last].name !== "") {
        newPlanning.value.push({
            matricule: "",
            name: "",
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
    if (hps.value === "") {
        errorMessage.value = "Veuillez saisir l'heure de prise de service.";
        return;
    }

    errorMessage.value = "";

    if (
        newPlanning.value.length === 0 ||
        (newPlanning.value.length === 1 && !newPlanning.value[0].id)
    ) {
        errorMessage.value = "Veuillez saisir au moins un employé.";
        return;
    }

    newPlanning.value = newPlanning.value.filter((emp) => emp.id);

    let request = {
        method: "post",
        url: "/plannings",
        data: {
            dps: date.value,
            hps: hps.value,
            list: newPlanning.value,
        },
    };

    let ok = await useFetch(request);

    if (ok) {
        await fetchPlanning();
        reset();
    };
}

function reset() {
    errorMessage.value = "";
    hps.value = "";
    newPlanning.value = [
        {
            matricule: "",
            name: "",
            telephone: "",
            lieu_ramassage: "",
            pnt_adm: "",
            pnt_dec: "",
        },
    ];
}

async function fetchPlanning(){
    plannings.value = await get("/plannings", {
        dps: date.value,
    });
}

onMounted(async () => {
    employes.value = await get("/emps");

    await fetchPlanning();

    console.log("list:", employes.value);
});

watch(date, async (newDate) => {
    if(!newDate) return;

    plannings.value = await get("/plannings", {
        dps: newDate
    },{
        immediate: true
    });
})
</script>
