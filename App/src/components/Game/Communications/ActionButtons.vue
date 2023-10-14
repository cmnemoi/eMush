<template>
    <div class="actions">
        <button
            v-for="(actionType, key) in actions"
            :key="key"
            @mousedown="$emit(actionType, $event)"
        >
            <img :src="action(actionType).icon">
            {{ action(actionType).wording }}
        </button>
    </div>
</template>

<script lang="ts">

import { defineComponent } from "vue";

const availableActions: {[index: string]: any} = {
    favorite: { icon: 'src/assets/images/comms/fav.png', wording: 'Favori' },
    invite: { icon: 'src/assets/images/comms/invite.png', wording: 'Inviter' },
    leave: { icon: 'src/assets/images/comms/close.png', wording: 'Quitter' },
    refresh: { icon: 'src/assets/images/comms/refresh.gif', wording: 'Rafr.' },
    reply: { icon: 'src/assets/images/comms/reply.png', wording: 'Répondre' },
    report: { icon: 'src/assets/images/comms/alert.png', wording: 'Plainte' }
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

.actions {
    flex-direction: row;
    justify-content: flex-end;
    align-items: stretch;

    button {
        cursor: pointer;

        @include button-style(0.83em, 400, initial);

        height: 100%;
        margin-left: 3px;

        img {
            padding: 0 0.2em 0 0;
        }
    }
}

</style>
