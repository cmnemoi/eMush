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
      'login',
    ]),
  },
  beforeMount() {
    if (this.$route.query.token !== 'undefined') {
      this.login({code: this.$route.query.code})
      router.push({ name: 'GamePage' })
    }
  }
}
</script>

<style scoped>

</style>