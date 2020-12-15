<template>
  <div :class="'room-log ' + roomLog.visibility">
    <p class="text-log" v-html="format(roomLog.message)"></p>
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
    format: function (value) {
      // console.log(value)
      if (!value) return ''
      value = value.toString()
      value = value.replaceAll(/\*\*(\w*)\*\*/g, '<strong>$1&nbsp;</strong>');
      value = value.replaceAll(/:pa:/g, '<img src="'+require("@/assets/images/pa.png")+'" alt="pa">')
      return value.replaceAll(/:pm:/g, '<img src="'+require("@/assets/images/pm.png")+'" alt="pm">')
    }
  }
}
</script>return

<style lang="scss" scoped>
.room-log {
  position: relative;
  padding: 4px 5px;
  margin: 1px 0;
  border-bottom: 1px solid rgb(170, 212, 229);

  &.private {
    color: #98388A;
    font-style: italic;
  }

  &.covert, &.secret {
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

  &.personnal, &.covert, &.secret, &.spotted {
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

  &.secret .timestamp:before {
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

p {
  margin: 0;
  font-size: .95em;
  /deep/ img { vertical-align: middle; }
}


.timestamp {
  position: absolute;
  z-index: 2;
  right: 5px;
  bottom: 5px;
  font-size: .85em;
  font-style: italic;
  opacity: .5;
  float: right;
}




</style>