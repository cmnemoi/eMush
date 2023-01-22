import { createWebHistory, createRouter } from "vue-router";
import GamePage from "@/components/GamePage.vue";
import RankingPage from "@/components/Ranking/RankingPage.vue";
import ShipRanking from "@/components/Ranking/ShipRanking.vue";
import Token from "@/components/Token.vue";
import { is_granted, UserRole } from "@/enums/user_role.enum";
import store from "@/store";
import HomePage from "@/components/HomePage.vue";
import UserListPage from "@/components/Admin/User/UserListPage.vue";
import UserDetailPage from "@/components/Admin/User/UserDetailPage.vue";
import DaedalusListPage from "@/components/Admin/Daedalus/DaedalusListPage.vue";
import DaedalusDetailPage from "@/components/Admin/Daedalus/DaedalusDetailPage.vue";
import AdminHomePage from "@/components/Admin/AdminHomePage.vue";
import AdminPage from "@/components/Admin/AdminPage.vue";
import AdminConfigPage from "@/components/Admin/AdminConfigPage.vue";
import TheEndPage from "@/components/Ranking/TheEndPage.vue";
import { adminConfigRoutes } from "@/router/adminConfigPages";

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
        path: "/ranking",
        name: "RankingPage",
        component: RankingPage,
        redirect: { name: 'ShipRanking' },
        meta: { authorize: [UserRole.USER] },
        children: [
            {
                name: "ShipRanking",
                path: '',
                component: ShipRanking,
            },
            {
                name: "TheEnd",
                path: '/the-end/:closedDaedalusId',
                component: TheEndPage
            }
        ]
    },
    {
        path: "/admin",
        name: "Admin",
        component: AdminPage,
        redirect: { name: 'AdminHomePage' },
        meta: { authorize: [UserRole.ADMIN] },
        children: [
            {
                name: "AdminHomePage",
                path: '',
                component: AdminHomePage
            },
            {
                path: "/config",
                name: "AdminConfigHomepage",
                component: AdminConfigPage,
                redirect: { name: 'AdminGameConfigList' },
                children: adminConfigRoutes
            },
            {
                name: "AdminDaedalusList",
                path: 'daedalus-list',
                component: DaedalusListPage
            },
            {
                name: "AdminDaedalusCreate",
                path: 'daedalus-create',
                component: DaedalusDetailPage
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
