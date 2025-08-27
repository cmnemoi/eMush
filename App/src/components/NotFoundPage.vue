<template>
    <ErrorPage :title="$t('errors.title')" :error-message="$t('errors.notFound') as unknown as string" />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import ErrorPage from '@/components/ErrorPage.vue';

export default defineComponent({
    name: 'NotFoundPage',
    components: { ErrorPage }
});
</script>


