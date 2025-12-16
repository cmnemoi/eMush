<template>
    <Tippy
        tag="button"
        v-for="(actionType, key) in actions"
        :key="key"
        @click="$emit(actionType)"
    >
        <img :src="action(actionType).icon">
        <span>{{ $t(action(actionType).wording) }}</span>
        <template #content>
            <h1>{{ $t(action(actionType).wording) }}</h1>
            <p>{{ $t(action(actionType).description) }}</p>
        </template>

    </Tippy>
</template>

<script lang="ts">

import { getImgUrl } from "@/utils/getImgUrl";
import { defineComponent } from "vue";
import { Tippy } from "vue-tippy";

const availableActions: {[index: string]: any} = {
    favorite: { icon: getImgUrl('comms/fav.png'), wording: 'game.communications.bookmark', description: 'game.communications.bookmarkDescription' },
    unfavorite: { icon: getImgUrl('comms/unfav.png'), wording: 'game.communications.unbookmark', description: 'game.communications.unbookmarkDescription' },
    invite: { icon: getImgUrl('comms/invite.png'), wording: 'game.communications.invite', description: 'game.communications.inviteDescription' },
    leave: { icon: getImgUrl('comms/close.png'), wording: 'game.communications.leave', description: 'game.communications.leaveDescription' },
    refresh: { icon: getImgUrl('comms/refresh.gif'), wording: 'game.communications.refresh', description: 'game.communications.refreshDescription' },
    reply: { icon: getImgUrl('comms/reply.png'), wording: 'game.communications.reply', description: 'game.communications.replyDescription' },
    report: { icon: getImgUrl('comms/alert.png'), wording: 'moderation.report.name', description: 'moderation.report.description' },
    delete: { icon: getImgUrl('bin.png'), wording: 'moderation.sanction.delete_message', description: 'moderation.sanction.deleteDescription' }
};

export default defineComponent ({
    components: { Tippy },
    props: {
        actions: {
            type: Array<string>,
            require: true
        }
    },
    computed: {
        action() {
            return (actionType: string) => availableActions[actionType] || {};
        }
    },
    emits: [
        'favorite',
        'unfavorite',
        'invite',
        'leave',
        'refresh',
        'reply',
        'report',
        'delete'
    ]
});
</script>

<style lang="scss" scoped>

button {
    cursor: pointer;
    height: 100%;

    @include button-style(0.83em, 400, initial);

    img {
        padding: 0 0.2em 0 0;
    }
}

</style>
