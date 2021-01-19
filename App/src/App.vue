<template>
    <div class="main-container">
        <Banner />
        <router-view />
        <Footer />
        <ErrorPopup />
    </div>
</template>

<script>

import Banner from "@/components/Banner";
import ErrorPopup from "@/components/ErrorPopup";
import Footer from "@/components/Footer";

export default {
    name: 'App',
    components: {
        Banner,
        ErrorPopup,
        Footer
    }
};
</script>
