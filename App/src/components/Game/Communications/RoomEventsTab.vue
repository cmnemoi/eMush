<template>
    <TabContainer :channel="channel">
        <section v-for="(cycleRoomLog, id) in roomLogs.slice().reverse()" :key="id" class="unit">
            <div class="banner cycle-banner">
                <img class="expand" src="@/assets/images/comms/less.png">
                <span>{{ calendar?.dayName }} {{ calendar?.day }} {{ calendar?.cycleName }} {{ calendar?.cycle }}</span>
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
import { Message } from "@/entities/Message";
import { defineComponent } from "vue";
import { GameCalendar } from "@/entities/GameCalendar";

export default defineComponent ({
    name: "RoomEventsTab",
    components: {
        Log,
        TabContainer
    },
    props: {
        channel: Channel,
        calendar: GameCalendar,
    },
    computed: {
        ...mapGetters('communication', [
            'messages'
        ]),
        roomLogs(): Message[] {
            return this.messages;
        }
    },
    methods: {
        ...mapActions('communication', [
            'loadMessages'
        ])
    }
});
</script>

<style lang="scss" scoped>

.unit {
    padding: 1px 0 !important;
}

</style>
