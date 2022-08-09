<template>
    <div class="admin-container">
        <AdminBanner></AdminBanner>
        <div class="admin-container">
            <div class="admin-content-container">
                <router-view></router-view>
            </div>
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

.admin-container {
    padding: 20px;
}

.admin-content-container {
    padding: 20px;
    border: solid goldenrod;
    border-radius: 30px;
}

</style>
