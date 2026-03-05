<template>
    <div class="box-container">
        <StatsBanner />
        <router-view></router-view>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import StatsBanner from "@/components/Stats/StatsBanner.vue";

export default defineComponent ({
    name: "StatsPage",
    components: {
        StatsBanner
    }
});

</script>

