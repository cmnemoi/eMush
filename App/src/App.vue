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
                { name: 'og:url', content: this.baseUrl },
            ]
        };
    },
    components: {
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
            loadGameMaintenanceStatus: 'admin/loadGameMaintenanceStatus'
        })
    },
    beforeMount() {
        this.loadGameMaintenanceStatus();
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
}

</style>
