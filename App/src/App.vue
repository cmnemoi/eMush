<template>
    <div class="main-container">
        <Spinner :loading="userLoading || playerLoading || configLoading" />
        <Banner />
        <MaintenancePage v-if="gameInMaintenance && !userIsAdmin"/>
        <router-view v-else/>
        <ErrorPopup />
        <ConfirmPopup />
        <ReportPopup />
        <Thanks />
        <ModerationWarningBanner :userWarnings="userWarnings" />
        <LocaleChange />
    </div>
</template>

<script lang="ts">

import Banner from "@/components/Banner.vue";
import ErrorPopup from "@/components/ErrorPopup.vue";
import ConfirmPopup from "@/components/ConfirmPopup.vue";
import ReportPopup from "@/components/ReportPopup.vue";
import Spinner from "@/components/Utils/Spinner.vue";
import { mapGetters, mapActions } from "vuex";
import LocaleChange from "@/components/Utils/LocaleChange.vue";
import Thanks from "@/components/Thanks.vue";
import MaintenancePage from "@/components/MaintenancePage.vue";
import ModerationWarningBanner from "@/components/Moderation/ModerationWarningBanner.vue";
import { defineComponent } from "vue";

export default defineComponent({
    name: 'App',
    head() {
        return {
            title: this.$t('title'),
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
        ReportPopup,
        LocaleChange,
        Thanks,
        MaintenancePage
    },
    computed: {
        ...mapGetters({
            gameInMaintenance: 'admin/gameInMaintenance',
            userLoading: 'auth/isLoading',
            userId: 'auth/userId',
            userWarnings: 'moderation/userWarnings',
            playerLoading: 'player/isLoading',
            configLoading: 'gameConfig/isLoading',
            userIsAdmin: 'auth/isAdmin'
        }),
        baseUrl() {
            return process.env.VUE_APP_URL as string;
        }
    },
    methods: {
        ...mapActions({
            loadGameMaintenanceStatus: 'admin/loadGameMaintenanceStatus',
            loadUserWarnings: 'moderation/loadUserWarnings'
        })
    },
    beforeMount() {
        this.loadGameMaintenanceStatus();
        if (this.userId) {
            this.loadUserWarnings(this.userId);
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
    background: #0f0f43 url("~@/assets/images/bg.jpg") no-repeat center 0;
    overflow-y: auto;
}

</style>
