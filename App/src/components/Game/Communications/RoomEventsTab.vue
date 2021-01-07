<template>
  <div class="chatbox-container" id="room-events-tab">

    <section class="unit" v-for="(cycleRoomLog, id) in roomLogs.slice().reverse()" v-bind:key="id">
      <div class="banner cycle-banner">
        <img class="expand" src="@/assets/images/comms/less.png">
        <span>Jour {{ cycleRoomLog.day }} Cycle {{cycleRoomLog.cycle}}</span>
      </div>
      <div class="cycle-events">
        <Log v-for="(roomLog, id) in cycleRoomLog.roomLogs" v-bind:key="id" :room-log="roomLog"></Log>
      </div>
    </section>
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
  overflow: auto;
  padding: 7px;

  .unit { padding: 1px 0 !important; }
}

</style>