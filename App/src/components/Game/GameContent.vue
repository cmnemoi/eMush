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

.game-content {
  flex-direction: row;
  justify-content: space-between;
}
</style>
