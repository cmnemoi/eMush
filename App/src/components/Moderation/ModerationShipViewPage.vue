<template>
    <div class="player_list_container">
        <Datatable
            :headers='fields'
            :uri="uri"
            :loading="loading"
            :row-data="rowData"
            :pagination="pagination"
            @pagination-click="paginationClick"
            @sort-table="sortTable"
        >
            <template #header-actions>
                Actions
            </template>
            <template #row-actions="player">
                <DropList class="align-right">
                    <router-link :to="{ name: 'ModerationViewPlayerDetail', params: {'playerId': player.id} }">{{ $t("moderation.goToPlayerDetails") }}</router-link>
                    <router-link :to="{ name: 'ModerationViewPlayerUserPage', params: {'userId': player.user.userId} }">{{ $t("moderation.goToUserProfile") }}</router-link>
                    <router-link :to="{ name: 'SanctionListPage', params: { username: player.user.username, userId : player.user.userId } }">{{ $t('moderation.sanctionList') }}</router-link>
                    <button class="action-button" @click="closePlayer(player.id)" v-if="isAdmin && player.gameStatus === $t('moderation.playerList.gameStatuses.finished')">{{ $t("admin.playerList.closePlayer") }}</button>
                </DropList>
            </template>
        </Datatable>
    </div>

    <div class="logsButtons">
        <button class="action-button" @click="seeAllTransfertLogs()"> {{ $t('moderation.seeAllTransfers') }}</button>
    </div>

    <div class="flex-row">
        <label>{{ $t('moderation.filters.day') }} :
            <input
                type="search"
                v-model="filters.logs.day"
                @change="loadLogs()"
            >
        </label>
        <label>{{ $t('moderation.filters.cycle') }} :
            <input
                type="search"
                v-model="filters.logs.cycle"
                @change="loadLogs()"
            >
        </label>
        <Tippy tag="label">{{ $t('moderation.filters.logContent') }} :
            <input
                type="search"
                v-model="filters.logs.content"
                @change="loadLogs()"
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
                @change="loadLogs()"
            >
            <template #content>
                <h1>{{ $t("moderation.filters.room") }}</h1>
                <p>{{ $t("moderation.filters.roomDescription") }}</p>
            </template>
        </Tippy>
    </div>
    <div class="logs-container" ref="logsContainer">
        <h2>{{ $t('moderation.logs') }}</h2>
        <div class="logs" v-if="logs">
            <section v-for="(cycleRoomLog, id) in logs.slice().reverse()" :key="id">
                <div class="banner cycle-banner">
                    <span>{{ $t('game.communications.day') }} {{ cycleRoomLog.day }} {{ $t('game.communications.cycle') }}  {{cycleRoomLog.cycle }}</span>
                </div>
                <div class="cycle-events">
                    <Log v-for="(roomLog, roomLogId) in cycleRoomLog.roomLogs" :key="roomLogId" :room-log="roomLog" />
                </div>
            </section>
        </div>
        <span v-else>{{ $t('moderation.nothingToDisplay') }}</span>
    </div>

    <label>
        <input
            type="checkbox"
            id="checkbox"
            v-model="ignoreNoise"
            @change="loadLogs()"/>
        {{ $t('moderation.ignoreNoise') }}
    </label>
</template>

<script lang="ts">
import Log from "@/components/Game/Communications/Messages/Log.vue";
import { defineComponent } from "vue";
import Datatable from "@/components/Utils/Datatable/Datatable.vue";
import DropList from "@/components/Utils/DropList.vue";
import qs from "qs";
import AdminService from "@/services/admin.service";
import { format } from "date-fns";
import { fr } from "date-fns/locale";
import ModerationService from "@/services/moderation.service";
import { mapGetters } from "vuex";
import { characterEnum } from "@/enums/character";

interface ModerationShipView {
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
    logs: any,
    ignoreNoise : boolean,
    fields : any,
    pagination: {
        currentPage: number,
        pageSize: number,
        totalItem: number,
        totalPage: number
    },

    rowData: any,
    sortField: string,
    sortDirection: string,
    loading: boolean,

}

