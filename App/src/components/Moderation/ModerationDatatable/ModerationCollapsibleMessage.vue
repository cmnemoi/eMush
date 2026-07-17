<template>
    <details>
        <summary>{{ $t('moderation.sanction.showMore') }}</summary>
        <span>{{ message }}</span>
    </details>
</template>

<script setup lang="ts">
defineProps<{
    message: string | null,
}>();
</script>

<style lang="scss" scoped>
summary { cursor: pointer; }

details { text-align: left !important; }
</style>
