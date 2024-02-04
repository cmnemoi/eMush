<template>
    <div v-if="player">
        {{ player.jsonEncode() }}
        <p>Logs:</p>
        <div class="logs-container" v-if="playerLogs">
            <div class="logs">
                <section v-for="(cycleRoomLog, id) in playerLogs.slice().reverse()" :key="id" class="unit">
                    <div class="banner cycle-banner">
                        <span>{{ $t('game.communications.day') }} {{ cycleRoomLog.day }} {{ $t('game.communications.cycle') }}  {{cycleRoomLog.cycle }}</span>
                    </div>
                    <div class="cycle-events">
                        <Log v-for="(roomLog, id) in cycleRoomLog.roomLogs" :key="id" :room-log="roomLog" />
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import Log from "@/components/Game/Communications/Messages/Log.vue";
import { ModerationViewPlayer } from "@/entities/ModerationViewPlayer";
import { defineComponent } from "vue";
import ModerationService from "@/services/moderation.service";

interface AdminViewPlayerData {
    player: ModerationViewPlayer | null,
    playerLogs: any,
    errors: any,
}

export default defineComponent({
    name: "AdminViewPlayerDetail",
    components: {
        Log,
    },
    data() : AdminViewPlayerData {
        return {
            player: null,
            playerLogs: null,
            errors: {}
        };
    },
    beforeMount() {
        ModerationService.getModerationViewPlayer(Number(this.$route.params.playerId))
            .then((response) => {
                this.player = new ModerationViewPlayer().load(response.data);
            })
            .catch((error) => {
                this.errors = error.response.data.errors;
            });
        ModerationService.getPlayerLogs(Number(this.$route.params.playerId))
            .then((response) => {
                this.playerLogs = response.data;
            })
            .catch((error) => {
                this.errors = error.response.data.errors;
            });
    },
});
</script>

<style lang="scss" scoped>

.logs-container {
    display: flex;
    position: relative;
    height: 436px;
}

.logs {
    overflow: auto;
}

.logs-container,
.logs {
    @extend %game-scrollbar;
}

</style>
