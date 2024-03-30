<template>
    <Tippy
        tag="button"
        v-for="(actionType, key) in actions"
        :key="key"
        @click="$emit(actionType)"
    >
        <img :src="action(actionType).icon">
        {{ $t(action(actionType).wording) }}
        <template #content>
            <h1>{{ $t(action(actionType).wording) }}</h1>
            <p>{{ $t(action(actionType).description) }}</p>
        </template>

    </Tippy>
</template>

<script lang="ts">

import { defineComponent } from "vue";
import { Tippy } from "vue-tippy";
import { getImgUrl } from "@/utils/getImgUrl";

const availableActions: {[index: string]: any} = {
    favorite: { icon: getImgUrl('comms/fav.png'), wording: 'game.communications.bookmark', description: 'game.communications.bookmarkDescription' },
    invite: { icon: getImgUrl('comms/invite.png'), wording: 'game.communications.invite', description: 'game.communications.inviteDescription' },
    leave: { icon: getImgUrl('comms/close.png'), wording: 'game.communications.leave', description: 'game.communications.leaveDescription' },
    refresh: { icon: getImgUrl('comms/refresh.gif'), wording: 'game.communications.refresh', description: 'game.communications.refreshDescription' },
    reply: { icon: getImgUrl('comms/reply.png'), wording: 'game.communications.reply', description: 'game.communications.replyDescription' },
    report: { icon: getImgUrl('comms/alert.png'), wording: 'moderation.report', description: 'moderation.reportDescription' },
    delete: { icon: getImgUrl('bin.png'), wording: 'moderation.sanction.delete_message', description: 'moderation.sanction.deleteDescription' }
};

export default defineComponent ({
    components: { Tippy },
    props: {
        actions: {
            type: Array,
            require: true
        }
    },
    computed: {
        action() {
            return (actionType: string) => availableActions[actionType] || {};
        }
    }
});
</script>

<style lang="scss" scoped>

button {
    cursor: pointer;

    @include button-style(0.83em, 400, initial);

    height: 100%;
    margin-left: 3px;

    img {
        padding: 0 0.2em 0 0;
    }
}

</style>
