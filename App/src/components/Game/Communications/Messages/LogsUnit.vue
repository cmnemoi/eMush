<template>
    <section>
        <button class="banner cycle-banner" @click="isOpen = !isOpen">
            <img :alt="isOpen ? `retract` : `expand`" :src="isOpen ? getImgUrl('comms/less.png') : getImgUrl('comms/more.png') ">
            <span>{{ calendar?.dayName }} {{ cycleRoomLog?.day }} {{ calendar?.cycleName }} {{ cycleRoomLog?.cycle }}</span>
        </button>
        <div class="cycle-events" v-if="isOpen">
            <Log v-for="(roomLog, id) in cycleRoomLog?.roomLogs" :key="id" :room-log="roomLog" />
        </div>
    </section>
</template>

<script lang="ts">
import { mapActions, mapGetters } from "vuex";
import Log from "@/components/Game/Communications/Messages/Log.vue";
import { defineComponent } from "vue";
import { GameCalendar } from "@/entities/GameCalendar";
import { getImgUrl } from "@/utils/getImgUrl";

export default defineComponent ({
    name: "LogsUnit",
    components: {
        Log    
    },
    data() {
        return {
            isOpen: true,
        };
    },
    props: {
        cycleRoomLog: Object,
        calendar: GameCalendar,
        retracted: Boolean,
    },
    computed: {
        ...mapGetters('communication', [
            'messages'
        ])
    },
    methods: {
        ...mapActions('communication', [
            'loadMessages'
        ]),
        getImgUrl
    },
    mounted() {
        this.isOpen = !this.retracted;
    }
});
</script>

<style lang="scss" scoped>

.cycle-events {
    &::v-deep(a) {
        color: $green;
    }
}

</style>
