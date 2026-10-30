import Swal from "sweetalert2";

const alertTypes = ["success", "error", "warning", "info", "question"];

function popupClasses(icon) {
    const typeClass = alertTypes.includes(icon) ? `logicar-swal-alert--${icon}` : "logicar-swal-alert--default";

    return `logicar-swal-popup logicar-swal-alert ${typeClass}`;
}

export function useAlert(title, text, icon) {
    Swal.fire({
        title,
        text,
        icon,
        toast: true,
        position: "bottom",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        customClass: {
            popup: popupClasses(icon),
        },
    });
}

export function useLoading(message) {
    Swal.fire({
        title: "Veuillez patienter...",
        text: message,
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            popup: "logicar-swal-popup",
        },
        didOpen: () => {
            Swal.showLoading();
        },
    });
}

export const close = () => Swal.close();
