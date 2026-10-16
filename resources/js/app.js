import { createApp } from "vue";
import app from "../pages/app.vue";
import { router } from "../services/router.js";
import { createPinia } from "pinia";
import "bootstrap"

createApp(app).use(router).use(createPinia()).mount("#app");
