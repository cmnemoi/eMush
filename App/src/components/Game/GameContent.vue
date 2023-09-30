<template>
    <div v-if="player">
        <div v-if="['in_game'].includes(player.gameStatus)" class="box-container">
            <InvitationPrivateChannelMenu />
            <div class="top-banner">
                <BannerPanel :player="player" :daedalus="player.daedalus" />
            </div>
            <div class="game-content">
                <CharPanel :player="player" />
                <ShipPanel v-if="!player.isFocused()" :room="player.room" :player="player" />
                <TerminalPanel v-else :player="player" />
                <CommsPanel :calendar="player.daedalus.calendar"/>
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
import { TerminalEnum } from "@/enums/terminal.enum";
import TerminalPanel from "./Terminals/TerminalPanel.vue";


export default defineComponent ({
    name: 'GameContent',
    components: {
        InvitationPrivateChannelMenu,
        Purgatory,
        BannerPanel,
        CharPanel,
        ShipPanel,
        CommsPanel,
        ProjectsPanel,
        TerminalPanel
    },
    data() {
        return {
            TerminalEnum
        };
    },
    props: {
        playerId: Number
    },
    computed: {
        ...mapState('player', [
            'player'
        ]),
    },
    beforeMount(): void {
        if (this.playerId) {
            this.loadPlayer({ playerId: this.playerId });
        }
    },
    methods: {
        ...mapActions('player', [
            'loadPlayer'
        ]),
    }
});
</script>

<style lang="scss" scoped>

.box-container {
    position: relative;
    min-height: 424px;
    //z-index: 10;
}

.game-content {
    flex-direction: row;
    justify-content: space-between;
}
</style>
