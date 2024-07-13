<template>
    <div v-if="player">
        <ModerationActionPopup
            :moderation-dialog-visible="moderationDialogVisible"
            :action="currentAction"
            @close="closeModerationDialog"
            @submit-sanction="applySanction" />

        <SanctionDetailPage
            :is-open="showDetailPopup"
            :moderation-sanction="selectedSanction"
            @close="showDetailPopup = false"
        />

        <h2 class="sanction_heading">{{ $t('moderation.reportToAddress') }}</h2>
        <Datatable
            :headers='reportFields'
            :row-data="playerReports"
            :pagination="reportPagination"
            @pagination-click="paginationClick"
        >
            <template #header-evidence>
                {{ $t('moderation.sanctionDetail.evidence') }}
            </template>
            <template #row-evidence="report">
                {{ report.sanctionEvidence.message }}
                <button
                    class="action-button"
                    @click="goToSanctionEvidence(report)">
                    {{ $t('moderation.report.seeContext') }}
                </button>
            </template>
            <template #header-actions>
                Actions
            </template>
            <template #row-actions="report">
                <DropList class="align-right">
                    <button class="action-button" @click="showSanctionDetails(report)">{{ $t('moderation.sanctionDetail.name') }}</button>
                    <button class="action-button" @click="archiveReport(report.id)">{{ $t('moderation.actions.archive') }}</button>
                    <button class="action-button" @click="closeReport(report.id)">{{ $t('moderation.actions.close') }}</button>
                </DropList>
            </template>
        </Datatable>

        <div class="flex-row">
            <Tippy
                tag="button"
                class="action-button"
                @click="openModerationDialog({ key: 'moderation.sanction.quarantine_player', value: 'quarantine_player' })"
                v-if="player.isAlive">
                {{ $t("moderation.sanction.quarantine_player") }}
                <template #content>
                    <h1>{{ $t("moderation.sanction.quarantine_player") }}</h1>
                    <p>{{ $t("moderation.sanction.quarantineDescription") }}</p>
                </template>
            </Tippy>
            <Tippy
                tag="button"
                class="action-button"
                @click="openModerationDialog({ key: 'moderation.sanction.quarantineAndBan', value: 'quarantine_ban' })"
                v-if="player.isAlive">
                {{ $t("moderation.sanction.quarantineAndBan") }}
                <template #content>
                    <h1>{{ $t("moderation.sanction.quarantineAndBan") }}</h1>
                    <p>{{ $t("moderation.sanction.quarantineAndBanDescription") }}</p>
                </template>
            </Tippy>
            <Tippy
                tag="button"
                class="action-button"
                @click="openModerationDialog({ key: 'moderation.sanction.ban_user', value: 'ban_user' })">
                {{ $t("moderation.sanction.ban_user") }}
                <template #content>
                    <h1>{{ $t("moderation.sanction.ban_user") }}</h1>
                    <p>{{ $t("moderation.sanction.banDescription") }}</p>
                </template>
            </Tippy>
            <Tippy
                tag="button"
                class="action-button"
                @click="openModerationDialog({ key: 'moderation.sanction.warning', value: 'warning' })">
                {{ $t("moderation.sanction.warning") }}
                <template #content>
                    <h1>{{ $t("moderation.sanction.warning") }}</h1>
                    <p>{{ $t("moderation.sanction.warningDescription") }}</p>
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
                    v-model="filters.logs.day"
                    @change="loadLogs(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.cycle') }} :
                <input
                    type="search"
                    v-model="filters.logs.cycle"
                    @change="loadLogs(player)"
                >
            </label>
            <Tippy tag="label">{{ $t('moderation.filters.logContent') }} :
                <input
                    type="search"
                    v-model="filters.logs.content"
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
                    v-model="filters.logs.room"
                    @change="loadLogs(player)"
                >
                <template #content>
                    <h1>{{ $t("moderation.filters.room") }}</h1>
                    <p>{{ $t("moderation.filters.roomDescription") }}</p>
                </template>
            </Tippy>
        </div>
        <div class="logs-container" ref="logsContainer">
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
                    v-model="filters.generalChannel.startDate"
                    @change="loadPublicChannelMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.endDate') }} :
                <input
                    type="search"
                    v-model="filters.generalChannel.endDate"
                    @change="loadPublicChannelMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.messageAuthor') }} :
                <input
                    type="search"
                    v-model="filters.generalChannel.author"
                    @change="loadPublicChannelMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.messageContent') }} :
                <input
                    type="search"
                    v-model="filters.generalChannel.messageContent"
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
                    <button
                        v-if="message.hasChildrenToDisplay()"
                        class="toggle-children"
                        @click="message.toggleChildren()"
                    >
                        {{ ($t(message.isFirstChildHidden() ? 'game.communications.showMessageChildren' : 'game.communications.hideMessageChildren', { count: message.getHiddenChildrenCount() })) }}
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
                    v-model="filters.mushChannel.startDate"
                    @change="loadMushChannelMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.endDate') }} :
                <input
                    type="search"
                    v-model="filters.mushChannel.endDate"
                    @change="loadMushChannelMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.messageAuthor') }} :
                <input
                    type="search"
                    v-model="filters.mushChannel.author"
                    @change="loadMushChannelMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.messageContent') }} :
                <input
                    type="search"
                    v-model="filters.mushChannel.messageContent"
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
                    v-model="filters.privateChannel.startDate"
                    @change="loadPrivateChannelsMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.endDate') }} :
                <input
                    type="search"
                    v-model="filters.privateChannel.endDate"
                    @change="loadPrivateChannelsMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.messageAuthor') }} :
                <input
                    type="search"
                    v-model="filters.privateChannel.author"
                    @change="loadPrivateChannelsMessages(player)"
                >
            </label>
            <label>{{ $t('moderation.filters.messageContent') }} :
                <input
                    type="search"
                    v-model="filters.privateChannel.messageContent"
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
import Datatable from "@/components/Utils/Datatable/Datatable.vue";
import qs from "qs";
import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import SanctionDetailPage from "@/components/Moderation/SanctionDetailPage.vue";
import DropList from "@/components/Utils/DropList.vue";
import { ModerationSanction } from "@/entities/ModerationSanction";
import { useRouter } from "vue-router";
import { ClosedPlayer } from "@/entities/ClosedPlayer";
import router from "@/router";

