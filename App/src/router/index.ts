import { createWebHistory, createRouter } from "vue-router";
import GamePage from "@/components/GamePage.vue";
import Token from "@/components/Token.vue";
import DefaultConfigPage from "@/components/Admin/DefaultConfigPage.vue";
import { UserRole } from "@/enums/user_role.enum";
import store from "@/store";
import HomePage from "@/components/HomePage.vue";

const routes = [
    {
        path: "/",
        name: "HomePage",
        component: HomePage
    },
    {
        path: "/game",
        name: "GamePage",
        component: GamePage,
        meta: { authorize: [UserRole.USER] }
    },
    {
        path: "/admin",
        name: "Admin",
        component: DefaultConfigPage,
        meta: { authorize: [UserRole.ADMIN] }
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

router.beforeEach((to, from, next) => {
    // redirect to login page if not logged in and trying to access a restricted page
    const { authorize }: any = to.meta;
    const currentUser = store.getters["auth/getUserInfo"];
    if (authorize) {
        if (!currentUser) {
            // not logged in so redirect to login page with the return url
            return next({ path: '/', query: { returnUrl: to.path } });
        }

        // check if route is restricted by role
        if (authorize.length && !authorize.some((role: UserRole) => currentUser.roles.includes(role) )) {
            // role not authorised so redirect to home page
            return next({ path: '/' });
        }
    }

    next();
});

export default router;
