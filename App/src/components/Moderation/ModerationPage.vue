<template>
    <div class="box-container">
        <ModerationBanner />
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

