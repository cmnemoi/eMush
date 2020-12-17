<template>
  <div class="chatbox-container" id="room-events-tab">

    <div class="unit" v-for="(cycleRoomLog, id) in roomLogs.slice().reverse()" v-bind:key="id">
      <div class="cycle-banner">
        <span>Jour {{ cycleRoomLog.day }} Cycle {{cycleRoomLog.cycle}}</span>
      </div>
      <div class="cycle-events">
        <Log v-for="(roomLog, id) in cycleRoomLog.roomLogs" v-bind:key="id" :room-log="roomLog"></Log>
      </div>
    </div>
  </div>
</template>

<script>
import {Room} from "@/entities/Room";
import ApiService from "@/services/api.service";
import {RoomLog} from "@/entities/RoomLog";
import Log from "@/components/Game/Communications/Messages/Log";

export default {
  name: "RoomEventsTab",
  components: {Log},
  props: {
    room: Room
  },
  data: () => {
    return {
      roomLogs: [],
    }
  },
  beforeMount() {
    ApiService.get(process.env.VUE_APP_API_URL+'room-log')
        .then(
            (result) => {
              if (result.data) {
                const days = result.data;
                Object.keys(days).map((day) => {
                  Object.keys(days[day]).map((cycle) => {
                    let roomLogs = []
                    days[day][cycle].forEach((value) => {
                      let roomLog = (new RoomLog()).load(value);
                      roomLogs.push(roomLog);
                    })
                    this.roomLogs.push({
                      "day": day,
                      "cycle": cycle,
                      roomLogs
                    })
                  });
                });
              }
            }
        )
        .catch((error) => {console.error(error)})
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