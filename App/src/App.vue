<template>
  <div class="main-container">
    <Banner></Banner>
    <GamePanel></GamePanel>
  </div>
</template>

<script>

import Banner from "@/components/Banner";
import GamePanel from "@/components/Game/GamePanel";

export default {
  name: 'App',
  components: {
    GamePanel,
    Banner
  }
}
</script>

<style>
</style>
