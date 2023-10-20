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

.admin-container {
    flex: 1;
    margin-right: 1.6em;

    &::v-deep {
        h3 {
            font-size: 1.3em;
            margin: 2.8em 0 0.6em;
        }
    }

}

</style>
