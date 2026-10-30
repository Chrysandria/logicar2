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

        <div
            class="mt-5 pt-5 text-center"
            v-if="plannings.length === 0"
            style="opacity: 0.7"
        >
            <span class="display-1"> ❌ </span> <br />
            <span class="small">
                Aucun planning enregistré pour le moment.
            </span>
        </div>

        <div
            class="table-responsive mb-3 p-3 bg-white"
            v-for="(p, index) in plannings"
            :key="index"
        >
            <div class="d-flex justify-content-end mb-3">
                <div class="badge text-light bg-primary">
                    <i class="bi bi-clock me-2"></i>
                    {{ p.hps.substring(0, 5) }}
                </div>
            </div>
            <table
                class="table table-fixed align-middle small"
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
            <div class="d-flex justify-content-end">
                <button
                    class="btn btn-light btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#newPlanning"
                    @click="specificPlanning = subPlanning(p.planning_items)"
                >
                    Gérer
                </button>
            </div>
        </div>
    </div>

    <div
        class="modal fade"
        id="newPlanning"
        tabindex="-1"
        aria-hidden="true"
        aria-labelledby="title"
    >
        <div class="modal-dialog modal-dialog-centered modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="title">
                        <i class="bi bi-filter text-primary fw-bold me-2"></i>
                        Triage d'un planning
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="fermer"
                    ></button>
                </div>
                <div class="modal-body">
                    <div class="row my-3">
                        <div class="col-12 col-md-3 d-flex align-items-center">

                                <p class="mb-0 small">
                                    <span
                                        class="text-decoration-underline fw-semibold me-2"
                                        >Date:</span
                                    >
                                    <span>{{ fd }}</span>
                                </p>

                        </div>
                        <div class="col-12 col-md-3">
                            <div class="input-group">
                                <label for="trajet" class="input-group-text"
                                    >Trajet</label
                                >
                                <select
                                    name="trajet"
                                    id="trajet"
                                    class="form-select"
                                >
                                    <option disabled selected value="">choisir un trajet</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="input-group">
                                <label for="car" class="input-group-text"
                                    >Véhicule</label
                                >
                                <select
                                    name="car"
                                    id="car"
                                    class="form-select"
                                >
                            <option disabled selected value="">choisir un véhicule</option>
                            </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="input-group">
                                <label for="car" class="input-group-text"
                                    >HDR</label
                                >
                                <input type="time" id="hdr" name="hdr" class="form-control">
                            </div>
                        </div>
                    </div>
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
                                    <th class="text-center">Sélection</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    class="border-bottom small align-middle"
                                    v-for="(item, index) in specificPlanning"
                                    :key="index"
                                >
                                    <td>
                                        {{ item.employe.matricule }}
                                    </td>
                                    <td>
                                        {{ item.employe.nom }}
                                    </td>
                                    <td>
                                        {{ item.employe.telephone }}
                                    </td>
                                    <td>
                                        {{ item.employe.lieu_ramassage }}
                                    </td>
                                    <td>
                                        {{ item.employe.pnt_adm }}
                                    </td>
                                    <td>
                                        {{ item.employe.pnt_dec }}
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" class="form-check-input" name="ok" id="ok" :value="item.id" v-model="sub">
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
                    >
                        Annuler
                    </button>
                    <button class="btn btn-primary" @click="console.log(sub)">
                        Enregistré
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import { onMounted, ref, computed, watch } from "vue";
import { dateToFr } from "../../services/dayManager";
import { useFetch, get } from "../../services/fetch";
import { useRoute } from "vue-router";

const plannings = ref([]);
const errorMessage = ref("");
const route = useRoute();
const date = computed(() => route.params.date);
const fd = computed(() => dateToFr(date.value));
const specificPlanning = ref([]);
const sub = ref([]);

async function fetchPlanning() {
    plannings.value = await get("/plannings", {
        dps: date.value,
    });
}

function subPlanning(obj){
    return obj.filter((o) => !o.expedition_id)
}

onMounted(async () => {
    await fetchPlanning();
});

watch(date, async (newDate) => {
    if (!newDate) return;

    plannings.value = await get(
        "/plannings",
        {
            dps: newDate,
        },
        {
            immediate: true,
        },
    );
});
</script>
