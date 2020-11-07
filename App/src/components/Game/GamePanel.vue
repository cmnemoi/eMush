<template>
  <div class="main">
      <div class="top-banner">
          <game-banner character-level="5" character-name="Jin Su"></game-banner>
      </div>
      <char-panel></char-panel>
      <div class="game-content"></div>
      <div class="bottom-banner"></div>
  </div>
</template>

<script>
import GameBanner from "@/components/Game/GameBanner";
import CharPanel from "@/components/Game/CharPanel";
export default {
  name: 'GamePanel',
  components: {
    GameBanner,
    CharPanel
  },
  props: {
  }
}
</script>

<style lang="scss" scoped>
</style>
