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
            <h2>{{ $t('moderation.logs') }}</h2>
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
            <span v-else>{{ $t('moderation.nothingToDisplay') }}</span>
        </div>
        <div class="flex-row">
            <label>{{ $t('moderation.startDate') }}
                <input
                    type="search"
                    v-model="startDateFilter"
                    @keyup.enter="loadPublicChannelMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.endDate') }}
                <input
                    type="search"
                    v-model="endDateFilter"
                    @keyup.enter="loadPublicChannelMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.messageAuthor :') }}
                <input
                    type="search"
                    v-model="authorFilter"
                    @change="loadPublicChannelMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.contenu du message :') }}
                <input
                    type="search"
                    v-model="messageFilter"
                    @change="loadPublicChannelMessages(player)"
                >
            </label>
        </div>
        <div class="messages-container" v-if="publicChannelMessages.length > 0">
            <h2> {{ $t('moderation.generalChannel') }}</h2>
            <section v-for="(message, id) in publicChannelMessages" :key="id" >
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
        <button class="action-button" @click="loadMushChannelMessages(player)" >{{ $t("moderation.loadMushChannel") }}</button>
        <div class="messages-container" v-if="mushChannelMessages.length > 0">
            <h2>{{ $t('moderation.mushChannel') }}</h2>
            <section v-for="(message, id) in mushChannelMessages" :key="id">
                <Message
                    :message="message"
                    :is-root="true"
                    :is-replyable="false"
                />
            </section>
        </div>
        <button class="action-button" @click="loadPrivateChannelsMessages(player)">{{ $t("moderation.loadPrivateChannels") }}</button>
        <div v-for="(channel, id) in privateChannels" :key="id" class="messages-container">
            <h2>{{ $t('moderation.privateChannel') }} {{ channel.id }} :</h2>
            <section v-for="(message, id) in channel.messages" :key="id">
                <Message
                    :message="message"
                    :is-root="true"
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
import { Message as MessageEntity } from "@/entities/Message"; 
import { Channel } from "@/entities/Channel";

interface PrivateChannel {
    id: number,
    messages: MessageEntity[],
}

interface ModerationViewPlayerData {
    authorFilter: string,
    mushChannelMessages: MessageEntity[],
    messageFilter: string,
    publicChannelMessages: MessageEntity[],
    player: ModerationViewPlayer | null,
    playerLogs: any,
    privateChannels: PrivateChannel[],
    startDateFilter: string,
    endDateFilter: string,
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
            authorFilter: "",
            startDateFilter: "",
            endDateFilter: new Date().toISOString(),
            mushChannelMessages: [],
            messageFilter: "",
            publicChannelMessages: [],
            player: null,
            playerLogs: null,
            privateChannels: [],
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
        async loadMushChannelMessages(player: ModerationViewPlayer) {
            this.mushChannelMessages = [];
            const mushChannel = await ModerationService.getPlayerDaedalusChannelByScope(player, "mush").then((channel: Channel) => {
                return channel;
            }).catch((error) => {
                this.errors = error.response.data.errors;
            });

            if (mushChannel) {
                await ModerationService.getChannelMessages(mushChannel, this.startDateFilter, this.endDateFilter)
                    .then((response) => {
                        this.mushChannelMessages = response;
                    })
                    .catch((error) => {
                        this.errors = error.response.data.errors;
                    });
            }
        },
        async loadPrivateChannelsMessages(player: ModerationViewPlayer) {
            this.privateChannels = [];
            await ModerationService.getPlayerPrivateChannels(player).then((channels: Channel[]) => {
                channels.forEach((channel) => {
                    ModerationService.getChannelMessages(channel, this.startDateFilter, this.endDateFilter)
                        .then((response) => {
                            this.privateChannels.push({ id: channel.id, messages: response });
                        })
                        .catch((error) => {
                            this.errors = error.response.data.errors;
                        });
                });
            }).catch((error) => {
                this.errors = error.response.data.errors;
            });
        },
        async loadPublicChannelMessages(player: ModerationViewPlayer) {
            this.publicChannelMessages = [];
            const publicChannel = await ModerationService.getPlayerDaedalusChannelByScope(player, "public").then((channel: Channel) => {
                return channel;
            }).catch((error) => {
                console.error(error);
            });

            if (publicChannel) {
                await ModerationService.getChannelMessages(publicChannel, this.startDateFilter, this.endDateFilter, this.messageFilter, this.authorFilter)
                    .then((response) => {
                        this.publicChannelMessages = response;
                    })
                    .catch((error) => {
                        console.error(error);
                    });
            }
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
                    this.authorFilter = this.player?.character?.name || "";
                    if (this.player?.cycleStartedAt) {
                        this.startDateFilter = this.getDateMinusOneDay(this.player.cycleStartedAt).toISOString();
                    }
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
            if (this.player) {
                await this.loadPublicChannelMessages(this.player);
            }
        },
        getDateMinusOneDay(date: Date) {
            date.setHours(date.getHours() - 24);
            return date;
        }
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
    :deep(.unit) {
        padding: 5px 0;
    }

    :deep(.banner) {
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

    :deep(.timestamp) {
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
