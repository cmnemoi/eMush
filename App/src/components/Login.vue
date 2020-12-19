<template>
  <a href="#" @click="showLogin = true" v-if="!loggedIn">Login</a>
  <a href="/#" @click="logout" v-if="loggedIn">Logout</a>
  <div id="login-modal" class="modal-window" v-show="showLogin" >
<!--    <div>-->
<!--      <a href="#" title="Close" class="modal-close" @click="showLogin = false">Close</a>-->
<!--      <label for="login">-->
<!--        Login-->
<!--      </label>-->
<!--      <input id="login" type="text" placeholder="login" v-model="email"/>-->
<!--      <button type="submit" @click="handleSubmit">{{ ('form.submit') }}</button>-->
<!--    </div>-->
    <form method="POST" action="http://localhost:8080/api/v1/login/redirect">
      <input type="hidden" name="frontend_uri" value="http://localhost" />
      <input type="submit" value="Se connecter" />
    </form>
  </div>
</template>

<script>
import {mapActions, mapGetters} from "vuex";

export default {
  name: 'Login',
  data() {
    return {
      showLogin: false,
      email: "",
      loginError: null,
    };
  },
  computed: {
    ...mapGetters('auth', [
      'loggedIn',
    ])
  },
  methods: {
    ...mapActions('auth', [
      'login',
      'logout',
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
              this.email = null;
              this.showLogin = false;
            })
        this.password = ""
      }
    }
  }
}
</script>

<style lang="scss" scoped>
a {
  margin: 0 20px;
  padding: 5px 10px;
  color: white;

  &:hover, &:active {
    color: #dffaff;
    text-shadow: 0 0px 1px rgb(255,255,255), 0 0px 1px rgb(255,255,255);
  }
}
.modal-window {
  position: fixed;
  background: transparentize(#09092d, .4);
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 999;
  transition: all 0.3s;

  & > div { /* modal box */
    min-width: 400px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    padding: 2em;
    margin: 0 12px 8px 12px;
    background-color: #191a4c;
    border-radius: 3px;
    border: 1px solid #3965fb;
    box-shadow:
        0 0 0 1px #191a4c,
        0px 0px 5px 1px rgba(57, 101, 251, 0.7),
        0px 12px 8px -6px rgba(0, 0, 0, 0.7);
  }

  h1 {
    margin: 0 0 15px;
    font-size: 150%;
    font-variant: small-caps;
  }
}

input {
  margin: 7px 0;
  padding: 5px 8px;
  font-style: italic;
  opacity: .85;
  box-shadow: 0px 1px 0px rgba(255, 255, 255, .3);
  border: 1px solid #aad4e5;
  border-radius: 3px;
  &:active, &:focus { font-style: initial; opacity: 1; }
}

button {
  @include button-style(1em);
  margin: 7px 0;
  padding-top: 4px;
  padding-bottom: 6px;
  border: 0;
}

.modal-close {
  position: absolute;
  text-align: center;
  right: 0;
  top: 0;
  padding: 12px;
  color: transparentize(white, .4);
  font-size: 80%;
  letter-spacing: .03em;
  text-decoration: none;
  font-variant: small-caps;
  transition: all 0.15s;
  &:hover {
    color: white;
  }
}


</style>



