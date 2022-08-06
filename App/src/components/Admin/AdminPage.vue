<template>
    <AdminBanner></AdminBanner>
    <router-view></router-view>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import AdminBanner from "@/components/Admin/AdminBanner.vue";

export default defineComponent ({
    name: "AdminPage",
    components: {
        AdminBanner,
    },
});

</script>

<style lang="scss" scoped>

button {
    cursor: pointer;
    margin: 0 20px;
    padding: 5px 10px;
    color: white;
    font-size: 1.1em;
    letter-spacing: .06em;

    &:hover,
    &:active {
        color: #dffaff;
        text-shadow: 0 0 1px rgb(255, 255, 255), 0 0 1px rgb(255, 255, 255);
    }
}

</style>
