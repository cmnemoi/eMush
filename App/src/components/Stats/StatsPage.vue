<template>
    <StatsBanner />
    <div class="admin-container">
        <div class="box-container">
            <router-view></router-view>
        </div>
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

<style lang="scss" scoped>

.box-container {
    margin-top: 0;
}

</style>
