<template>
  <div class="comms-panel">
    <label v-for="(channel, id) in getChannels"
           v-bind:key="id"
           @click="changeChannel({channel: channel})"
           :class="getCurrentChannel === channel ? 'checked' : ''"
    >
      <img :src="channelIcon(channel)">
    </label>
<!--    <label @click="createPrivateChannel">-->
<!--      <img :src="require('@/assets/images/comms/newtab.png')">-->
<!--    </label>-->
    <div class="cycle-time"><img src="@/assets/images/comms/calendar.png"><span>Jour {{ day }} - Cycle {{ cycle }}</span></div>

    <div class="tabs-content">
      <component v-bind:is="currentTabComponent(getCurrentChannel)" :channel="getCurrentChannel"></component>
    </div>

  </div>
</template>

<script>
import TipsTab from "@/components/Game/Communications/TipsTab";
import RoomEventsTab from "@/components/Game/Communications/RoomEventsTab";
import DiscussionTab from "@/components/Game/Communications/DiscussionTab";
import {Room} from "@/entities/Room";
import {mapActions, mapGetters} from "vuex";
import {PRIVATE, PUBLIC, ROOM_LOG, TIPS} from '@/enums/communication.enum';
import {Channel} from "@/entities/Channel";


export default {
  name: "CommsPanel",
  components: {
    TipsTab,
    DiscussionTab,
    RoomEventsTab
  },
  props: {
    day: Number,
    cycle: Number,
    room: Room
  },
  computed: {
    ...mapGetters('communication', [
      'getCurrentChannel',
      'getChannels',
    ])
  },
  methods: {
    ...mapActions('communication', [
      'loadChannels',
      'changeChannel',
      'createPrivateChannel'
    ]),
    currentTabComponent: (channel) => {
      if (channel instanceof Channel) {
        switch (channel.scope) {
          case TIPS:
            return TipsTab;
          case ROOM_LOG:
            return RoomEventsTab;
          case PUBLIC:
          default:
            return DiscussionTab;
        }
      }
      return DiscussionTab;
    },
    channelIcon: (channel) => {
      if (channel instanceof Channel) {
        switch (channel.scope) {
          case TIPS:
            return require('@/assets/images/comms/tip.png');
          case ROOM_LOG:
            return require('@/assets/images/comms/local.png');
          case PRIVATE:
            return require('@/assets/images/comms/private.png');
          case PUBLIC:
          default:
            return require('@/assets/images/comms/wall.png');
        }
      }
      return DiscussionTab;
    }
  },
  beforeMount() {
    this.loadChannels();
  }
}
</script>

<style lang="scss" scoped>

#tips-input:checked ~ .tabs-content #tips-tab,
#room-events-input:checked ~ .tabs-content #room-events-tab,
#discussion-input:checked ~ .tabs-content #discussion-tab {
  display: flex;
  visibility: visible;
}



.comms-panel {
  position: relative;
  display: block;
  width: 404px;
  height: 460px;

  & .checked {
    opacity: 1;
    &::after { background: rgba(194, 243, 252, 1); }
  }

  & input {
    position: absolute;
    left: -100vw;
  }

  & .tabs-content {
    min-width: 100%;
    font-size: .8em;
  }


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