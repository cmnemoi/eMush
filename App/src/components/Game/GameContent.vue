<template>
  <div class="main" v-if="player !== null">
    <ExplorationPanel style="display: none;"></ExplorationPanel>
    <div class="top-banner">
      <BannerPanel :player="player" :daedalus="player.daedalus"></BannerPanel>
    </div>
    <div class="game-content">
      <CharPanel :player="player"></CharPanel>
      <ShipPanel></ShipPanel>
      <CommsPanel></CommsPanel>
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
import CommsPanel from "@/components/Game/CommsPanel";
import ProjectsPanel from "@/components/Game/ProjectsPanel";
import ApiService from "@/services/api.service";
import {Player} from "@/entities/Player";

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
  data() {
    return {
      player: null,
    };
  },
  beforeMount() {
    ApiService.get(process.env.VUE_APP_API_URL + 'player/' + this.playerId)
        .then((result) => {
          if (result.data) {
            this.player = (new Player()).load(result.data);
          }
        })
    .catch((error) => {
      console.error(error)
    })
  }
}
</script>

<style lang="scss" scoped>

.game-content {
  flex-direction: row;
  justify-content: space-between;
}
</style>
