<template>
    <div class="main-container">
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
        <LocaleChange />
    </div>
</template>

<script lang="ts">

import Banner from "@/components/Banner.vue";
import ErrorPopup from "@/components/ErrorPopup.vue";
import ConfirmPopup from "@/components/ConfirmPopup.vue";
import Spinner from "@/components/Utils/Spinner.vue";
import { mapGetters, mapActions } from "vuex";
import LocaleChange from "@/components/Utils/LocaleChange.vue";
import Thanks from "@/components/Thanks.vue";
import MaintenancePage from "@/components/MaintenancePage.vue";
import ModerationWarningBanner from "@/components/Moderation/ModerationWarningBanner.vue";
import { defineComponent } from "vue";
import ToastContainer from "./components/ToastContainer.vue";
import PlayerNotificationPopUp from "@/components/Game/PlayerNotificationPopUp.vue";
import UserService from "@/services/user.service";

export default defineComponent({
    name: 'App',
    head() {
        return {
            title: this.$t('title.headline'),
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
        LocaleChange,
        Thanks,
        MaintenancePage,
        ToastContainer,
        PlayerNotificationPopUp
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
            userIsAdmin: 'auth/isAdmin'
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
}

</style>
