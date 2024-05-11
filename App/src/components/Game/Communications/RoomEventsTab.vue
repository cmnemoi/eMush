<template>
    <TabContainer :channel="channel">
        <LogsUnit
            v-for="(cycleRoomLog, id) in roomLogs.slice().reverse()"
            :id="id"
            class="unit"
            :cycleRoomLog="cycleRoomLog"
            :calendar="calendar"
        />
    </TabContainer>
</template>

<script lang="ts">
import { mapActions, mapGetters } from "vuex";
import { Channel } from "@/entities/Channel";
import LogsUnit from "@/components/Game/Communications/Messages/LogsUnit.vue";
import TabContainer from "@/components/Game/Communications/TabContainer.vue";
import { defineComponent } from "vue";
import { GameCalendar } from "@/entities/GameCalendar";
import { RoomLog } from "@/entities/RoomLog";
import { getImgUrl } from "@/utils/getImgUrl";

export default defineComponent ({
    name: "RoomEventsTab",
    components: {
        LogsUnit,
        TabContainer
    },
    data() {
        return {
            showComponent: true,
        };
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
        ])
    }
});
</script>

<style lang="scss" scoped>

.unit {
    padding: 1px 0 !important;
}

</style>
