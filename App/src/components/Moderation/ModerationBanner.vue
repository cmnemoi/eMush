<template>
    <div class="sub-menu">
        <router-link :to="{ name: 'ModerationPlayerList' }">{{ $t('moderation.banner.players') }}</router-link>
        <router-link :to="{ name: 'ModerationUserList' }">{{ $t('moderation.banner.users') }}</router-link>
        <router-link :to="{ name: 'ModerationReportList' }">{{ $t('moderation.reportToAddress') }}</router-link>
        <router-link :to="{ name: 'ModerationBanAllUsers' }">{{ $t('moderation.banner.banAllUsers') }}</router-link>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent({
    name: 'ModerationBanner'
});
</script>