export default defineComponent({
    name: "ModerationShipViewPage",
    components: {
        Datatable,
        DropList,
        Log
    },
    computed: {
        ...mapGetters({
            isAdmin: 'auth/isAdmin'
        })
    },
    data() : ModerationShipView {
        return {
            filters: {
                logs: {
                    content: "",
                    day: 1,
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
            logs: null,
            ignoreNoise: true,
            fields: [
                {
                    key: 'id',
                    name: 'moderation.playerList.id'
                },
                {
                    key: 'gameStatus',
                    name: 'moderation.playerList.gameStatus'
                },
                {
                    key: 'characterName',
                    name: 'moderation.playerList.characterName',
                    image: 'characterBody'
                },
                {
                    key: 'user',
                    subkey: 'username',
                    name: 'moderation.playerList.user'
                },
                {
                    key: 'actions',
                    name: 'Actions',
                    sortable: false,
                    slot: true
                }

            ],
            pagination: {
                currentPage: 1,
                pageSize: 16,
                totalItem: 1,
                totalPage: 1
            },
            rowData: [],
            sortField: '',
            sortDirection: 'DESC',
            loading: false
        };
    },
    methods: {
        formatDate: (date: string): string => {
            const dateObject = new Date(date);
            return format(dateObject, 'PPPPpp', { locale: fr });
        },

        loadPlayersData() {
            const params: any = {
                params: {},
                paramsSerializer: qs.stringify
            };
            if (this.pagination.currentPage) {
                params.params['page'] = this.pagination.currentPage;
            }
            if (this.pagination.pageSize) {
                params.params['itemsPerPage'] = this.pagination.pageSize;
            }

            params.params['player.daedalus.id'] = String(this.$route.params.daedalusId);

            ModerationService.getPlayerInfoList(params)
                .then((result) => {
                    for (const playerInfo of result.data['hydra:member']) {
                        playerInfo.gameStatus = this.$t('moderation.playerList.gameStatuses.' + playerInfo.gameStatus);
                        playerInfo.characterBody = this.getCharacterBodyFromKey(playerInfo.characterConfig.characterName);
                        playerInfo.characterName = this.getCharacterNameFromKey(playerInfo.characterConfig.characterName);
                    }
                    return result.data;
                })
                .then((remoteRowData: any) => {
                    this.rowData = remoteRowData['hydra:member'];
                    this.pagination.totalItem = remoteRowData['hydra:totalItems'];
                    this.pagination.totalPage = this.pagination.totalItem / this.pagination.pageSize;

                });
        },

        async loadLogs() {
            await ModerationService.getLogs(
                this.filters.logs.day,
                this.filters.logs.cycle,
                null,
                this.filters.logs.content,
                this.filters.logs.room,
                this.$route.params.daedalusId as string,
                this.ignoreNoise
            )
                .then((response) => {
                    this.logs = response.data;
                })
                .catch((error) => {
                    console.error(error);
                });
        },


        loadData() {
            this.loading = true;

            this.loadPlayersData();
            this.loadLogs();

            this.loading = false;

        },
        getCharacterNameFromKey(characterKey: string) {
            return characterEnum[characterKey].name;
        },
        getCharacterBodyFromKey(characterKey: string) {
            return characterEnum[characterKey].body;
        },
        paginationClick(page: number) {
            this.pagination.currentPage = page;
            this.loadData();
        },
        closeAllPlayers() {
            AdminService.closeAllPlayers().then(() => {
                this.loadData();
            });
        },
        closePlayer(playerId: string) {
            AdminService.closePlayer(playerId)
                .then((result) => {
                    this.loadData();
                    return result.data;
                })
                .then((remoteRowData: any) => {
                    this.rowData = remoteRowData['hydra:member'];
                    this.pagination.totalItem = remoteRowData['hydra:totalItems'];
                    this.pagination.totalPage = this.pagination.totalItem / this.pagination.pageSize;
                    this.loading = false;
                });
        },
        sortTable(selectedField: any): void {
            if (!selectedField.sortable) {
                return;
            }
            if (this.sortField === selectedField.key) {
                switch (this.sortDirection) {
                case 'DESC':
                    this.sortDirection = 'ASC';
                    break;
                case 'ASC':
                    this.sortDirection = 'DESC';
                    break;
                }
            } else {
                this.sortDirection = 'DESC';
            }
            this.sortField = selectedField.key;
            this.loadData();
        },
        updateFilter() {
            this.pagination.currentPage = 1;
            this.loadData();
        },
        seeAllTransfertLogs() {
            this.filters.logs.day = null;
            this.filters.logs.cycle = null;
            this.filters.logs.content = 'exchange_body_success';
            this.filters.logs.room = '';

            this.updateFilter();

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

.player_filter_options {
    display: flex;
    flex-grow: 1;
    flex-direction: row;
    justify-content: space-between;
    padding: 10px;

    label {
        padding: 0 10px;
    }
}

button {
    @include button-style();

    & {
        padding: 2px 15px 4px;
    }
}

</style>
