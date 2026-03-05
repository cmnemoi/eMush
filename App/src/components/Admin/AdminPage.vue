<template>
    <div class="box-container">
        <AdminBanner />
        <router-view></router-view>
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
