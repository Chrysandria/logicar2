import axios from "axios";
import { useAlert } from "./alert";

export async function useFetch(request){
    try {
        let response = await axios(request);
        useAlert("Succès",response.data.message,"success");
        return true;

    }catch(err){
        console.error(err);
        useAlert("Erreur",err.response?.data?.message,"error");
        return false;
    }
}


export async function get(url, params = null) {
    try {
        let response = await axios({
            method: 'get',
            url: url,
            params: params
        });
        return response.data;
    }catch(err){
        console.error(err.response);
        useAlert("Erreur",err.response?.data?.message,"error")
    }
}
