<template>
    <div class="box-container">
        <router-view></router-view>
    </div>
</template>

<script lang="ts">

import { defineComponent } from "vue";
import ModerationPlayerListPage from "./ModerationPlayerListPage.vue";

export default defineComponent ({
    name: "ModerationPage",
    components: {
        ModerationPlayerListPage
    }
});

</script>
