<template>
    <PopUp :is-open="popUp.isOpen" @close="close">
        <h1 class="title" v-html="formatText(popUp.title)" />
        <p v-html="formatText(popUp.description)" />
        <div class="actions">
            <button class="action-button" @click="close">{{ $t('game.popUp.ok') }}</button>
        </div>
    </PopUp>
</template>

<script lang="ts">
import PopUp from "@/components/Utils/PopUp.vue";
import { formatText } from "@/utils/formatText";
import { defineComponent } from "vue";
import { mapActions, mapGetters } from "vuex";

export default defineComponent ({
    name: "PlayerNotificationPopUp",
    components: { PopUp },
    computed: {
        ...mapGetters({
            popUp: 'popup/playerNotificationPopUp'
        })
    },
    methods: {
        formatText,
        ...mapActions({
            closePopUp: 'popup/closePlayerNotificationPopUp',
            deleteNotification: 'player/deleteNotification'
        }),
        async close() {
            await Promise.all([this.closePopUp(), this.deleteNotification()]);
        }
    }
});

</script>

<style lang="scss" scoped>
.actions {
    flex-direction: row;
    align-self: center;

     button, a {
        min-width: 160px;
     }
}
</style>
