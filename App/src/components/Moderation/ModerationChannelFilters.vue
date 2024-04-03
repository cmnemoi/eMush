<template>
    <label>{{ $t('moderation.contenu du message :') }}
        <input
            type="search"
            class=""
            placeholder=""
            aria-controls="example"
            @change="$emit('change')"
        >
    </label>
    <label>{{ $t('moderation.messageAuthor :') }}
        <input
            type="search"
            class=""
            placeholder=""
            aria-controls="example"
            @change="$emit('change')"
        >
    </label>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'ModerationChannelFilters',
    emits: ['change'],
    props: {
        value: String
    }
});
</script>

<style lang="scss" scoped>
</style>
