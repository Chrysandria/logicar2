import { defineStore } from "pinia";
import { ref } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

export const useStore = defineStore("auth", () => {
    const router = useRouter();

    const user = ref(null);

    async function getConnected(){
        try{
            let response = await axios.get("/auth/me");
            let connected = response?.data?.user;
            user.value = {
                name: connected.name,
                service: connected.service
            }

            if(connected.service = "SNR"){
                router.replace({
                    name: "snr"
                })
            }else{
                router.replace({
                    name: "services",
                    params: {
                        service: connected.service
                    }
                });
            }

            return;

        }catch{
            router.replace("/");
        }
    }

    async function flush(){
        user.value = null
    }

    return { user, getConnected, flush }
})
