<template>
    <ModerationBanner />
    <div class="box-container">
        <router-view></router-view>
    </div>
</template>

<script lang="ts">

import { defineComponent } from "vue";
import ModerationBanner from "@/components/Moderation/ModerationBanner.vue";

export default defineComponent ({
    name: "ModerationPage",
    components: {
        ModerationBanner
    }
});

</script>

<style lang="scss" scoped>
    .box-container {
        margin-top: 0;
    }
</style>
