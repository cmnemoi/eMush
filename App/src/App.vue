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
