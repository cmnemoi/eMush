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
import UserPage from "@/components/User/UserPage.vue";
import UserShips from "@/components/User/UserShips.vue";
import NewsListPage from "@/components/Admin/News/NewsListPage.vue";
import NewsWritePage from "@/components/Admin/News/NewsWritePage.vue";
import NewsPage from "@/components/NewsPage.vue";
import PlayerListPage from "@/components/Admin/Player/PlayerListPage.vue";
import { adminConfigRoutes } from "@/router/adminConfigPages";

const routes = [
    {
        path: "/",
        name: "HomePage",
        component: HomePage,
    },
    {
        path: "/game",
        name: "GamePage",
        component: GamePage,
        meta: { authorize: [UserRole.USER] }
    },
    {
        path: "/user/:userId",
        name: "UserPage",
        component: UserPage,
        redirect: { name: 'UserShips' },
        meta: { authorize: [UserRole.USER] },
        children: [
            {
                name: "UserShips",
                path: '',
                component: UserShips
            },
            {
                name: "UserLegacyProfile",
                path: '',
                component: () => import("@/components/User/UserLegacyProfile.vue"),
            }
        ]
    },
    {
        path: "/me",
        name: "MePage",
        component: UserPage,
        meta: { authorize: [UserRole.USER] },
        // @ts-ignore
        beforeEnter: (to, from, next) => {
            const currentUser = store.getters["auth/getUserInfo"];
            if (currentUser) {
                next({ name: 'UserPage', params: { userId: currentUser.userId } });
            } else {
                next({ name: 'HomePage' });
            }
        }
        
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
                component: TheEndPage,
                children: [
                    {
                        name: "TheEndUserPage",
                        path: '/user/:userId',
                        component: UserPage,
                        redirect: { name: 'UserPage' },
                    }
                ]

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
                meta: { authorize: [UserRole.ADMIN] },
                component: AdminHomePage
            },
            {
                path: "/config",
                name: "AdminConfigHomepage",
                component: AdminConfigPage,
                meta: { authorize: [UserRole.ADMIN] },
                redirect: { name: 'AdminGameConfigList' },
                children: adminConfigRoutes
            },
            {
                name: "AdminDaedalusList",
                path: 'daedalus-list',
                meta: { authorize: [UserRole.ADMIN] },
                component: DaedalusListPage
            },
            {
                name: "AdminDaedalusCreate",
                path: 'daedalus-create',
                meta: { authorize: [UserRole.ADMIN] },
                component: DaedalusDetailPage
            },
            {
                name: "AdminUser",
                path: 'user',
                meta: { authorize: [UserRole.ADMIN] },
                component: UserListPage
            },
            {
                name: "AdminUserDetail",
                path: 'user/:userId',
                meta: { authorize: [UserRole.ADMIN] },
                component: UserDetailPage
            },
            {
                name: "AdminNewsList",
                path: 'news-list',
                meta: { authorize: [UserRole.ADMIN] },
                component: NewsListPage
            },
            {
                name: "AdminNewsWrite",
                path: 'write-news',
                meta: { authorize: [UserRole.ADMIN] },
                component: NewsWritePage
            },
            {
                name: "AdminNewsEdit",
                path: 'edit-news/:newsId',
                meta: { authorize: [UserRole.ADMIN] },
                component: NewsWritePage
            },
            {
                name: "AdminPlayerList",
                path: 'player-list',
                meta: { authorize: [UserRole.ADMIN] },
                component: PlayerListPage
            },
            {
                name: "AdminSecretsList",
                path: 'secrets-list',
                meta: { authorize: [UserRole.ADMIN] },
                component: () => import("@/components/Admin/Secrets/SecretsListPage.vue"),
            },
            {
                name: "AdminSecretsCreate",
                path: 'create-secret',
                meta: { authorize: [UserRole.ADMIN] },
                component: () => import("@/components/Admin/Secrets/SecretsEditPage.vue"),
            },
            {
                name: "AdminSecretsEdit",
                path: 'edit-secret/:secret',
                meta: { authorize: [UserRole.ADMIN] },
                component: () => import("@/components/Admin/Secrets/SecretsEditPage.vue"),
            }
        ]
    },
    {
        path: "/news",
        name: "NewsPage",
        component: NewsPage
    },
    {
        path: "/import",
        name: "ImportPage",
        component: () => import("@/components/ImportPage.vue"),
    },
    {
        path: "/token",
        name: "Token",
        component: Token,
    }
];

const router = createRouter({
    history: createWebHistory(),
    // @ts-ignore
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
