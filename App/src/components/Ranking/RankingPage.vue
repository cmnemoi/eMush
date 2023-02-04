<template>
    <RankingBanner></RankingBanner>
    <div class="ranking-container">
        <router-view></router-view>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import RankingBanner from "@/components/Ranking/RankingBanner.vue";

export default defineComponent ({
    name: "RankingPage",
    components: {
        RankingBanner
    }
});

</script>

<style lang="scss" scoped>

</style>
