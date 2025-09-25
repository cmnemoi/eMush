import { createRouter, createWebHistory } from "vue-router";
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
import NewsListPage from "@/components/Admin/News/NewsListPage.vue";
import NewsWritePage from "@/components/Admin/News/NewsWritePage.vue";
import NewsPage from "@/components/NewsPage.vue";
import NeronAnnouncementPage from "@/components/Admin/Daedalus/NeronAnnouncementPage.vue";
import ModerationPlayerListPage from "@/components/Moderation/ModerationPlayerListPage.vue";
import ModerationViewPlayerDetailPage from "@/components/Moderation/ModerationViewPlayerDetailPage.vue";
import ModerationPage from "@/components/Moderation/ModerationPage.vue";
import ModerationHomePage from "@/components/Moderation/ModerationHomePage.vue";
import { adminConfigRoutes } from "@/router/adminConfigPages";
import SanctionListPage from "@/components/Moderation/SanctionListPage.vue";
import ClosedExpeditionPanel from "@/components/Game/ClosedExpeditionPanel.vue";
import Rules from "@/components/Rules.vue";
import AdminActionsPage from "@/components/Admin/Actions/AdminActionsPage.vue";
import ModerationReportListPage from "@/components/Moderation/ModerationReportListPage.vue";
import ModerationShipListPage from "@/components/Moderation/ModerationShipListPage.vue";
import ModerationShipViewPage from "@/components/Moderation/ModerationShipViewPage.vue";
import { User } from "@/entities/User";
import NotFoundPage from "@/components/NotFoundPage.vue";
import FakeAdminPage from "@/components/FakeAdminPage.vue";
import UserProfileShips from "@/components/User/UserProfileShips.vue";
import UserShipHistory from "@/components/User/UserShipHistory.vue";

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
        path: "/user/:userId",
        name: "UserPage",
        component: UserPage,
        redirect: { name: 'UserShipHistory' },
        meta: { authorize: [UserRole.USER] },
        children: [
            {
                name: "UserShipHistory",
                path: '',
                component: UserShipHistory
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
                component: ShipRanking
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
                        redirect: { name: 'UserPage' }
                    }
                ]

            }
        ]
    },
    {
        path: "/rules",
        name: "Rules",
        component: Rules
    },
    {
        path: "/admin",
        name: "Admin",
        component: AdminPage,
        redirect: { name: 'AdminHomePage' },
        meta: { authorize: [UserRole.ADMIN]  },
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
                name: "AdminNeronAnnouncement",
                path: "neron-announcement",
                component: NeronAnnouncementPage
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
            },
            {
                name: "AdminNewsList",
                path: 'news-list',
                component: NewsListPage
            },
            {
                name: "AdminNewsWrite",
                path: 'write-news',
                component: NewsWritePage
            },
            {
                name: "AdminNewsEdit",
                path: 'edit-news/:newsId',
                component: NewsWritePage
            },
            {
                name: "AdminPlayerList",
                path: 'player-list',
                component: ModerationPlayerListPage
            },
            {
                name: "AdminViewPlayerDetail",
                path: 'player-view-detail/:playerId',
                component: ModerationViewPlayerDetailPage,
                children: [
                    {
                        name: "ModerationViewPlayerUserPage",
                        path: '/user/:userId',
                        component: UserPage,
                        redirect: { name: 'UserPage' }
                    }
                ]
            },
            {
                name: "AdminActionsPage",
                path: 'actions',
                component: AdminActionsPage
            }
        ]
    },
    {
        path: "/moderation",
        name: "Moderation",
        component: ModerationPage,
        redirect: { name: 'ModerationHomePage' },
        meta: { authorize: [UserRole.MODERATOR]  },
        children: [
            {
                name: "ModerationHomePage",
                path: '',
                component: ModerationHomePage
            },
            {
                name: "ModerationPlayerList",
                path: 'player-list',
                component: ModerationPlayerListPage
            },
            {
                name: "ModerationUserList",
                path: 'user',
                component: UserListPage,
                children: [
                    {
                        name: "ModerationUserListUserPage",
                        path: '/user/:userId',
                        component: UserPage,
                        redirect: { name: 'UserPage' }
                    },
                    {
                        name: "SanctionListPage",
                        path: '/user/:username/:userId/ModerationSanction',
                        component: UserPage,
                        redirect: { name: 'SanctionListPage' }
                    }
                ]
            },
            {
                name: "ModerationReportList",
                path: 'reports',
                component: ModerationReportListPage
            },
            {
                name: "ModerationShipList",
                path: 'ships',
                component: ModerationShipListPage

            },
            {
                name: "ModerationShipView",
                path: 'ships/:daedalusId',
                component: ModerationShipViewPage
            },
            {
                name: "ModerationViewPlayerDetail",
                path: 'player-view-detail/:playerId',
                component: ModerationViewPlayerDetailPage,
                children: [
                    {
                        name: "ModerationViewPlayerUserPage",
                        path: '/user/:userId',
                        component: UserPage,
                        redirect: { name: 'UserPage' }
                    }
                ]
            },
            {
                name: "SanctionListPage",
                path: '/user/:username/:userId/moderationSanctions',
                component: SanctionListPage
            },
            {
                name: "ModerationUserDetail",
                path: 'user/:userId',
                component: UserDetailPage
            }
        ]
    },
    {
        path: "/news",
        name: "NewsPage",
        component: NewsPage
    },
    {
        path: "/expPerma/:id",
        name: "ClosedExpeditionPanel",
        component: ClosedExpeditionPanel
    },
    {
        path: "/token",
        name: "Token",
        component: Token
    },
    {
        path: "/fake-admin",
        name: "FakeAdmin",
        component: FakeAdminPage
    },
    {
        path: "/:pathMatch(.*)*",
        name: "NotFound",
        component: NotFoundPage
    }
];

const router = createRouter({
    history: createWebHistory(),
    // @ts-ignore
    routes,
    scrollBehavior(to, from, savedPosition) {
        return { top: 0 };
    }
});

router.beforeEach((to, from, next) => {
    const authorize = (to.meta?.authorize as UserRole[] | undefined);
    if (!authorize) {
        return next();
    }

    const currentUser = store.getters["auth/getUserInfo"] as User | null;
    if (!currentUser) {
        return next({ path: '/', query: { returnUrl: to.path } });
    }

    if (!currentUser.hasAcceptedRules && to.name !== 'Rules') {
        return next({ name: 'Rules' });
    }

    const isAuthorizedForRoute: boolean = authorize.some((role: UserRole) => is_granted(role, currentUser));
    if (!isAuthorizedForRoute) {
        return next({ name: 'FakeAdmin' });
    }

    return next();
});

export default router;
