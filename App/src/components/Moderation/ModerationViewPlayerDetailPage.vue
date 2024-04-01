<template>
    <div v-if="player">
        <ModerationActionPopup :moderationDialogVisible="moderationDialogVisible" :action="currentAction" @close="closeModerationDialog" @submitSanction="applySanction" />
        <div class="flex-row">
            <Tippy tag="button"
                   class="action-button"
                   @click="openModerationDialog({ key: 'moderation.sanction.quarantine_player', value: 'quarantine_player' })"
                   v-if="player.isAlive">
                {{ $t("moderation.sanction.quarantine_player") }}
                <template #content>
                    <h1>{{ $t("moderation.sanction.quarantine_player") }}</h1>
                    <p>{{ $t("moderation.sanction.quarantineDescription") }}</p>
                </template>
            </Tippy>
            <Tippy tag="button"
                   class="action-button"
                   @click="openModerationDialog({ key: 'moderation.sanction.quarantineAndBan', value: 'quarantine_ban' })"
                   v-if="player.isAlive">
                {{ $t("moderation.sanction.quarantineAndBan") }}
                <template #content>
                    <h1>{{ $t("moderation.sanction.quarantineAndBan") }}</h1>
                    <p>{{ $t("moderation.sanction.quarantineAndBanDescription") }}</p>
                </template>
            </Tippy>
            <Tippy tag="button"
                   class="action-button"
                   @click="openModerationDialog({ key: 'moderation.sanction.ban_user', value: 'ban_user' })"
                   v-if="!player.isAlive">
                {{ $t("moderation.sanction.ban") }}
                <template #content>
                    <h1>{{ $t("moderation.sanction.ban") }}</h1>
                    <p>{{ $t("moderation.sanction.banDescription") }}</p>
                </template>
            </Tippy>
            <button class="action-button router-button">
                <router-link :to="{ name: 'ModerationViewPlayerUserPage', params: {'userId': player.user.userId} }">{{ $t("moderation.goToUserProfile") }}</router-link>
            </button>
            <Tippy tag="button" class="action-button" @click="loadData()">
                {{ $t('moderation.reloadData') }}

                <template #content>
                    <h1>{{ $t("moderation.reloadData") }}</h1>
                    <p>{{ $t("moderation.reloadDataDescription") }}</p>
                </template>
            </Tippy>
        </div>
        <span>{{ player.character.name }} - {{ $t('moderation.player.playedBy') }} {{ player.user.username }}  - {{ player.isMush ? $t('moderation.player.mush') : $t('moderation.player.human') }} - {{ player.isAlive ? $t('moderation.player.alive') : $t('moderation.player.dead') }} - {{ player.user.isBanned ? $t('moderation.player.banned') : $t('moderation.player.notBanned') }}</span>
        <div class="flex-row">
            <label>{{ $t('moderation.filters.day') }} :
                <input
                    type="search"
                    v-model="logsDay"
                    @change="loadLogs(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.cycle') }} :
                <input
                    type="search"
                    v-model="logsCycle"
                    @change="loadLogs(player)"
                >
            </label>
            <Tippy tag="label">{{ $t('moderation.filters.logContent') }} :
                <input
                    type="search"
                    v-model="logsContent"
                    @change="loadLogs(player)"
                >
                <template #content>
                    <h1>{{ $t("moderation.filters.logContent") }}</h1>
                    <p>{{ $t("moderation.filters.logContentDescription") }}</p>
                </template>
            </Tippy>
            <Tippy tag="label">{{ $t('moderation.filters.room') }} :
                <input
                    type="search"
                    v-model="logsRoom"
                    @change="loadLogs(player)"
                >
                <template #content>
                    <h1>{{ $t("moderation.filters.room") }}</h1>
                    <p>{{ $t("moderation.filters.roomDescription") }}</p>
                </template>
            </Tippy>
        </div>
        <div class="logs-container">
            <h2>{{ $t('moderation.logs') }}</h2>
            <div class="logs" v-if="playerLogs">
                <section v-for="(cycleRoomLog, id) in playerLogs.slice().reverse()" :key="id">
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
            <label>{{ $t('moderation.filters.startDate') }} :
                <input
                    type="search"
                    v-model="generalChannelStartDateFilter"
                    @change="loadPublicChannelMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.endDate') }} :
                <input
                    type="search"
                    v-model="generalChannelEndDateFilter"
                    @change="loadPublicChannelMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.messageAuthor') }} :
                <input
                    type="search"
                    v-model="generalChannelAuthorFilter"
                    @change="loadPublicChannelMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.messageContent') }} :
                <input
                    type="search"
                    v-model="generalChannelMessageFilter"
                    @change="loadPublicChannelMessages(player)"
                >
            </label>
        </div>
        <div class="messages-container">
            <h2> {{ $t('moderation.generalChannel') }}</h2>
            <div v-if="publicChannelMessages.length > 0">
                <section v-for="(message, id) in publicChannelMessages" :key="id" >
                    <Message
                        :message="message"
                        :is-root="true"
                        :is-replyable="false"
                        :admin-mode = "true"
                    />
                    <button class="toggle-children" @click="message.toggleChildren()">
                        {{ message.hasChildrenToDisplay() ? ($t(message.isFirstChildHidden() ? 'game.communications.showMessageChildren' : 'game.communications.hideMessageChildren', { count: message.getHiddenChildrenCount() })) : '' }}
                    </button>
                    <Message
                        v-for="(child, id) in message.children"
                        :key="id"
                        :message="child"
                        :is-replyable="false"
                        :admin-mode = "true"
                    />
                </section>
            </div>
            <span v-else>{{ $t('moderation.nothingToDisplay') }}</span>
        </div>
        <div class="flex-row">
            <label>{{ $t('moderation.filters.startDate') }} :
                <input
                    type="search"
                    v-model="mushChannelStartDateFilter"
                    @change="loadMushChannelMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.endDate') }} :
                <input
                    type="search"
                    v-model="mushChannelEndDateFilter"
                    @change="loadMushChannelMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.messageAuthor') }} :
                <input
                    type="search"
                    v-model="mushChannelAuthorFilter"
                    @change="loadMushChannelMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.messageContent') }} :
                <input
                    type="search"
                    v-model="mushChannelMessageFilter"
                    @change="loadMushChannelMessages(player)"
                >
            </label>
        </div>
        <div class="messages-container">
            <h2>{{ $t('moderation.mushChannel') }}</h2>
            <div v-if="mushChannelMessages.length > 0">
                <section v-for="(message, id) in mushChannelMessages" :key="id">
                    <Message
                        :message="message"
                        :is-root="true"
                        :is-replyable="false"
                        :admin-mode = "true"
                    />
                </section>
            </div>
            <span v-else>{{ $t('moderation.nothingToDisplay') }}</span>
        </div>
        <div class="flex-row" v-if="privateChannels.length > 0">
            <label>{{ $t('moderation.filters.startDate') }} :
                <input
                    type="search"
                    v-model="privateChannelStartDateFilter"
                    @change="loadPrivateChannelsMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.endDate') }} :
                <input
                    type="search"
                    v-model="privateChannelEndDateFilter"
                    @change="loadPrivateChannelsMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.messageAuthor') }} :
                <input
                    type="search"
                    v-model="privateChannelAuthorFilter"
                    @change="loadPrivateChannelsMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.messageContent') }} :
                <input
                    type="search"
                    v-model="privateChannelMessageFilter"
                    @change="loadPrivateChannelsMessages(player)"
                >
            </label>
        </div>
        <div v-for="(channel, id) in privateChannels" :key="id" class="messages-container">
            <h2>{{ $t('moderation.privateChannel') }} {{ channel.id }} :</h2>
            <div v-if="channel.messages.length > 0">
                <section v-for="(message, id) in channel.messages" :key="id">
                    <Message
                        :message="message"
                        :is-root="true"
                        :is-replyable="false"
                        :admin-mode = "true"
                    />
                </section>
            </div>
            <span v-else>{{ $t('moderation.nothingToDisplay') }}</span>
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
import ModerationActionPopup from "@/components/Moderation/ModerationActionPopup.vue";

