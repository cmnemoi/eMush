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
      <div class="daedalus-alarms-left"></div>
      <div class="daedalus-alarms">
        <ul>
          <li>Alertes :</li>
          <li><img src="@/assets/images/door.png"></li>
          <li><img src="@/assets/images/alert.png"></li>
          <li><img src="@/assets/images/hunter.png"></li>
        </ul>
      </div>
      <div class="daedalus-alarms-right"></div>
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
          <li><img src="@/assets/images/p_deadmush.png"></li>
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
.titles, .title, .game-banner, .daedalus-banner, .character-banner, .daedalus-players, .cycle-time {
  flex-direction: row;
  font-size: 10pt;
  font-variant: small-caps;
}

.casio-img {
  width: 15px;
  height: 18px;
}

.daedalus-banner {
  padding-top: 3px;
}

.daedalus-players {
  margin-left: 13px;
}

.cycle-time {
  width: 80px;
  margin-left: 13px;
  margin-right: 13px;
}

.daedalus-alarms {
  padding-top: 3px;
  height: 25px;
  background: transparent url("~@/assets/images/alarm_on_bg.gif") repeat-x left top;
  color: #ff4e64;
  text-shadow: -1px 0 2px rgba(0,0,0,0.5), 0 1px 2px rgba(0,0,0,0.5), 1px 0 2px rgba(0,0,0,0.5);
  font-weight: bold;
}

.daedalus-alarms ul {
  align-items: center;
  justify-content: center;
}
.daedalus-alarms li {
  display: flex;
  flex-grow: 1;
  justify-content: center;
  margin-top: 2px;
  margin-right: 7px;
  margin-left: 4px;
  text-align: center;
  font-size: 10pt;
  font-variant: small-caps;
  align-items: end;
}
.daedalus-info li {
  display: flex;
  flex-grow: 1;
  justify-content: center;
  margin-top: 2px;
  margin-left: 13px;
  margin-right: 4px;
  text-align: center;
  font-size: 10pt;
  font-variant: small-caps;
  align-items: end;
}
.daedalus-info img {
  margin-left: 4px;
}

.daedalus-alarms-left {
  padding-top: 1px;
  height: 26px;
  width: 4px;
  background: transparent url("~@/assets/images/alarm_on_left.gif") repeat-x left top;
  color: #ff4e64;
  text-shadow: -1px 0 2px rgba(0,0,0,0.5), 0 1px 2px rgba(0,0,0,0.5), 1px 0 2px rgba(0,0,0,0.5);
  font-weight: bold;
}

.daedalus-alarms-right {
  padding-top: 1px;
  height: 26px;
  width: 4px;
  background: transparent url("~@/assets/images/alarm_on_right.gif") repeat-x left top;
  color: #ff4e64;
  text-shadow: -1px 0 2px rgba(0,0,0,0.5), 0 1px 2px rgba(0,0,0,0.5), 1px 0 2px rgba(0,0,0,0.5);
  font-weight: bold;
}

.title-img {
  width: 16px;
  height: 13px;
  vertical-align: -3px;
  margin-right: 4px;
}
.title-name {
  font-size: 8pt;
  font-variant: small-caps;
  font-weight: bold;
  white-space: nowrap;
  color: #FFFFFF;
  color: rgba(255,255,255,0.7);
}

.game-banner {
  padding: 12px;
  flex-direction: row;
  justify-content: space-between;
}

.text-level {
  text-align: center;
  width: 41px;
  margin-top: 2px;
}

.in-game-level {
  margin-right: 6px;
  width: 52px;
  height: 35px;
  font-size: 17pt;
  background: transparent url("~@/assets/images/level_ingame_on.png") no-repeat;
}
.character-info {
  margin-left: 2px;
}

.character-name {
  height: 18px;
  font-size: 11pt;
  text-transform: uppercase;
  padding: 0;
  font-weight: 700;
}
</style>