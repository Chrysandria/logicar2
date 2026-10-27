import {
    startOfWeek,
    endOfWeek,
    addWeeks,
    eachDayOfInterval,
    format,
} from "date-fns";
import { fr } from "date-fns/locale";

const nextMonday = startOfWeek(addWeeks(new Date(), 1), {
    weekStartsOn: 1,
});

const nextSunday = endOfWeek(nextMonday, {
    weekStartsOn: 1,
});

export const nextWeek = eachDayOfInterval({
    start: nextMonday,
    end: nextSunday,
}).map((date) => ({
    jour: toUpper(format(date, "EEEE", { locale: fr })),
    date: format(date, "yyyy-MM-dd"),
}));

function toUpper(str){
    return str.charAt(0).toUpperCase() + str.slice(1);
}

export function dateToFr(date){
    return format(date, "dd/MM/yyyy");
}
