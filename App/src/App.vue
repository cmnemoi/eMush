<template>
    <div class="main-container">
        <TopBar />
        <UserMenu />
        <Spinner :loading="userLoading || playerLoading || configLoading || adminLoading"/>
        <ToastContainer />
        <Banner />
        <MaintenancePage v-if="gameInMaintenance && !userIsAdmin"/>
        <router-view v-else/>
        <ErrorPopup />
        <ConfirmPopup />
        <PlayerNotificationPopUp />
        <Thanks />
        <ModerationWarningBanner :user-sanctions="userSanctions" />
    </div>
</template>

<script lang="ts">

import Banner from "@/components/Banner.vue";
import ConfirmPopup from "@/components/ConfirmPopup.vue";
import ErrorPopup from "@/components/ErrorPopup.vue";
import PlayerNotificationPopUp from "@/components/Game/PlayerNotificationPopUp.vue";
import MaintenancePage from "@/components/MaintenancePage.vue";
import ModerationWarningBanner from "@/components/Moderation/ModerationWarningBanner.vue";
import Thanks from "@/components/Thanks.vue";
import Spinner from "@/components/Utils/Spinner.vue";
import { TokenService } from "@/services/storage.service";
import UserService from "@/services/user.service";
import store from "@/store";
import { defineComponent } from "vue";
import { mapActions, mapGetters } from "vuex";
import TopBar from "./components/Hud/TopBar.vue";
import UserMenu from "./components/Hud/UserMenu.vue";
import ToastContainer from "./components/ToastContainer.vue";

export default defineComponent({
    name: 'App',
    head() {
        return {
            title: this.$t('title.headline', { count: this.notificationsCount }),
            meta: [
                { name: 'description', content: this.$t('metaSeo.description') },
                { name: 'og:title', content: this.$t('metaSeo.og:title') },
                { name: 'og:description', content: this.$t('metaSeo.og:description') },
                { name: 'og:url', content: this.baseUrl }
            ]
        };
    },
    components: {
        ModerationWarningBanner,
        Spinner,
        Banner,
        ErrorPopup,
        ConfirmPopup,
        Thanks,
        MaintenancePage,
        ToastContainer,
        PlayerNotificationPopUp,
        TopBar,
        UserMenu
    },
    computed: {
        ...mapGetters({
            gameInMaintenance: 'admin/gameInMaintenance',
            userLoading: 'auth/isLoading',
            user: 'auth/getUserInfo',
            userSanctions: 'moderation/userSanctions',
            playerLoading: 'player/isLoading',
            configLoading: 'gameConfig/isLoading',
            adminLoading: 'admin/isLoading',
            userIsAdmin: 'auth/isAdmin',
            notificationsCount: 'notifications/notificationsCount'
        }),
        baseUrl() {
            return import.meta.env.VITE_APP_URL as string;
        }
    },
    methods: {
        ...mapActions({
            loadGameMaintenanceStatus: 'admin/loadGameMaintenanceStatus',
            loadUserSanctions: 'moderation/loadUserSanctions',
            openNewsToast: 'toast/openNewsToast'
        })
    },
    async beforeMount() {
        await this.loadGameMaintenanceStatus();

        // Try to restore session from httpOnly cookie
        if (TokenService.getUserInfo()) {
            await store.dispatch('auth/userInfo');
        }

        if (this.user) {
            await this.loadUserSanctions(this.user.id);
            const userHasNotReadLatestNews = await UserService.hasNotReadLatestNews();
            if (userHasNotReadLatestNews) {
                this.openNewsToast(this.$t('game.popUp.newNews'));
            }
        }
    }
});
</script>

<style lang="scss" scoped>

.main-container {
    color: #fff;
    flex-grow: 1;
    min-width: 100%;
    min-height: 100%;
    background: #0f0f43 url("/src/assets/images/bg.jpg") no-repeat center 0;
    overflow-y: auto;
    padding-top: 32px;
}

</style>
