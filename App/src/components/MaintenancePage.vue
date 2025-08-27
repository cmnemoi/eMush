<template>
    <ErrorPage :title="$t('errors.title')" :error-message="errorMessage" />
</template>

<script lang="ts">
import { defineComponent } from "vue";
import ErrorPage from "@/components/ErrorPage.vue";

export default defineComponent ({
    name: 'MaintenancePage',
    components: { ErrorPage },
    computed: {
        errorMessage(): string {
            return this.$t('errors.gameInMaintenance') as unknown as string;
        }
    }
});

</script>

