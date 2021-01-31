<template>
    <div class="actions">
        <button v-for="(actionType, key) in actions" :key="key">
            <img :src="action(actionType).icon">
            {{ action(actionType).wording }}
        </button>
    </div>
</template>

<script>

const availableActions = {
    favorite: { icon: require('@/assets/images/comms/fav.png'), wording: 'Favori' },
    reply: { icon: require('@/assets/images/comms/reply.png'), wording: 'Répondre' },
    report: { icon: require('@/assets/images/comms/alert.png'), wording: 'Plainte' }
};

export default {
    props: {
        actions: {
            type: Array,
            require: true
        }
    },
    computed: {
        action() {
            return actionType => availableActions[actionType] || {};
        }
    }
};
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
