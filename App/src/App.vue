<template>
  <body>
    <Banner></Banner>
    <GamePanel></GamePanel>
  </body>
</template>

<script>

import Banner from "@/components/Banner";
import GamePanel from "@/components/GamePanel";

export default {
  name: 'App',
  components: {
    GamePanel,
    Banner
  }
}
</script>

<style>
body {
  fixed: left;
  color : #fff;
  background : #0f0f43 url("~@/assets/images/bg.jpg") no-repeat center 23px;
}
</style>
