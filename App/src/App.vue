<template>
    <div class="main-container">
        <Banner />
        <router-view />
        <ErrorPopup />
    </div>
</template>

<script>

import Banner from "@/components/Banner";
import ErrorPopup from "@/components/ErrorPopup";

export default {
    name: 'App',
    components: {
        Banner,
        ErrorPopup
    }
};
</script>
