<template>
    <TabContainer :channel="channel">
        <section v-for="(cycleRoomLog, id) in roomLogs.slice().reverse()" :key="id" class="unit">
            <div class="banner cycle-banner">
                <img class="expand" :src="getImgUrl('comms/less.png')">
                <span>{{ calendar?.dayName }} {{ cycleRoomLog.day }} {{ calendar?.cycleName }} {{ cycleRoomLog.cycle }}</span>
            </div>
            <div class="cycle-events">
                <Log v-for="(roomLog, id) in cycleRoomLog.roomLogs" :key="id" :room-log="roomLog" />
            </div>
        </section>
    </TabContainer>
</template>

<script lang="ts">
import { mapActions, mapGetters } from "vuex";
import { Channel } from "@/entities/Channel";
import Log from "@/components/Game/Communications/Messages/Log.vue";
import TabContainer from "@/components/Game/Communications/TabContainer.vue";
import { defineComponent } from "vue";
import { GameCalendar } from "@/entities/GameCalendar";
import { RoomLog } from "@/entities/RoomLog";
import { getImgUrl } from "@/utils/getImgUrl";

export default defineComponent ({
    name: "RoomEventsTab",
    components: {
        Log,
        TabContainer
    },
    props: {
        channel: Channel,
        calendar: GameCalendar
    },
    computed: {
        ...mapGetters('communication', [
            'messages'
        ]),
        roomLogs(): { day: number, cycle: number, roomLogs: RoomLog[] }[] {
            return this.messages;
        }
    },
    methods: {
        ...mapActions('communication', [
            'loadMessages'
        ]),
        getImgUrl
    }
});
</script>

<style lang="scss" scoped>

.unit {
    padding: 1px 0 !important;
}

.cycle-events {
    &::v-deep(a) {
        color: $green;
    }
}

</style>
