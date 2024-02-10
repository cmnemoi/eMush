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
            <button class="action-button router-button">
                <router-link :to="{ name: 'ModerationViewPlayerUserPage', params: {'userId': player.user.userId} }">{{ $t("moderation.goToUserProfile") }}</router-link>
            </button>
        </div>
        {{ player.jsonEncode() }}
        <div class="logs-container">
            <h2>Logs:</h2>
            <div class="logs" v-if="playerLogs">
                <section v-for="(cycleRoomLog, id) in playerLogs.slice().reverse()" :key="id" class="unit">
                    <div class="banner cycle-banner">
                        <span>{{ $t('game.communications.day') }} {{ cycleRoomLog.day }} {{ $t('game.communications.cycle') }}  {{cycleRoomLog.cycle }}</span>
                    </div>
                    <div class="cycle-events">
                        <Log v-for="(roomLog, id) in cycleRoomLog.roomLogs" :key="id" :room-log="roomLog" />
                    </div>
                </section>
            </div>
            <span v-else>No logs to display.</span>
        </div>
        <div class="messages-container">
            <h2>Messages:</h2>
            <section v-for="(message, id) in playerPublicMessages" :key="id" class="unit">
                <Message
                    :message="message"
                    :is-root="true"
                    :is-replyable="false"
                />
                <button class="toggle-children" @click="message.toggleChildren()">
                    {{ message.hasChildrenToDisplay() ? ($t(message.isFirstChildHidden() ? 'game.communications.showMessageChildren' : 'game.communications.hideMessageChildren', { count: message.getHiddenChildrenCount() })) : '' }}
                </button>
                <Message
                    v-for="(child, id) in message.children"
                    :key="id"
                    :message="child"
                    :is-replyable="false"
                />
            </section>
        </div>
    </div>
    <button class="action-button" @click="goBack">{{ $t("util.goBack") }}</button>
</template>

<script lang="ts">
import Log from "@/components/Game/Communications/Messages/Log.vue";
import Message from "@/components/Game/Communications/Messages/Message.vue";
import { ModerationViewPlayer } from "@/entities/ModerationViewPlayer";
import { defineComponent } from "vue";
import ModerationService from "@/services/moderation.service";

interface ModerationViewPlayerData {
    player: ModerationViewPlayer | null,
    playerLogs: any,
    playerPublicMessages: any,
    errors: any,
}

export default defineComponent({
    name: "ModerationViewPlayerDetail",
    components: {
        Log,
        Message,
    },
    data() : ModerationViewPlayerData {
        return {
            player: null,
            playerLogs: null,
            playerPublicMessages: null,
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
            ModerationService.quarantinePlayer(player.id)
                .then(() => {
                    this.loadData();
                })
                .catch((error) => {
                    this.errors = error.response.data.errors;
                });
        },
        quarantineAndBanPlayer(player: ModerationViewPlayer) {
            ModerationService.quarantinePlayer(player.id)
                .then(() => {
                    ModerationService.banUser(player.user.id)
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
        async loadData() {
            await ModerationService.getModerationViewPlayer(Number(this.$route.params.playerId))
                .then((response) => {
                    this.player = new ModerationViewPlayer().load(response.data);
                })
                .catch((error) => {
                    this.errors = error.response.data.errors;
                });
            await ModerationService.getPlayerLogs(Number(this.$route.params.playerId))
                .then((response) => {
                    this.playerLogs = response.data;
                })
                .catch((error) => {
                    this.errors = error.response.data.errors;
                });
            await ModerationService.getPlayerMessages(Number(this.$route.params.playerId), 'public')
                .then((response) => {
                    this.playerPublicMessages = response.data;
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
.logs-container, .messages-container {
    position: relative;
    // min-height: 436px;
    height: 436px;
    overflow: auto;
    resize: vertical;
    margin: 1em 0;
    padding: 1.2em;
    background: rgba(194, 243, 252, 1);
    color: $deepBlue;

    @extend %game-scrollbar;

    h2 { margin-top: 0; }


    /* Duplicated styles from TabContainer component */
    ::v-deep(.unit) {
        padding: 5px 0;
    }

    ::v-deep(.banner) {
        flex-direction: row;
        align-items: center;
        justify-content: center;
        min-height: 24px;
        border-radius: 3px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        background: $lightCyan;

        span {
            flex: 1;
            text-align: center;
            font-size: .92em;
        }

        .expand {
            align-self: center;
            padding: 2px;
        }

        img { vertical-align: middle; }
    }

    ::v-deep(.timestamp) {
        text-align: end;
        padding-top: 0.2em;
        font-size: 0.85em;
        letter-spacing: 0.03em;
        font-style: italic;
        font-variant: initial;
        opacity: 0.65;
        float: right;
    }
}

.router-button a {
    text-decoration: none;
    color: white;
}
</style>
