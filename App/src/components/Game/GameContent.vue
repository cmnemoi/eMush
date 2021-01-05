<template>
  <div class="main" v-if="getPlayer !== null && !loading">
    <ExplorationPanel style="display: none;"></ExplorationPanel>
    <div class="top-banner">
      <BannerPanel :player="getPlayer" :daedalus="getPlayer.daedalus"></BannerPanel>
    </div>
    <div class="game-content">
      <CharPanel :player="getPlayer"></CharPanel>
      <ShipPanel :room="getPlayer.room"></ShipPanel>
      <CommsPanel :day="getPlayer.daedalus.day" :cycle="getPlayer.daedalus.cycle"></CommsPanel>
    </div>
    <ProjectsPanel></ProjectsPanel>
    <div class="bottom-banner"></div>
  </div>
</template>

<script>
import ExplorationPanel from "@/components/Game/ExplorationPanel";
import BannerPanel from "@/components/Game/BannerPanel";
import CharPanel from "@/components/Game/CharPanel";
import ShipPanel from "@/components/Game/ShipPanel";
import CommsPanel from "@/components/Game/Communications/CommsPanel";
import ProjectsPanel from "@/components/Game/ProjectsPanel";
import {mapActions, mapGetters} from "vuex";

export default {
  name: 'GameContent',
  components: {
    ExplorationPanel,
    BannerPanel,
    CharPanel,
    ShipPanel,
    CommsPanel,
    ProjectsPanel
  },
  props: {
    playerId: Number,
  },
  computed: {
    ...mapGetters('player', [
      'getPlayer',
      'loading',
    ])
  },
  methods: {
    ...mapActions('player', [
      'loadPlayer',
    ]),
  },
  beforeMount() {
      this.loadPlayer({playerId: this.playerId});
  }
}
</script>

<style lang="scss" scoped>

.main {
    position: relative;
    min-height: 424px;
    width: 1080px;
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
        box-shadow: inset 0 0 35px 25px rgb(15,89,171);
        background-color: rgb(34,38,102);
        opacity: 0.5;
    }
}

.game-content {
  flex-direction: row;
  justify-content: space-between;
}
</style>
