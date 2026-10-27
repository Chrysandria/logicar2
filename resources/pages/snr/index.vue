<template>
    <div class="container p-2">
        <div class="d-flex flex-column flex-md-row justify-content-end">
            <div class="card p-2">
                <div class="d-flex align-items-center gap-2">
                    <span
                        class="bg-primary-subtle text-primary p-2 rounded fs-3"
                    >
                        <i class="bi bi-calendar-check"></i>
                    </span>
                    <p class="mb-0">
                        <span class="small">Résumé du jour:</span><br />
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
                Aucune séance de ramassage n'a été enregistré pour aujourd'hui.
            </span>
        </div>

        <div
            class="table-responsive mb-3 p-3 mt-5"
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
                    <col style="width: 12%" />
                    <col style="width: 12%" />
                    <col style="width: 12%" />
                    <col style="width: 12%" />
                    <col style="width: 12%" />
                </colgroup>
                <thead>
                    <tr>
                        <th>Matricule</th>
                        <th>Nom</th>
                        <th>Téléphone</th>
                        <th>Lieu ramassage</th>
                        <th>Point ADM</th>
                        <th>Point décalé</th>
                        <th>Remarque</th>
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
                        <td>
                            <p
                                class="badge bg-danger text-white mb-0"
                                v-if="!item.ok"
                            >
                                Non récupéré
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="d-flex justify-content-end">
                <button class="btn btn-sm btn-light">
                    Gérer
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { dateToFr } from "../../services/dayManager";
import { ref, onMounted } from "vue";
import { get, useFetch } from "../../services/fetch";
import { format } from "date-fns";
const today = new Date();
const fd = dateToFr(today);
const plannings = ref([]);

async function fetchPlanning() {
    plannings.value = await get("/expeditions", {
        date: "2026-11-02",
    });
}

onMounted(async () => {
    await fetchPlanning();
    console.log(plannings.value);
});
</script>
