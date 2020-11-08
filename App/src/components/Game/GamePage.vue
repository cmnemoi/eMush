<template>
  <div class="main">
      <div class="top-banner">
          <BannerPanel character-level="5" character-name="Jin Su"></BannerPanel>
      </div>
      <div class="game-content">
        <CharPanel></CharPanel>
        <ShipPanel></ShipPanel>
      </div>
      <div class="bottom-banner"></div>
  </div>
</template>

<script>
import BannerPanel from "@/components/Game/BannerPanel";
import CharPanel from "@/components/Game/CharPanel";
import ShipPanel from "@/components/Game/ShipPanel";
export default {
  name: 'GamePage',
  components: {
    BannerPanel,
    CharPanel,
    ShipPanel
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
