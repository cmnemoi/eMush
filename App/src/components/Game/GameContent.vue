<template>
    <div v-if="getPlayer !== null" class="main">
        <GamePopUp style="display: none;" />
        <div class="top-banner">
            <BannerPanel :player="getPlayer" :daedalus="getPlayer.daedalus" />
        </div>
        <div class="game-content">
            <CharPanel :player="getPlayer" />
            <ShipPanel :room="getPlayer.room" />
            <CommsPanel :day="getPlayer.daedalus.day" :cycle="getPlayer.daedalus.cycle" />
        </div>
        <ProjectsPanel />
        <div class="bottom-banner" />
    </div>
</template>

<script>
import GamePopUp from "@/components/Utils/GamePopUp";
import BannerPanel from "@/components/Game/BannerPanel";
import CharPanel from "@/components/Game/CharPanel";
import ShipPanel from "@/components/Game/Ship/ShipPanel";
import CommsPanel from "@/components/Game/Communications/CommsPanel";
import ProjectsPanel from "@/components/Game/ProjectsPanel";
import { mapActions, mapGetters } from "vuex";

export default {
    name: 'GameContent',
    components: {
        GamePopUp,
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
        ...mapGetters('player', [
            'getPlayer'
        ])
    },
    beforeMount() {
        this.loadPlayer({ playerId: this.playerId });
    },
    methods: {
        ...mapActions('player', [
            'loadPlayer'
        ])
    }
};
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
