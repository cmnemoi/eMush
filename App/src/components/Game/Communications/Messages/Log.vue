<template>
    <section :class="'log ' + roomLog.visibility">
        <p class="text-log">
            <span v-html="formatLog(roomLog.message)"></span>
            <span class="room" v-if="roomLog?.place"> - {{ roomLog.place }}</span>
            <span class="timestamp">{{ roomLog?.date }}</span>
        </p>
    </section>
</template>

<script lang="ts">
import { formatText } from "@/utils/formatText";
import { RoomLog } from "@/entities/RoomLog";
import { defineComponent } from "vue";

export default defineComponent ({
    name: "Log",
    props: {
        roomLog: RoomLog
    },
    methods: {
        formatLog(value: string): string {
            if (! value) return '';
            return formatText(value.toString());
        }
    }
});
</script>return

<style lang="scss" scoped>

.log {
    position: relative;
    padding: 4px 5px;
    margin: 1px 0;
    border-bottom: 1px solid rgb(170, 212, 229);

    &::v-deep(p:not(.timestamp) em)  { color: $red; }

    &.new {
        border-left: 2px solid #ea9104;
        padding-left: 8px;

        &::after {
            content: "";
            position: absolute;
            top: 0;
            bottom: 0;
            left: -6px;
            min-height: 11px;
            width: 11px;
            background: transparent url('~@/assets/images/comms/thinklinked.png') center no-repeat;
        }
    }

    &.private {
        color: #98388a;
        font-style: italic;
    }

    &.covert,
    &.secret {
        border-radius: 3px;
        background: #88def8;
        font-style: italic;
        border: none;
    }

    &.revealed {
        background: #e29ec3;
        border: 1px solid #ff3f58;
        font-style: normal;
    }

    //Add corresponding icons next to the timestamp

    &.personnal,
    &.covert,
    &.secret,
    &.revealed {
        .timestamp::before {
            content: "";
            display: inline-block;
            margin-right: 4px;
            vertical-align: middle;
        }
    }

    &.personnal .timestamp::before {
        width: 16px;
        height: 16px;
        background: url('~@/assets/images/comms/personnal.png') center no-repeat;
    }

    &.covert .timestamp::before {
        width: 16px;
        height: 16px;
        background: url('~@/assets/images/comms/covert.png') center no-repeat;
    }

    &.secret .timestamp::before {
        width: 16px;
        height: 15px;
        background: url('~@/assets/images/comms/discrete.png') center no-repeat;
    }

    &.revealed .timestamp::before {
        width: 20px;
        height: 16px;
        background: url('~@/assets/images/comms/spotted.png') center no-repeat;
    }
}

.text-log {
    margin: 0;
    font-size: 0.92em;
}

.room { font-variant: small-caps; }

</style>
