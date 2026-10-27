import { defineStore } from "pinia";
import { ref } from "vue";
import axios from "axios";
import { useRouter, useRoute } from "vue-router";

export const useStore = defineStore("auth", () => {
    const router = useRouter();
    const route = useRoute();

    const user = ref(null);

    async function getConnected() {
        try {
            let response = await axios.get("/auth/me");
            let connected = response?.data?.user;

            const publicRoutes = ["/","/login"];

            if (!connected) {
                if (!publicRoutes.includes(route.path)) {
                    await router.replace("/");
                }

                return;
            }

            user.value = {
                name: connected.name,
                service: connected.service,
            };

            if (connected.service === "SNR" && !route.path.startsWith("/snr")) {
                await router.replace({
                    name: "snr.index",
                });
            }

            if(connected.service !== "SNR" && route.path.startsWith("/snr") || publicRoutes.includes(route.path)){
                await router.replace({
                    name: "services.index",
                    params: {
                        service: connected.service
                    }
                })
            }
            return;
        } catch (error) {
            console.error(error);
        }
    }

    async function logout() {
        try {
            await axios.post("/auth/logout");
            console.log("Déconnections effectuer");

            user.value = null;
            getConnected();
        } catch (err) {
            console.error(err);
        }
    }

    return { user, getConnected, logout };
});
