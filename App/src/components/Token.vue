<template>
  <div></div>
</template>

<script>
import {mapActions} from "vuex";
import router from "@/router";

export default {
  name: "Token",
  methods: {
    ...mapActions('auth', [
      'setToken',
    ]),
  },
  beforeMount() {
    if (this.$route.query.token !== 'undefined') {
      this.setToken({token: this.$route.query.token})
      router.push({ name: 'GamePage' })
    }
  }
}
</script>

<style scoped>

</style>