interface PrivateChannel {
    id: number,
    messages: MessageEntity[],
}

interface ModerationViewPlayerData {
    logsContent: string,
    logsDay: integer | null,
    logsCycle: integer | null,
    logsRoom: string,
    generalChannelAuthorFilter: string,
    generalChannelMessageFilter: string,
    generalChannelStartDateFilter: string,
    generalChannelEndDateFilter: string,
    mushChannelAuthorFilter: string,
    mushChannelMessageFilter: string,
    mushChannelStartDateFilter: string,
    mushChannelEndDateFilter: string,
    privateChannelAuthorFilter: string,
    privateChannelMessageFilter: string,
    privateChannelStartDateFilter: string,
    privateChannelEndDateFilter: string,
    mushChannelMessages: MessageEntity[],
    publicChannelMessages: MessageEntity[],
    player: ModerationViewPlayer | null,
    playerLogs: any,
    privateChannels: PrivateChannel[],
    errors: any,
    moderationDialogVisible: boolean,
    currentAction: { key: string, value: string }
}

export default defineComponent({
    name: "ModerationViewPlayerDetail",
    components: {
        Log,
        Message,
        ModerationActionPopup,
    },
    data() : ModerationViewPlayerData {
        return {
            logsContent: "",
            logsDay: null,
            logsCycle: null,
            logsRoom: "",
            generalChannelAuthorFilter: "",
            generalChannelMessageFilter: "",
            generalChannelStartDateFilter: "",
            generalChannelEndDateFilter: new Date().toISOString(),
            mushChannelAuthorFilter: "",
            mushChannelMessageFilter: "",
            mushChannelStartDateFilter: "",
            mushChannelEndDateFilter: new Date().toISOString(),
            privateChannelAuthorFilter: "",
            privateChannelMessageFilter: "",
            privateChannelStartDateFilter: "",
            privateChannelEndDateFilter: new Date().toISOString(),
            mushChannelMessages: [],
            publicChannelMessages: [],
            player: null,
            playerLogs: null,
            privateChannels: [],
            errors: {},
            moderationDialogVisible: false,
            currentAction: { key: "", value: "" },
        };
    },
    methods: {
        openModerationDialog(moderationAction: { key: string, value: string }) {
            this.currentAction = moderationAction;
            this.moderationDialogVisible = true;
        },
        closeModerationDialog() {
            this.moderationDialogVisible = false;
        },
        applySanction(params: URLSearchParams) {
            if (this.player === null) {
                return;
            }

            if (this.currentAction.value === 'quarantine_player' || this.currentAction.value === 'quarantine_ban') {
                ModerationService.quarantinePlayer(this.player.id, params)
                    .then(() => {
                        this.loadData();
                    })
                    .catch((error) => {
                        console.error(error);
                    });
            }
            if (this.currentAction.value === 'ban_user' || this.currentAction.value === 'quarantine_ban') {
                ModerationService.banUser(this.player.user.id, params)
                    .then(() => {
                        this.loadData();
                    })
                    .catch((error) => {
                        console.error(error);
                    });
            }
            this.moderationDialogVisible = false;
        },
        async loadLogs(player: ModerationViewPlayer) {
            if (this.logsDay === null) {
                this.logsDay = player.daedalusDay;
            }
            if (this.logsCycle === null) {
                this.logsCycle = player.daedalusCycle;
            }
            await ModerationService.getPlayerLogs(player.id, this.logsDay, this.logsCycle, this.logsContent, this.logsRoom)
                .then((response) => {
                    this.playerLogs = response.data;
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        async loadMushChannelMessages(player: ModerationViewPlayer) {
            this.mushChannelMessages = [];
            const mushChannel = await ModerationService.getPlayerDaedalusChannelByScope(player, "mush").then((channel: Channel) => {
                return channel;
            }).catch((error) => {
                console.error(error);
            });

            if (mushChannel) {
                await ModerationService.getChannelMessages(mushChannel, this.mushChannelStartDateFilter, this.mushChannelEndDateFilter, this.mushChannelMessageFilter, this.mushChannelAuthorFilter)
                    .then((response) => {
                        this.mushChannelMessages = response;
                    })
                    .catch((error) => {
                        console.error(error);
                    });
            }
        },
        async loadPrivateChannelsMessages(player: ModerationViewPlayer) {
            this.privateChannels = [];
            await ModerationService.getPlayerPrivateChannels(player).then((channels: Channel[]) => {
                channels.forEach((channel) => {
                    ModerationService.getChannelMessages(channel, this.privateChannelStartDateFilter, this.privateChannelEndDateFilter, this.privateChannelMessageFilter, this.privateChannelAuthorFilter)
                        .then((response) => {
                            this.privateChannels.push({ id: channel.id, messages: response });
                        })
                        .catch((error) => {
                            console.error(error);
                        });
                });
            }).catch((error) => {
                console.error(error);
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
                await ModerationService.getChannelMessages(publicChannel, this.generalChannelStartDateFilter, this.generalChannelEndDateFilter, this.generalChannelMessageFilter, this.generalChannelAuthorFilter)
                    .then((response) => {
                        this.publicChannelMessages = response;
                    })
                    .catch((error) => {
                        console.error(error);
                    });
            }
        },
        goBack() {
            this.$router.go(-1);
        },
        async loadData() {
            await ModerationService.getModerationViewPlayer(Number(this.$route.params.playerId))
                .then((response) => {
                    this.player = new ModerationViewPlayer().load(response.data);
                    this.setupAuthorFilters();
                    this.setupStartDateFilters();
                })
                .catch((error) => {
                    console.error(error);
                });
            if (this.player) {
                await this.loadLogs(this.player);
                await this.loadPublicChannelMessages(this.player);
                await this.loadMushChannelMessages(this.player);
                await this.loadPrivateChannelsMessages(this.player);
            }
        },
        getDateMinusOneDay(date: Date) {
            const newDate = new Date(date);
            newDate.setDate(newDate.getDate() - 1);
            return newDate;
        },
        setupAuthorFilters() {
            this.generalChannelAuthorFilter = this.player?.character?.key || "";
            this.mushChannelAuthorFilter = this.player?.character?.key || "";
            this.privateChannelAuthorFilter = this.player?.character?.key || "";
        },
        setupStartDateFilters() {
            if (this.player?.cycleStartedAt) {
                this.mushChannelStartDateFilter = this.getDateMinusOneDay(this.player.cycleStartedAt).toISOString();
                this.generalChannelStartDateFilter = this.getDateMinusOneDay(this.player.cycleStartedAt).toISOString();
                this.privateChannelStartDateFilter = this.getDateMinusOneDay(this.player.cycleStartedAt).toISOString();
            }
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
