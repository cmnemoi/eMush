<template>
    <UserBanner></UserBanner>
    <div class="User-container">
        <div class="box-container">
            <router-view></router-view>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import UserBanner from "@/components/User/UserBanner.vue";

export default defineComponent ({
    name: "UserPage",
    components: {
        UserBanner
    }
});

</script>

<style lang="scss" scoped>

.box-container {
    margin-top: 0;
}

</style>
