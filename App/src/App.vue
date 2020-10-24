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


.main-container {
  color : #fff;
  flex-grow: 1;
  height: 100%;
  background : #0f0f43 url("~@/assets/images/bg.jpg") no-repeat center 23px;
}
</style>
