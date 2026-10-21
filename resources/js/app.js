import { createApp } from "vue";
import app from "../pages/app.vue";
import { router } from "../services/router.js";
import { createPinia } from "pinia";
import "bootstrap";

const pinia = createPinia();

createApp(app).use(pinia).use(router).mount("#app");
