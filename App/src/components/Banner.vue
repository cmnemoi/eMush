<template>
  <div class="banner">
    <div class="tid_shadow"></div>
    <div class="logo">
      <a href="/" class="logo"><img src="@/assets/images/logo.png" alt=""></a>
    </div>
    <div class="mainmenu">
      <a href="/#">Daedalus</a>
      <a href="/#">Mon Compte</a>
      <a href="/#">Castings</a>
      <a href="/#">Classement</a>
      <a href="/#">Forum</a>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Banner',
  props: {
    msg: String
  }
}
</script>

<style scoped>
.banner {
  padding-top: 25px;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.mainmenu {
  display: flex;
  flex-direction: row;
}

.logo {
  height: 100%;
}

.mainmenu > a {
  font-family: "Segoe UI", "Lucida Grande", "Trebuchet MS", Arial, "lucida sans unicode", sans-serif;
  font-weight: normal;
  text-transform: capitalize;
  font-size: 11pt;
  color: #dffaff;
  letter-spacing: 1px;
  text-decoration: none;
  margin: 0 30px;
}

.tid_shadow {
  top: 32px;
  width: 100%;
  height: 10px;
  background-image: url("~@/assets/images/shadow.png");
  background-position: left bottom;
}

a.logo {
  height: 121px;
  margin: auto;
}
</style>
