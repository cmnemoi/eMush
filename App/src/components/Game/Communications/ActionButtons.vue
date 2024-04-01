<template>
    <button
        v-for="(actionType, key) in actions"
        :key="key"
        @click="$emit(actionType)"
    >
        <img :src="action(actionType).icon">
        {{ $t(action(actionType).wording) }}
    </button>
</template>

<script lang="ts">

import { defineComponent } from "vue";

const availableActions: {[index: string]: any} = {
    favorite: { icon: require('@/assets/images/comms/fav.png'), wording: 'game.communications.bookmark' },
    invite: { icon: require('@/assets/images/comms/invite.png'), wording: 'game.communications.invite' },
    leave: { icon: require('@/assets/images/comms/close.png'), wording: 'game.communications.leave' },
    refresh: { icon: require('@/assets/images/comms/refresh.gif'), wording: 'game.communications.refresh' },
    reply: { icon: require('@/assets/images/comms/reply.png'), wording: 'game.communications.reply' },
    report: { icon: require('@/assets/images/comms/alert.png'), wording: 'game.communications.report' },
    delete: { icon: require('@/assets/images/bin.png'), wording: 'moderation.sanction.delete' }
};

export default defineComponent ({
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