interface PrivateChannel {
    id: number,
    messages: MessageEntity[],
}

interface ModerationViewPlayerData {
    filters: {
        logs: {
            content: string,
            day: integer | null,
            cycle: integer | null,
            room: string,
        },
        generalChannel: {
            author: string,
            messageContent: string,
            startDate: string,
            endDate: string,
        },
        mushChannel: {
            author: string,
            messageContent: string,
            startDate: string,
            endDate: string,
        },
        privateChannel: {
            author: string,
            messageContent: string,
            startDate: string,
            endDate: string,
        }
    },
    mushChannelMessages: MessageEntity[],
    publicChannelMessages: MessageEntity[],
    player: ModerationViewPlayer | null,
    playerLogs: any,
    privateChannels: PrivateChannel[],
    errors: any,
    moderationDialogVisible: boolean,
    currentAction: { key: string, value: string },
    showDetailPopup: boolean,
    selectedSanction: any
}

export default defineComponent({
    name: "ModerationViewPlayerDetail",
    components: {
        DropList,
        SanctionDetailPage,
        Datatable,
        Log,
        Message,
        ModerationActionPopup
    },
    data() : ModerationViewPlayerData {
        return {
            filters: {
                logs: {
                    content: "",
                    day: null,
                    cycle: null,
                    room: ""
                },
                generalChannel: {
                    author: "",
                    messageContent: "",
                    startDate: "",
                    endDate: new Date().toISOString()
                },
                mushChannel: {
                    author: "",
                    messageContent: "",
                    startDate: "",
                    endDate: new Date().toISOString()
                },
                privateChannel: {
                    author: "",
                    messageContent: "",
                    startDate: "",
                    endDate: new Date().toISOString()
                }
            },
            mushChannelMessages: [],
            publicChannelMessages: [],
            player: null,
            playerLogs: null,
            privateChannels: [],
            errors: {},
            moderationDialogVisible: false,
            currentAction: { key: "", value: "" },
            playerReports: [],
            reportPagination: {
                currentPage: 1,
                pageSize: 5,
                totalItem: 1,
                totalPage: 1
            },
            reportFields: [
                {
                    key: 'reason',
                    name: 'moderation.sanctionReason'
                },
                {
                    key: 'message',
                    name: 'moderation.report.playerMessage'
                },
                {
                    key: 'evidence',
                    name: 'moderation.sanctionDetail.evidence',
                    slot: true
                },
                {
                    key: 'actions',
                    name: 'Actions',
                    sortable: false,
                    slot: true
                }
            ],
            showDetailPopup: false,
            selectedSanction: {}
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
        showSanctionDetails(sanction: any) {
            this.selectedSanction = sanction;
            this.showDetailPopup = true;
        },
        applySanction(params: URLSearchParams) {
            if (this.player === null) {
                return;
            }

            ModerationService.applySanctionToPlayer(this.player, this.currentAction.value, params)
                .then(() => {
                    this.loadData();
                })
                .catch((error) => {
                    console.error(error);
                });

            this.moderationDialogVisible = false;
        },
        archiveReport(sanctionId) {
            const params = new URLSearchParams();
            params.append('isAbusive', false);

            ModerationService.archiveReport(sanctionId, params)
                .catch((error) => {
                    console.error(error);
                });
            this.$emit('close');
            this.loadPlayerReports();
        },
        closeReport(sanctionId) {
            const params = new URLSearchParams();
            params.append('isAbusive', true);

            ModerationService.archiveReport(sanctionId, params)
                .catch((error) => {
                    console.error(error);
                });
            this.$emit('close');
            this.loadPlayerReports();
        },
        async loadLogs(player: ModerationViewPlayer) {
            if (this.filters.logs.day === null) {
                this.filters.logs.day = player.daedalusDay;
            }
            if (this.filters.logs.cycle === null) {
                this.filters.logs.cycle = player.daedalusCycle;
            }
            await ModerationService.getPlayerLogs(player.id, this.filters.logs.day, this.filters.logs.cycle, this.filters.logs.content, this.filters.logs.room)
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
                await ModerationService.getChannelMessages(mushChannel, this.filters.mushChannel)
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
                    ModerationService.getChannelMessages(channel, this.filters.privateChannel)
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
                await ModerationService.getChannelMessages(publicChannel, this.filters.generalChannel)
                    .then((response) => {
                        this.publicChannelMessages = response;
                    })
                    .catch((error) => {
                        console.error(error);
                    });
            }
        },
        async loadPlayerReports() {
            this.loading = true;
            const params: any = {
                header: {
                    'accept': 'application/ld+json'
                },
                params: {},
                paramsSerializer: qs.stringify
            };
            params.params['moderationAction'] = 'report';
            params.params['user.userId'] = this.player.user.userId;

            if (this.reportPagination.currentPage) {
                params.params['page'] = this.reportPagination.currentPage;
            }
            if (this.reportPagination.pageSize) {
                params.params['itemsPerPage'] = this.reportPagination.pageSize;
            }

            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL, 'moderation_sanctions'), params)
                .then((result) => {
                    return result.data;
                })
                .then((remoteRowData: any) => {
                    this.playerReports = remoteRowData['hydra:member'].map((reportData: object) => {
                        return (new ModerationSanction()).load(reportData);
                    });
                    this.reportPagination.totalItem = remoteRowData['hydra:totalItems'];
                    this.reportPagination.totalPage = this.reportPagination.totalItem / this.reportPagination.pageSize;
                    this.loading = false;
                });
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
                await this.loadPlayerReports();
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
        paginationClick(page: number) {
            this.pagination.currentPage = page;
            this.loadData();
        },
        getClosedDaedalusId(closedPlayerId: number): Promise<number>
        {
            const closedPlayer = new ClosedPlayer();
            try {
                const result = ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL, 'closed_players', String(closedPlayerId)));
                closedPlayer.load(result.data);
                return closedPlayer.closedDaedalusId;
            } catch (error) {
                throw error;
            }
        },
        goToSanctionEvidence(sanction: any)
        {
            const sanctionEvidence = sanction.sanctionEvidence;
            const evidenceClass = sanctionEvidence.className;

            if (
                evidenceClass === 'message'
                || evidenceClass === 'roomLog'
            ) {
                const startDate = new Date(sanctionEvidence.date.getTime() - 30*60000).toISOString();
                const endDate = new Date(sanctionEvidence.date.getTime() + 30*60000).toISOString();

                this.filters.logs.cycle = sanctionEvidence.cycle;
                this.filters.logs.day = sanctionEvidence.day;
                this.filters.privateChannel.startDate = startDate;
                this.filters.privateChannel.endDate = endDate;
                this.filters.mushChannel.startDate = startDate;
                this.filters.mushChannel.endDate = endDate;
                this.filters.generalChannel.startDate = startDate;
                this.filters.generalChannel.endDate = endDate;

                this.loadLogs(this.player);
                this.loadPublicChannelMessages(this.player);
                this.loadMushChannelMessages(this.player);
                this.loadPrivateChannelsMessages(this.player);

                this.$nextTick(() => {
                    const logsContainer = this.$refs.logsContainer as HTMLElement;
                    if (logsContainer) {
                        logsContainer.scrollIntoView({ behavior: 'smooth' });
                    }
                });

            } else if (evidenceClass === 'closedPlayer') {
                const closedDaedalusId = this.getClosedDaedalusId(sanctionEvidence.id);
                router.push({ name: 'TheEnd', params: { closedDaedalusId } });
            }
        }
    },
    beforeMount() {
        this.loadData();
    }
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
