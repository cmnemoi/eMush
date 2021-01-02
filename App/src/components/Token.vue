<template>
  <div>
    <p v-if="errorMessage">{{ errorMessage }}</p>
  </div>
</template>

<script>
import {mapActions} from "vuex";
import router from "@/router";

export default {
  name: "Token",
  data: () => {
    return {
      errorMessage: ''
    }
  },
  methods: {
    ...mapActions('auth', [
      'login',
    ]),
  },
  beforeMount() {
    if (typeof this.$route.query.code !== 'undefined') {
      if (this.login({code: this.$route.query.code})) {
        router.push({ name: 'GamePage' })
      }
    }

    if (typeof this.$route.query.error !== 'undefined') {
      this.errorMessage= this.$route.query.error;
    }
  }
}
</script>

<style scoped>

</style>