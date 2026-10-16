import Swal from "sweetalert2";

export function useAlert(title,text,icon){
    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        toast: true,
        position: "bottom",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
}

export function useLoading(message){
    Swal.fire({
            title: "Veuillez patienter...",
            text: message,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
}

export const close = () => Swal.close();
