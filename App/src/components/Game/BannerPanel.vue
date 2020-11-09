<template>
  <div class="game-banner">
    <div class="character-banner">
      <a class="in-game-level" href="#">{{characterLevel}}</a>
      <div class="in-game-level-progress">
        <div></div>
      </div>
      <div class="character-info">
        <span class="character-name">{{characterName}}</span>
        <div class="titles">
          <div class="title"><img class="title-img" src="@/assets/images/title_01.png"><span class="title-name">Commandant</span></div>
        </div>
      </div>
    </div>

    <div class="daedalus-banner">

      <div class="daedalus-alarms">
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
  name: "BannerPanel",
  props: {
    characterLevel: Number,
    characterName: String
  }
}
</script>

<style  lang="scss" scoped>

.titles, .title, .game-banner, .daedalus-banner, .character-banner, .daedalus-players, .cycle-time {
  flex-direction: row;

}

p, a, li, span {
  font-size: .85em;
  letter-spacing: .03em;
  font-variant: small-caps;
}

.game-banner {
  margin-bottom: 8px;
  flex-direction: row;
  justify-content: space-between;
  align-items: baseline;
}

.character-banner {
  display: flex;
  flex-direction: row;

  & .in-game-level {

    --bezel-size: 11.5px;

    display: flex;
    align-items: center;
    justify-content: center;

    width: 52px;
    height: 35px;
    font-size: 17pt;
    border: 1px solid rgba(4,21,156,1);
    border-radius: 4px;
    background: rgb(0,60,195);
    background: linear-gradient(0deg, rgba(0,60,195,1) 2%, rgba(0,116,223,1) 6%, rgba(0,116,223,1) 46%, rgba(0,142,229,1) 54%, rgba(0,142,229,1) 94%, rgba(73,156,229,1) 96%);

    clip-path: polygon(
      var(--bezel-size) 0,
      100% 0,
      100% 100%,
      0 100%,
      0 var(--bezel-size)
    );

    margin: auto;
    padding-right: 4px;
    font-family: Days-One;
    font-weight: 700;
    color: white;
    text-decoration: none;
    text-shadow: 0 0 4px #15273C;
  }

  & .in-game-level-progress {
    position: relative;
    width: 11px;
    height: 33px;
    z-index: 2;
    margin: 1px;
    margin-left: -10px;
    border-radius: 4px;
    border: 1px solid #4077B5;
    background: #4077B5;
    box-shadow: 0 0 5px 1px #15273C inset, 0 0 0 1px #234164;

    & > div {
      position: relative;
      width: 100%;
      height: 100%;
      border-radius: 3px;
      background: rgb(128,72,112);
      background: linear-gradient(90deg, rgba(255,137,204,1) 0%, rgba(255,137,204,1) 25%, rgba(213,38,89,1) 94%, rgba(128,18,47,1) 100%);
      box-shadow: 0 0 2px 1px rgba(71,10,26,.7) inset;
    }

    &::after {
      content: "";
      position: absolute;
      bottom: 2px;
      width: 9px;
      height: 11px;
      background : transparent url('~@/assets/images/levelup_arrow_ingame.png') no-repeat center; 
    }
  }

  & .character-info {
    display: flex;
    justify-content: center;
    margin-left: 6px;

    & .character-name {
      margin-bottom: 1px;
      font-size: .91em;
      font-weight: 700;
      text-transform: uppercase;
    }

    & .title {
      display: flex;
      flex-direction: row;
      align-items: center;

      & .title-name {
        margin-left: 4px;
        font-size: .68em;
        font-weight: bold;
        font-variant: small-caps;
        white-space: nowrap;
        color: rgba(255,255,255,0.75);
      }
    }
  }
}



.daedalus-banner {
  align-items: center;

  & > div {
    margin: 0 12px;
    display:flex;
    align-items: center;
  }

  & .daedalus-alarms {
    padding: 3px 6px 1px 6px;
    max-height: 25px;
    border: 1px solid #ff4e64;
    border-radius: 3px;
    background: rgba(58,106,171,1);
    box-shadow: 0 0 5px 1px inset rgba(28,29,56,1);

    animation: alarms-border-color .85s ease-in-out infinite; /* keyframes at the end of the doc */

    color: #ff4e64;
    text-shadow: 0 0 2px rgba(0,0,0,1), 0 0 2px rgba(0,0,0,1); /* twice the same shadow */
    font-weight: bold;

    & li:not(:last-child) {
      margin-right: 10px;
    }
  }

  & .daedalus-info li {
    display: flex;
    align-items: center;
  
      &:not(:last-child) {
      padding-right: 1em;
    }

    & img {
      padding: 2px 0 0 4px;
    }
  }

  & .cycle-time img {
    margin-right: .4em;
  }
}

@keyframes alarms-border-color {
  0%   {border: 1px solid #ff4e64;}
  50%  {border: 1px solid rgba(58,106,171,1);}
  100% {border: 1px solid #ff4e64;}
}

</style>