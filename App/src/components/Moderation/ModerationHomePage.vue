<template>
    <ModerationPlayerListPage />
</template>

<script lang="ts">
import { defineComponent } from "vue";
import ModerationPlayerListPage from "./ModerationPlayerListPage.vue";

export default defineComponent ({
    name: "ModeratorHomePage",
    components: {
        ModerationPlayerListPage
    }
});

</script>
