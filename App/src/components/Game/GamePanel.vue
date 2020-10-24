<template>
  <div class="game-container">
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
    .game-container {
      align-self: center;
      max-width: 1080px;
      padding-top: 50px;
      width: 100%;
      height: 695px;
    }
    .top-banner {
      background-image : url("~@/assets/images/topleft.png"), url("~@/assets/images/topright.png");
      background-position: left top, right top;
      background-color: transparent;
      background-repeat: no-repeat;
      height: 61px;
      width: 100%;
    }
    .game-content {
      width: 100%;
      height: 480px;
      background-image : url("~@/assets/images/bg_left.png"), url("~@/assets/images/bg_right.png");
      background-position: left top, right top;
      background-color: transparent;
      background-repeat: repeat-y;
    }
    .bottom-banner {
      background-image : url("~@/assets/images/bottomleft.png"), url("~@/assets/images/bottomright.png");
      background-position: left bottom, right bottom;
      background-color: transparent;
      background-repeat: no-repeat;
      height: 61px;
      width: 100%;
    }
</style>
