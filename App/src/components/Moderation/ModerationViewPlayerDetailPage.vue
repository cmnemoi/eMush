<template>
    <div v-if="player">
        <div class="flex-row">
            <Tippy tag="button"
                   class="action-button"
                   @click="quarantinePlayer(player)"
                   v-if="player.isAlive">
                {{ $t("moderation.quarantine") }}
                <template #content>
                    <h1>{{ $t("moderation.quarantine") }}</h1>
                    <p>{{ $t("moderation.quarantineDescription") }}</p>
                </template>
            </Tippy>
            <Tippy tag="button"
                   class="action-button"
                   @click="quarantineAndBanPlayer(player)"
                   v-if="player.isAlive">
                {{ $t("moderation.quarantineAndBan") }}
                <template #content>
                    <h1>{{ $t("moderation.quarantineAndBan") }}</h1>
                    <p>{{ $t("moderation.quarantineAndBanDescription") }}</p>
                </template>
            </Tippy>
            <Tippy tag="button"
                   class="action-button"
                   @click="banPlayer(player)"
                   v-if="!player.isAlive">
                {{ $t("moderation.ban") }}
                <template #content>
                    <h1>{{ $t("moderation.ban") }}</h1>
                    <p>{{ $t("moderation.banDescription") }}</p>
                </template>
            </Tippy>
        </div>
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
    <button class="action-button" @click="goBack">{{ $t("util.goBack") }}</button>
</template>

<script lang="ts">
import Log from "@/components/Game/Communications/Messages/Log.vue";
import { ModerationViewPlayer } from "@/entities/ModerationViewPlayer";
import { defineComponent } from "vue";
import ModerationService from "@/services/moderation.service";

interface ModerationViewPlayerData {
    player: ModerationViewPlayer | null,
    playerLogs: any,
    errors: any,
}

export default defineComponent({
    name: "ModerationViewPlayerDetail",
    components: {
        Log,
    },
    data() : ModerationViewPlayerData {
        return {
            player: null,
            playerLogs: null,
            errors: {}
        };
    },
    methods: {
        banPlayer(player: ModerationViewPlayer) {
            ModerationService.banUser(player.user.id)
                .then(() => {
                    this.loadData();
                })
                .catch((error) => {
                    this.errors = error.response.data.errors;
                });
        },
        quarantinePlayer(player: ModerationViewPlayer) {
            ModerationService.quarantinePlayer(Number(this.$route.params.playerId))
                .then(() => {
                    this.loadData();
                })
                .catch((error) => {
                    this.errors = error.response.data.errors;
                });
        },
        quarantineAndBanPlayer(player: ModerationViewPlayer) {
            ModerationService.quarantinePlayer(Number(this.$route.params.playerId))
                .then(() => {
                    if (!this.player) {
                        return;
                    }
                    ModerationService.banUser(this.player.user.id)
                        .then(() => {
                            this.loadData();
                        })
                        .catch((error) => {
                            this.errors = error.response.data.errors;
                        });
                })
                .catch((error) => {
                    this.errors = error.response.data.errors;
                });
        },
        goBack() {
            this.$router.go(-1);
        },
        loadData() {
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
    },
    beforeMount() {
        this.loadData();
    },
});
</script>

<style lang="scss" scoped>
.logs-container {
    display: flex;
    position: relative;
    height: 436px;

    .logs {
        @extend %game-scrollbar;
        overflow: auto;
    }
}
</style>
