<template>
<div class="comms-panel">

  <input type="radio" id="tips-input" name="comms_tabs">
  <input type="radio" id="room-events-input" name="comms_tabs">
  <input type="radio" id="discussion-input" name="comms_tabs" checked>

  <label for="tips-input"><img src="@/assets/images/comms/tip.png"></label>
  <label for="room-events-input"><img src="@/assets/images/comms/local.png"></label>
  <label for="discussion-input"><img src="@/assets/images/comms/wall.png"></label>

  <div class="cycle-time"><img src="@/assets/images/comms/calendar.png"><span>Jour {{ day }} - Cycle {{ cycle }}</span></div>

  <div class="tabs-content">
    <RoomEventsTab></RoomEventsTab>
    <DiscussionTab></DiscussionTab>
  </div>

</div>
</template>

<script>
import DiscussionTab from "@/components/Game/Communications/DiscussionTab";
import RoomEventsTab from "@/components/Game/Communications/RoomEventsTab";


export default {
  name: "CommsPanel",
  components: {
    DiscussionTab,
    RoomEventsTab
  },
  props: {
    day: Number,
    cycle: Number
  }
}
</script>

<style lang="scss" scoped>

.comms-panel {
  position: relative;
  display: block;
  width: 404px;
  height: 460px;

  & input {
    position: absolute;
    left: -100vw;
  }
  
  & .tabs-content { min-width: 100%; }
  

  & label {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: visible;
    float: left;
    cursor: pointer;
    width: 31px;
    min-height: 25px;
    margin-right: 4px;
    & * { z-index: 2; }

    &::after {
      content: "";
      z-index: 1;
      position: absolute;
      width: 100%;
      height: 100%;
      background: #213578;
      @include corner-bezel(4.5px, 4.5px, 0px);
    }

    &:last-child {
      opacity: .3;
      &::after { background: none !important; }
    }

    &.active, &:hover, &:focus {
      opacity: 1;
      &::after { background: rgba(194, 243, 252, 1); }
    }

    & span {
      position: absolute;
      top: -6px;
      right: 3px;
      font-size: .82em;
      font-weight: 600;
      text-shadow: 0 0 3px black, 0 0 3px black, 0 0 3px black;
    }
  }

  & .cycle-time {
    flex-direction: row;
    align-items: center;
    margin: 0 12px;
    min-height: 25px;
    float: right;
    font-size: .7em;
    font-variant: small-caps;

    & img { margin-right: 3px; }
  }

/* SCROLLBAR STYLING */

  & .chatbox, {
    --scrollbarBG: white;
    --thumbBG: #090a61;

    scrollbar-width: thin;
    scrollbar-color: var(--thumbBG) var(--scrollbarBG);
    &::-webkit-scrollbar { width: 6px; }
    &::-webkit-scrollbar-track { background: var(--scrollbarBG); }
    &::-webkit-scrollbar-thumb { background-color: var(--thumbBG); }
  }
}

</style>