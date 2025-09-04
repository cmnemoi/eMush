<template>
    <div v-if="player" class="game-content-wrapper">
        <ExpeditionPopUp :exploration="player.daedalus.exploration" />
        <div v-if="['in_game'].includes(player.gameStatus)" class="box-container">
            <InvitationPrivateChannelMenu />
            <div class="top-banner">
                <BannerPanel :player="player" :daedalus="player.daedalus" />
            </div>
            <div class="game-content">
                <div class="grid-container char-panel-container">
                    <CharPanel :player="player" />
                </div>
                <div class="grid-container central-panel-container">
                    <TerminalPanel v-if="player.isFocused()" :player="player" />
                    <CommanderOrderPanel v-else-if="commanderOrderPanelOpen" :player="player" />
                    <ComManagerAnnouncementPanel v-else-if="comManagerAnnouncementPanelOpen" :player="player" />
                    <ExpeditionPanel v-else-if="player.isExploring()" :player="player" />
                    <ShipPanel v-else :room="player.room" :player="player" />
                </div>
                <div class="grid-container comms-panel-container">
                    <CommsPanel :calendar="player.daedalus.calendar"/>
                </div>
                <div class="grid-container projects-panel-container">
                    <ProjectsPanel :projects="player.daedalus.projects" />
                </div>
            </div>
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
import { mapActions, mapState, mapGetters } from "vuex";
import Purgatory from "@/components/PurgatoryPage.vue";
import InvitationPrivateChannelMenu from "@/components/Game/Communications/InvitationPrivateChannelMenu.vue";
import ExpeditionPopUp from "@/components/Game/ExpeditionPopUp.vue";
import { defineComponent } from "vue";
import { TerminalEnum } from "@/enums/terminal.enum";
import TerminalPanel from "@/components/Game/Terminals/TerminalPanel.vue";
import ExpeditionPanel from "@/components/Game/ExpeditionPanel.vue";
import CommanderOrderPanel from "@/components/Game/CommanderOrderPanel.vue";
import ComManagerAnnouncementPanel from "./ComManagerAnnouncementPanel.vue";


export default defineComponent ({
    name: 'GameContent',
    components: {
        InvitationPrivateChannelMenu,
        ExpeditionPopUp,
        Purgatory,
        BannerPanel,
        CharPanel,
        ShipPanel,
        CommsPanel,
        ProjectsPanel,
        TerminalPanel,
        ExpeditionPanel,
        CommanderOrderPanel,
        ComManagerAnnouncementPanel
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
            'player',
            'playerChanged'
        ]),
        ...mapGetters('player', ['commanderOrderPanelOpen']),
        ...mapGetters('player', ['comManagerAnnouncementPanelOpen']),
        ...mapGetters('admin', ['gameInMaintenance'])
    },
    async beforeMount(): Promise<void> {
        if (this.gameInMaintenance) {
            return;
        }

        if (!this.playerChanged && this.playerId) {
            await this.loadPlayer({ playerId: this.playerId });
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

.game-content-wrapper {
    width: fit-content;
    margin: auto;
}

.box-container {
    margin-top: 8px;
    position: relative;
    min-height: 424px;
    //z-index: 10;
}

.game-content {
    display: grid;
    gap: 6px;

    .char-panel-container {
        grid-area: char-panel;
    }

    .central-panel-container {
        grid-area: central-panel;
    }

    .comms-panel-container {
        grid-area: comms-panel;
    }

    .projects-panel-container {
        grid-area: projects-panel;
    }

    grid-template-columns: 1fr minmax(360px, 424px) 2fr;
    grid-template-rows: auto 1fr;
    grid-template-areas:
      "char-panel       central-panel       comms-panel"
      "projects-panel   projects-panel      comms-panel";
}

@media screen and (max-width: $breakpoint-desktop-m) and (orientation: portrait) {
    .game-content {
        grid-template-columns: 1fr;
        grid-template-areas:
            "char-panel"
            "central-panel"
            "comms-panel"
            "projects-panel";

        .grid-container {
            overflow-x: auto;
            align-items: center;
        }
    }
}
</style>
