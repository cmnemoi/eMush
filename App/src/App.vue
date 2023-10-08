<template>
    <div class="main-container">
        <Title :title="$t('title')" />
        <Spinner :loading="userLoading || playerLoading || configLoading" />
        <Banner />
        <ErrorPage v-if="error && parseInt(error.status) == 503" :error="error"/>
        <router-view v-else />
        <ErrorPopup />
        <Thanks />
        <LocaleChange />
    </div>
</template>

<script>

import Banner from "@/components/Banner";
import ErrorPopup from "@/components/ErrorPopup";
import Spinner from "@/components/Utils/Spinner";
import { mapGetters, mapState } from "vuex";
import LocaleChange from "@/components/Utils/LocaleChange.vue";
import Title from "@/components/Utils/Title.vue";
import Thanks from "@/components/Thanks.vue";
import ErrorPage from "@/components/ErrorPage.vue";

export default {
    name: 'App',
    components: {
        Spinner,
        Banner,
        ErrorPopup,
        LocaleChange,
        Title,
        Thanks,
        ErrorPage
    },
    computed: {
        ...mapState('error', [
            'error'
        ]),
        ...mapGetters({
            userLoading: 'auth/isLoading',
            playerLoading: 'player/isLoading',
            configLoading: 'gameConfig/isLoading'
        })
    }
};
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
