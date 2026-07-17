<template>
    <button v-if="sanctionDetails" class="action-button" @click="$emit('detail', sanction)">{{ $t(`moderation.sanctionDetail.${sanctionType}.title`) }}</button>
    <router-link
        v-if="goToPlayer && sanction.user.playerId"
        :to="{ name: 'ModerationViewPlayerDetail', params: { playerId: sanction.user.playerId } }"
        class="action-button"
    >
        {{ $t('moderation.goToPlayerDetails') }}
    </router-link>
    <router-link
        v-if="goToUser"
        :to="{ name: 'ModerationUserListUserPage', params: { userId: sanction.user.id } }"
        class="action-button"
    >
        {{ $t('moderation.goToUserProfile') }}
    </router-link>
    <router-link
        v-if="sanctionList"
        :to="{ name: 'SanctionListPage', params: { username: sanction.user.username, userId: sanction.user.id } }"
        class="action-button"
    >
        {{ $t('moderation.sanctionList') }}
    </router-link>
</template>

<script setup lang="ts">
import { ModerationSanction } from "@/entities/ModerationSanction";
import { computed } from "vue";

const props = withDefaults(defineProps<{
    sanction: ModerationSanction,
    sanctionDetails?: boolean,
    goToPlayer?: boolean,
    goToUser?: boolean,
    sanctionList?: boolean
}>(), {
    sanctionDetails: true,
    goToPlayer: false,
    goToUser: false,
    sanctionList: false
});

defineEmits<{
    detail: [sanction: ModerationSanction],
}>();

const reportActions = ['report', 'report_processed', 'report_abusive'];
const sanctionType = computed(() => reportActions.includes(props.sanction.moderationAction) ? 'report' : 'sanction');
</script>

<style lang="scss" scoped>
.action-button {
    width: 100%;
    margin: 0.2rem;

    @include button-style();
}
</style>
