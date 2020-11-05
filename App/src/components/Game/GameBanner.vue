<template>
  <div class="game-banner">
    <div class="character-banner">
      <div class="in-game-level"><span class="text-level">{{characterLevel}}</span></div>
      <div class="character-info">
        <span class="character-name">{{characterName}}</span>
        <div class="titles">
          <div class="title"><img class="title-img" src="@/assets/images/title_01.png"><span class="title-name">Commandant</span></div>
        </div>
      </div>
    </div>
    <div class="daedalus-banner">

      <div class="daedalus-alarms-new">
        <ul>
          <li>Alertes :</li>
          <li><img src="@/assets/images/door.png"></li>
          <li><img src="@/assets/images/alert.png"></li>
          <li><img src="@/assets/images/hunter.png"></li>
        </ul>
      </div>


      <div class="daedalus-info">
        <ul>
          <li>25<img src="@/assets/images/o2.png"></li>
          <li>9<img src="@/assets/images/fuel.png"></li>
          <li>90<img src="@/assets/images/shield.png"></li>
          <li>125<img src="@/assets/images/triumph.png"></li>
        </ul>
      </div>
      <div class="daedalus-players">
        <ol>
          <li><img src="@/assets/images/p_alive.png"></li>
          <li><img src="@/assets/images/p_alive.png"></li>
          <li><img src="@/assets/images/p_alive.png"></li>
          <li><img src="@/assets/images/p_alive.png"></li>
          <li><img src="@/assets/images/p_alive.png"></li>
          <li><img src="@/assets/images/p_alive.png"></li>
          <li><img src="@/assets/images/p_alive.png"></li>
          <li><img src="@/assets/images/p_alive.png"></li>
          <li><img src="@/assets/images/p_alive.png"></li>
          <li><img src="@/assets/images/p_alive.png"></li>
          <li><img src="@/assets/images/p_alive.png"></li>
          <li><img src="@/assets/images/p_alive.png"></li>
          <li><img src="@/assets/images/p_mush.png"></li>
          <li><img src="@/assets/images/p_deadmush.png"></li>
          <li><img src="@/assets/images/p_dead.png"></li>
          <li><img src="@/assets/images/p_dead.png"></li>
        </ol>
      </div>
      <div class="cycle-time">
        <img class="casio-img" src="@/assets/images/casio.png">
        <span class="cycle-time-left">2h08m05s</span>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "GameBanner",
  props: {
    characterLevel: Number,
    characterName: String
  }
}
</script>

<style scoped>
</style>