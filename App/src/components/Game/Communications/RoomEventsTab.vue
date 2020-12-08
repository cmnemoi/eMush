<template>
<div class="chatbox-container" id="room-events-tab">

  <div class="unit">
    <div class="cycle-banner">
      <span>Jour 1 Cycle 6</span>
    </div>
    <ul class="cycle-events">
      <li class="personnal">
        <p><img src="@/assets/images/triumph.png" alt="triumph"> Cette action remplit les objectifs personnels de votre personnage. Vous gagnez <strong>1 Triomphe</strong>.</p>
        <span class="timestamp">moments ago</span>
      </li>
      <li class="personnal">
        <p>Vous avez gagné 2 <img src="@/assets/images/xp.png" alt="xp">.</p>
        <span class="timestamp">moments ago</span>
      </li>
      <li class="personnal">
        <p>Vous avez gagné 1 <img src="@/assets/images/pm.png" alt="pm">.</p>
        <span class="timestamp">moments ago</span>
      </li>
      <li class="personnal">
        <p>Vous avez gagné 1 <img src="@/assets/images/pa.png" alt="pa">.</p>
        <span class="timestamp">moments ago</span>
      </li>
    </ul>
  </div>

  <div class="unit">
    <div class="cycle-banner">
      <span>Jour 1 Cycle 5</span>
    </div>
    <ul class="cycle-events">
      <li class="discrete spotted">
        <p><strong>Janice</strong> souffle sur <strong>Stephen</strong> un nuage étrange...</p>
        <span class="timestamp">24min</span>
      </li>
      <li class="discrete">
        <p><strong>Janice</strong> souffle sur <strong>Stephen</strong> un nuage étrange...</p>
        <span class="timestamp">26min</span>
      </li>
      <li class="covert">
        <p><strong>Janice</strong> souffle sur <strong>Stephen</strong> un nuage étrange...</p>
        <span class="timestamp">29min</span>
      </li>
      <li>
        <p><strong>Derek</strong> s'allonge... Ça fait du bien de s'arrêter. Allez direction le pays des rêves !</p>
        <span class="timestamp">36min</span>
      </li>
    </ul>
  </div>

  <div class="unit">
    <div class="cycle-banner">
      <span>Jour 1 Cycle 4</span>
    </div>
    <ul class="cycle-events">
      <li>
        <p><strong>Stephen</strong> s'est éveillé de son si doux sommeil.</p>
        <span class="timestamp">~1h</span>
      </li>
      <li>
        <p><strong>Roland</strong> est sorti.</p>
        <span class="timestamp">~1h</span>
      </li>
      <li>
        <p><strong>Ian</strong> a lâché un <strong>Bananier</strong>.</p>
        <span class="timestamp">~1h</span>
      </li>
      <li>
        <p><strong>Ian</strong> a lâché un <strong>Bananier</strong>.</p>
        <span class="timestamp">~4h</span>
      </li>
      <li>
        <p><strong>Ian</strong> est entré.</p>
        <span class="timestamp">~4h</span>
      </li>
      <li>
        <p><strong>Ian</strong> est sorti.</p>
        <span class="timestamp">~4h</span>
      </li>
      <li>
        <p><strong>Ian</strong> a lâché une <strong>HydroPot</strong>.</p>
        <span class="timestamp">~5h</span>
      </li>
      <li>
        <p><strong>Jin Su</strong> s'est éveillé de son si doux sommeil.</p>
        <span class="timestamp">~5h</span>
      </li>
    </ul>
  </div>

</div>
</template>

<script>
export default {
  name: "RoomEventsTab",
  props: {
  }
}
</script>

<style lang="scss" scoped>

#room-events-tab {
  position: relative;
  overflow: auto;
  z-index: 2;
  height: 436px;
  margin-top: -1px;
  padding: 4px 0;
  line-height: initial;
  background: rgba(194, 243, 252, 1);
  color: #090a61;
  @include corner-bezel(0px, 6.5px, 0px);

  display: none;
  visibility: hidden;

  & .unit {
    display: block;
    padding: 1px 6px;

    & .cycle-banner {
      min-height: 24px;
      justify-content: center;
      text-align: center;
      text-transform: uppercase;
      letter-spacing: .03em;
      background: #A6EEFB;
    }

    & ul {
      flex-direction: column;

      & li {
        position: relative;
        padding: 4px 5px;
        margin: 1px 0;
        border-bottom: 1px solid rgb(170, 212, 229);

        &.personnal {
          color: #98388A;
          font-style: italic;
        }

        &.covert, &.discrete {
          border-radius: 3px;
          background: #88def8;
          font-style: italic;
          border: none;
          
          &.spotted {
            background: #e29ec3;
            border: 1px solid #ff3f58;
            font-style: normal;
          }
        }

        &.personnal, &.covert, &.discrete, &.spotted {
          & .timestamp:before {
            content: "";
            display: inline-block;
            margin-right: 4px;
            vertical-align: middle;
          }
        }

        &.personnal .timestamp:before {
          width: 16px;
          height: 16px;
          background: url('~@/assets/images/comms/personnal.png') center no-repeat;
        }

        &.covert .timestamp:before {
          width: 16px;
          height: 16px;
          background: url('~@/assets/images/comms/covert.png') center no-repeat;
        }

        &.discrete .timestamp:before {
          width: 16px;
          height: 15px;
          background: url('~@/assets/images/comms/discrete.png') center no-repeat;
        }

        &.spotted .timestamp:before {
          width: 20px;
          height: 16px;
          background: url('~@/assets/images/comms/spotted.png') center no-repeat;
        }
      }

      & p {
        margin: 0;
        font-size: .95em;
        & img { vertical-align: middle; }
        }
    }

    & .timestamp {
      position: absolute;
      z-index: 2;
      right: 5px;
      bottom: 5px;
      font-size: .85em;
      font-style: italic;
      opacity: .5;
      float: right;
    }
  }
}

/* SCROLLBAR STYLING */

.chatbox-container, {
  --scrollbarBG: white;
  --thumbBG: #090a61;

  scrollbar-width: thin;
  scrollbar-color: var(--thumbBG) var(--scrollbarBG);
  &::-webkit-scrollbar { width: 6px; }
  &::-webkit-scrollbar-track { background: var(--scrollbarBG); }
  &::-webkit-scrollbar-thumb { background-color: var(--thumbBG); }
}

</style>