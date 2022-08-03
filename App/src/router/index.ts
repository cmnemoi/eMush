import { createWebHistory, createRouter } from "vue-router";
import GamePage from "@/components/GamePage.vue";
import Token from "@/components/Token.vue";
import DefaultConfigPage from "@/components/Admin/DefaultConfigPage.vue";

const routes = [
    {
        path: "/",
        name: "GamePage",
        component: GamePage
    },
    {
        path: "/admin",
        name: "admin",
        component: DefaultConfigPage
    },
    {
        path: "/token",
        name: "Token",
        component: Token
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
