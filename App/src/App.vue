<template>
  <div class="main-container">
    <Banner></Banner>
    <GamePage></GamePage>
  </div>
</template>

<script>

import Banner from "@/components/Banner";
import GamePage from "@/components/GamePage";

export default {
  name: 'App',
  components: {
    GamePage,
    Banner
  }
}
</script>

<style>
</style>
