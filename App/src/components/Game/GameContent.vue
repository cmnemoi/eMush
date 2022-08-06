<template>
    <div v-if="player">
        <div v-if="['in_game'].includes(player.gameStatus)" class="main">
            <InvitationPrivateChannelMenu />
            <div class="top-banner">
                <BannerPanel :player="player" :daedalus="player.daedalus" />
            </div>
            <div class="game-content">
                <CharPanel :player="player" />
                <ShipPanel :room="player.room" :player="player" />
                <CommsPanel :day="player.daedalus.day" :cycle="player.daedalus.cycle" />
            </div>
            <ProjectsPanel />
            <div class="bottom-banner" />
        </div>
        <div v-else-if="['finished'].includes(player.gameStatus)" class="main">
            <Purgatory :player="player" />
        </div>
    </div>
</template>

<script lang="ts">
import BannerPanel from "@/components/Game/BannerPanel.vue";
import CharPanel from "@/components/Game/CharPanel.vue";
import ShipPanel from "@/components/Game/Ship/ShipPanel.vue";
import CommsPanel from "@/components/Game/Communications/CommsPanel.vue";
import ProjectsPanel from "@/components/Game/ProjectsPanel.vue";
import { mapActions, mapState } from "vuex";
import Purgatory from "@/components/PurgatoryPage.vue";
import InvitationPrivateChannelMenu from "@/components/Game/Communications/InvitationPrivateChannelMenu.vue";
import { defineComponent } from "vue";

export default defineComponent ({
    name: 'GameContent',
    components: {
        InvitationPrivateChannelMenu,
        Purgatory,
        BannerPanel,
        CharPanel,
        ShipPanel,
        CommsPanel,
        ProjectsPanel
    },
    props: {
        playerId: Number
    },
    computed: {
        ...mapState('player', [
            'player'
        ])
    },
    beforeMount(): void {
        if (this.playerId) {
            this.loadPlayer({ playerId: this.playerId });
        }
    },
    methods: {
        ...mapActions('player', [
            'loadPlayer'
        ])
    }
});
</script>

<style lang="scss" scoped>

.main {
    position: relative;
    min-height: 424px;
    max-width: 1080px;
    width: 100%;
    margin: 36px auto;
    padding: 12px 12px 42px 12px;
    z-index: 10;

    &::after {
        content: "";
        position: absolute;
        z-index: -1;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;

        @include corner-bezel(18.5px);

        box-shadow: inset 0 0 35px 25px rgb(15, 89, 171);
        background-color: rgb(34, 38, 102);
        opacity: 0.5;
    }
}

.game-content {
    flex-direction: row;
    justify-content: space-between;
}
</style>
