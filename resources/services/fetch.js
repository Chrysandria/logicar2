import axios from "axios";
import { useAlert } from "./alert";

export async function useFetch(request){
    try {
        let response = await axios(request);
        useAlert("Succès",response.data.message,"success");

    }catch(err){
        console.error(err.response);
        useAlert("Erreur",err.response?.data?.message,"error")
    }
}
