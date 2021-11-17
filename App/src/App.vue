<template>
    <div class="main-container">
        <Spinner :loading="userLoading || playerLoading" />
        <Banner />
        <router-view />
        <ErrorPopup />
        <LocaleChange />
    </div>
</template>

<script>

import Banner from "@/components/Banner";
import ErrorPopup from "@/components/ErrorPopup";
import Spinner from "@/components/Utils/Spinner";
import { mapGetters } from "vuex";
import LocaleChange from "@/components/Utils/LocaleChange.vue";

export default {
    name: 'App',
    components: {
        Spinner,
        Banner,
        ErrorPopup,
        LocaleChange
    },
    computed: {
        ...mapGetters({
            userLoading: 'auth/isLoading',
            playerLoading: 'player/isLoading'
        }
        )
    }
};
</script>

<style lang="scss" scoped>

.main-container {
    z-index: -2;
    color: #fff;
    flex-grow: 1;
    min-width: 100%;
    min-height: 100%;
    padding-bottom: 5em;
    background: #0f0f43 url("~@/assets/images/bg.jpg") no-repeat center 0;
}

</style>
