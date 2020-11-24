<template>
  <div v-if="loggedIn">
    <div v-if="getUserInfo.currentGame !== null">
      <GameContent :player-id="getUserInfo.currentGame"></GameContent>
    </div>
    <div v-else>
      <CharSelection></CharSelection>
    </div>
  </div>
  <div v-else>
    <div class="row">
      <label for="login">Login</label><input id="login" type="text" placeholder="login" v-model="email"/>
      <button type="submit" @click="handleSubmit">{{ ('form.submit') }}</button>
    </div>
  </div>
</template>

<script>
import {mapActions, mapGetters} from "vuex";
import GameContent from "@/components/Game/GameContent";
import CharSelection from "@/components/CharSelection";

export default {
  name: "GamePage",
  components: {
    GameContent,
    CharSelection
  },
  data() {
    return {
      email: "",
      loginError: null,
    };
  },
  computed: {
    ...mapGetters('auth', [
      'loggedIn',
      'getUserInfo',
    ])
  },
  beforeMount() {
  },
  methods: {
    ...mapActions('auth', [
      'login',
      'userInfo'
    ]),
    handleSubmit() {
      // Perform a simple validation that email and password have been typed in
      if (this.email !== '') {
        this.submitted = true;
        this.login({email: this.email})
            .then((success) => {
              if (success) {
                this.userInfo();
              }  else {
                this.loginError = 'login.invalid'
              }
              this.submitted = false;
            })
        this.password = ""
      }
    }
  }
}
</script>

<style scoped>

</style>