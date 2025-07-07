<template>
    <PopUp :is-open="popUp.isOpen" @close="closeAction">
        <h1 class="title" v-html="formatText(popUp.title)" v-if="popUp.title" />
        <h3 class="sub-title" v-html="formatText(popUp.subTitle)" v-if="popUp.subTitle" />
        <p class="message">
            <img :src="getImgUrl('mush_stamp.png')" v-if="popUp.isStamped">
            <span v-html="formatText(popUp.description)" />
        </p>
        <div class="actions">
            <button class="action-button" @click="closeAction">{{ $t('game.popUp.ok') }}</button>
        </div>
    </PopUp>
</template>

<script lang="ts">
import PopUp from "@/components/Utils/PopUp.vue";
import { Player } from "@/entities/Player";
import { formatText } from "@/utils/formatText";
import { getImgUrl } from "@/utils/getImgUrl";
import { defineComponent } from "vue";
import { mapActions, mapGetters } from "vuex";

export default defineComponent ({
    name: "PlayerNotificationPopUp",
    components: { PopUp },
    computed: {
        ...mapGetters({
            popUp: 'popup/playerNotificationPopUp',
            player: 'player/player'
        })
    },
    methods: {
        formatText,
        getImgUrl,
        ...mapActions({
            closePopUp: 'popup/closePlayerNotificationPopUp',
            deleteNotification: 'player/deleteNotification',
            loadPlayer: 'player/loadPlayer',
            openNextNotificationPopUp: 'popup/openPlayerNotificationPopUp'
        }),
        async closeAction() {
            await Promise.all([this.closePopUp(), this.deleteNotification()]);
            await this.loadPlayer({ playerId: this.player.id });
            await this.openNextNotificationPopUp({ player: this.player });
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

.sub-title {
    margin-bottom: 0;
}

.message {
    font-family: "Days One", "Segoe UI", "Lucida Grande", "Trebuchet MS", Arial, "lucida sans unicode", sans-serif;
    max-height: 300px;
    overflow-y: auto;
    padding-right: 10px;
    margin-bottom: 15px;

    :deep(a) {
        color: $green;
    }

    :deep(strong), :deep(em) {
        color: #00b5e4;
    }
}

img {
    float: left;
    margin: 0 auto 0 0;
    padding: 0 10px 5px 0;
}
</style>
