import { createWebHistory, createRouter } from "vue-router";
import GamePage from "@/components/GamePage.vue";
import Token from "@/components/Token.vue";
import { is_granted, UserRole } from "@/enums/user_role.enum";
import store from "@/store";
import HomePage from "@/components/HomePage.vue";
import AdminPage from "@/components/Admin/AdminPage.vue";
import DefaultConfigPage from "@/components/Admin/DefaultConfigPage.vue";
import DaedalusPage from "@/components/Admin/DaedalusPage.vue";
import UserListPage from "@/components/Admin/User/UserListPage.vue";
import UserDetailPage from "@/components/Admin/User/UserDetailPage.vue";

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
        component: AdminPage,
        meta: { authorize: [UserRole.ADMIN] },
        children: [
            {
                name: "AdminGameConfig",
                path: 'game-config',
                component: DefaultConfigPage
            },
            {
                name: "AdminDaedalus",
                path: 'daedalus',
                component: DaedalusPage
            },
            {
                name: "AdminUser",
                path: 'user',
                component: UserListPage
            },
            {
                name: "AdminUserDetail",
                path: 'user/:userId',
                component: UserDetailPage
            }
        ]
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
        if (authorize.length && is_granted(authorize, currentUser)) {
            // role not authorised so redirect to home page
            return next({ path: '/' });
        }
    }

    next();
});

export default router;
