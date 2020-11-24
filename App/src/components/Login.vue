<template>
  <div id="login-modal" class="modal-window">
    <div>
      <a href="#" title="Close" class="modal-close">Close</a>
      <label for="login"><h1>Login</h1></label><input id="login" type="text" placeholder="login" v-model="email"/>
      <button type="submit" @click="handleSubmit">{{ ('form.submit') }}</button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Login',
  props: {
  }
}
</script>

<style lang="scss" scoped>

.modal-window {
  position: fixed;
  background: transparentize(#09092d, .4);
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  z-index: 999;
  visibility: hidden;
  opacity: 0;
  pointer-events: none;
  transition: all 0.3s;
  &:target {
    visibility: visible;
    opacity: 1;
    pointer-events: auto;
  }

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



