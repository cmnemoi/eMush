<template>
  <div class="main">
    <ExplorationPanel style="display: none;"></ExplorationPanel>
    <div class="top-banner">
        <BannerPanel character-level="5" character-name="Jin Su"></BannerPanel>
    </div>
    <div class="game-content">
      <CharPanel></CharPanel>
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
export default {
  name: 'GamePage',
  components: {
    ExplorationPanel,
    BannerPanel,
    CharPanel,
    ShipPanel,
    CommsPanel,
    ProjectsPanel
  },
  props: {
  }
}
</script>

<style lang="scss" scoped>

.game-content {
  flex-direction: row;
  justify-content: space-between;
}
</style>
