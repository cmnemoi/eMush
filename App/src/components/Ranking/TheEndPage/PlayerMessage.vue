<template>
    <Tippy
        tag="span"
        :class="['message', {'hidden' : player.messageIsHidden && isModerator}]"
        v-if="player.messageIsHidden"
    >
        <span v-html="player.getFormattedMessage()" />
        <template #content>
            <h1>{{ $t('moderation.theEndPage.messageIsHidden')}}</h1>
            <p>{{ $t('moderation.theEndPage.messageIsHiddenDescription') }}</p>
        </template>
    </Tippy>
    <span v-html="player.getFormattedMessage()" v-else />
    <Tippy tag="span" v-if="isModerator && !player.messageHasBeenModerated" @click="$emit('openHideDialog', player)">
        <img :src="getImgUrl('comms/discrete.png')" alt="Hide message">
        <template #content>
            <h1>{{ $t('moderation.theEndPage.hideMessage')}}</h1>
            <p>{{ $t('moderation.theEndPage.hideMessageDescription') }}</p>
        </template>
    </Tippy>
    <Tippy tag="span" v-if="isModerator && !player.messageHasBeenModerated" @click="$emit('openEditDialog', player)">
        <img :src="getImgUrl('ui_icons/action_points/pa_core.png')" alt="Edit message">
        <template #content>
            <h1>{{ $t('moderation.theEndPage.editMessage')}}</h1>
            <p>{{ $t('moderation.theEndPage.editMessageDescription') }}</p>
        </template>
    </Tippy>
    <Tippy tag="span" v-if="!player.messageHasBeenModerated" @click="$emit('openReportDialog', player)">
        <img :src="getImgUrl('comms/alert.png')" alt="Report message">
        <template #content>
            <h1>{{ $t('moderation.report.name')}}</h1>
            <p>{{ $t('moderation.report.description') }}</p>
        </template>
    </Tippy>
</template>

<script setup lang="ts">
import { ClosedPlayer } from "@/entities/ClosedPlayer";
import { getImgUrl } from "@/utils/getImgUrl";

defineProps<{
    player: ClosedPlayer
    isModerator: boolean
}>();

defineEmits<{
    openHideDialog: [player: ClosedPlayer]
    openEditDialog: [player: ClosedPlayer]
    openReportDialog: [player: ClosedPlayer]
}>();
</script>

<style lang="scss" scoped>
.hidden {
    opacity: 20%;
}
</style>
