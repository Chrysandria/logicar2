import { createRouter, createWebHistory } from "vue-router";

const pages = import.meta.glob("../**/*vue",{
    eager: true,
    import: "default"
});

const getPage = (path) => pages[`../pages/${path}.vue`];

const routes = [
    {
        name: "public",
        path: "/",
        component: getPage("public"),
        children:[
            {
                name: "public.index",
                path: "",
                component: getPage("public/index")
            },
            {
                name: "public.login",
                path: "login",
                component: getPage("public/login")
            }
        ]
    },
    {
        name: "services",
        path: "/:service?",
        component: getPage("services"),
        children: [
            {
                name: "services.index",
                path: "",
                component: getPage("services/index")
            },
            {
                name: "service.edit",
                path: ":date?",
                component: getPage("services/edit")
            }
        ]
    },
    {
        name: "snr",
        path: "/snr",
        component: getPage("snr"),
        children: [
            {
                name: "snr.index",
                path: "",
                component: getPage("snr/index")
            },
            {
                name: "snr.treat",
                path: ":date?",
                component: getPage("snr/treat")
            },
            {
                name: "snr.users",
                path: "users",
                component: getPage("snr/users")
            },
            {
                name: "snr.cars",
                path: "cars",
                component: getPage("snr/cars")
            }
        ]
    }
];

export const router = createRouter({
    history: createWebHistory(),
    routes
});
