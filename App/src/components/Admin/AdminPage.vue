<template>
    <AdminBanner />
    <div class="admin-container">
        <div class="box-container">
            <router-view></router-view>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import AdminBanner from "@/components/Admin/AdminBanner.vue";

export default defineComponent ({
    name: "AdminPage",
    components: {
        AdminBanner
    }
});

</script>

<style lang="scss" scoped>

.box-container {
    margin-top: 0;
}

</style>
