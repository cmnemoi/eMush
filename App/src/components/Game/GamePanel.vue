<template>
  <div class="main">
      <div class="top-banner">
          <game-banner character-level="5" character-name="Jin Su"></game-banner>
      </div>
      <div class="game-content"></div>
      <div class="bottom-banner"></div>
  </div>
</template>

<script>
import GameBanner from "@/components/Game/GameBanner";
export default {
  name: 'GamePanel',
  components: {GameBanner},
  props: {
  }
}
</script>

<style scoped>
</style>
