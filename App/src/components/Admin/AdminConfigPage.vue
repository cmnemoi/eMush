<template>
    <div class="admin-config-container">
        <AdminConfigBanner></AdminConfigBanner>
        <div class="admin-container">
            <div class="admin-config-content-container">
                <router-view></router-view>
            </div>
        </div>
    </div></template>

<script lang="ts">
import { defineComponent } from "vue";
import AdminConfigBanner from "@/components/Admin/AdminConfigBanner.vue";

export default defineComponent ({
    name: "AdminConfigPage",
    components: {
        AdminConfigBanner
    }
});

</script>

<style lang="scss" scoped>

.admin-config-container { flex-direction: row; }

.admin-container { flex: 1; }

</style>
