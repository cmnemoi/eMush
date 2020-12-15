<template>
  <div :class="'room-log ' + roomLog.visibility">
    <p class="text-log" v-html="roomLog.message"></p>
    <span class="timestamp">{{ formatDate(roomLog.date, {local: "fr-FR"}) }}</span>
  </div>
</template>

<script>
import {RoomLog} from "@/entities/RoomLog";
import formatDistanceToNow from 'date-fns/formatDistanceToNow'
import { fr } from 'date-fns/locale'

export default {
  name: "Log",
  props: {
    roomLog: RoomLog
  },
  methods: {
    formatDate: (date) => {
      return formatDistanceToNow(date, {locale : fr});
    },
  }
}
</script>

<style lang="scss" scoped>
.room-log {
  flex-direction: row;
  justify-content: space-between;
  padding: 4px 5px;
  margin: 1px 0;
  border-bottom: 1px solid rgb(170, 212, 229);
  max-width: 100%;
}
.private {
  color: #98388A;
  font-style: italic;
}

.covert, .secret {
  border-radius: 3px;
  background: #88def8;
  font-style: italic;
  border: none;

  .spotted {
    background: #e29ec3;
    border: 1px solid #ff3f58;
    font-style: normal;
  }
}

.private, .covert, .secret, .spotted {
  .timestamp:before {
    content: "";
    display: inline-block;
    margin-right: 4px;
    vertical-align: middle;
  }
}

.private .timestamp:before {
  width: 16px;
  height: 16px;
  background: url('~@/assets/images/comms/personnal.png') center no-repeat;
}

.covert .timestamp:before {
  width: 16px;
  height: 16px;
  background: url('~@/assets/images/comms/covert.png') center no-repeat;
}

.secret .timestamp:before {
  width: 16px;
  height: 15px;
  background: url('~@/assets/images/comms/discrete.png') center no-repeat;
}

.spotted .timestamp:before {
  width: 20px;
  height: 16px;
  background: url('~@/assets/images/comms/spotted.png') center no-repeat;
}
.timestamp {
  display: flex;
  max-width: 20%;
  right: 5px;
  bottom: 5px;
  font-size: .85em;
  font-style: italic;
  opacity: .5;
}
.text-log {
  display: flex;
  max-width: 80%;
}
p {
  margin: 0;
  font-size: .95em;
  img { vertical-align: middle; }
}

</style>