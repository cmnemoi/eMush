<template>
  <div class="main-container">
    <Banner></Banner>
    <router-view />
  </div>
</template>

<script>

import Banner from "@/components/Banner";

export default {
  name: 'App',
  components: {
    Banner
  }
}
</script>

<style>
</style>
