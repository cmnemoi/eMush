<template>
    <div id="room-events-tab" class="chatbox-container">
        <section v-for="(cycleRoomLog, id) in roomLogs.slice().reverse()" :key="id" class="unit">
            <div class="banner cycle-banner">
                <img class="expand" src="@/assets/images/comms/less.png">
                <span>Jour {{ cycleRoomLog.day }} Cycle {{ cycleRoomLog.cycle }}</span>
            </div>
            <div class="cycle-events">
                <Log v-for="(roomLog, id) in cycleRoomLog.roomLogs" :key="id" :room-log="roomLog" />
            </div>
        </section>
    </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";
import { Channel } from "@/entities/Channel";
import Log from "@/components/Game/Communications/Messages/Log";

export default {
    name: "RoomEventsTab",
    components: { Log },
    props: {
        channel: Channel
    },
    computed: {
        ...mapGetters('communication', [
            'messages'
        ]),
        roomLogs() {
            return this.messages;
        }
    },
    beforeMount() {
        this.loadMessages({ channel: this.channel });
    },
    methods: {
        ...mapActions('communication', [
            'loadMessages'
        ])
    }
};
</script>

<style lang="scss" scoped>

#room-events-tab {
    overflow: auto;
    padding: 7px;

    .unit { padding: 1px 0 !important; }
}

</style>
