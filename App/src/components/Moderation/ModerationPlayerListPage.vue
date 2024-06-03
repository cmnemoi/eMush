<template>
    <div class="player_list_container">
        <div class="player_filter_options">
            <label>{{ $t('moderation.alivePlayersFilter') }}
                <select v-model="playerStatusFilter" @change="updateFilter">
                    <option
                        v-for="option in playerStatusFilterOption"
                        :value=option.value
                        :key=option.key
                    >
                        {{ $t(option.key) }}
                    </option>
                </select>
            </label>
            <label>{{ $t('moderation.mushPlayersFilter') }}
                <input
                    type="checkbox"
                    class=""
                    placeholder=""
                    aria-controls="example"
                    v-model="mushPlayersFilter"
                    @change="updateFilter"
                >
            </label>
            <label>{{ $t('moderation.searchByUsername')  }}
                <input
                    v-model="usernameFilter"
                    type="search"
                    class=""
                    placeholder=""
                    aria-controls="example"
                    @change="updateFilter"
                >
            </label>
            <label>{{ $t('moderation.searchByCharacter')  }}
                <input
                    v-model="characterFilter"
                    type="search"
                    class=""
                    placeholder=""
                    aria-controls="example"
                    @change="updateFilter"
                >
            </label>
            <label>{{ $t('moderation.searchByDaedalusId') }}
                <input
                    v-model="daedalusIdFilter"
                    type="search"
                    class=""
                    placeholder=""
                    aria-controls="example"
                    @change="updateFilter"
                >
            </label>
            <button @click="closeAllPlayers" v-if="isAdmin">{{ $t("admin.playerList.closeAllPlayers") }}</button>
        </div>
        <Datatable
            :headers='fields'
            :uri="uri"
            :loading="loading"
            :row-data="rowData"
            :pagination="pagination"
            :character-filter="characterFilter"
            :daedalus-id-filter="daedalusIdFilter"
            :username-filter="usernameFilter"
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
</template>

<script lang="ts">
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

export default defineComponent({
    name: "ModerationPlayerListPage",
    components: {
        Datatable,
        DropList
    },
    computed: {
        ...mapGetters({
            isAdmin: 'auth/isAdmin'
        })
    },
    data() {
        return {
            playerStatusFilterOption: [
                { key: 'moderation.playerList.gameStatuses.in_game', value: 'in_game' },
                { key: 'moderation.playerList.gameStatuses.finished', value: 'finished' },
                { key: 'moderation.playerList.gameStatuses.closed', value: 'closed' },
                { key: 'moderation.playerList.gameStatuses.all', value: '' }
            ],
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
                    key: 'daedalusId',
                    name: 'moderation.playerList.daedalusId'
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
                pageSize: 10,
                totalItem: 1,
                totalPage: 1
            },
            rowData: [],
            sortField: '',
            sortDirection: 'DESC',
            loading: false,
            characterFilter: '',
            daedalusIdFilter: '',
            mushPlayersFilter: false,
            usernameFilter: '',
            playerStatusFilter: 'in_game',
            pageSizeOptions: [
                { text: 5, value: 5 },
                { text: 10, value: 10 },
                { text: 20, value: 20 }
            ]
        };
    },
    methods: {
        formatDate: (date: string): string => {
            const dateObject = new Date(date);
            return format(dateObject, 'PPPPpp', { locale: fr });
        },
        loadData() {
            this.loading = true;
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
            if (this.sortField) {
                qs.stringify(params.params['order'] = { [this.sortField]: this.sortDirection });
            }
            if (this.characterFilter) {
                params.params['characterConfig.characterName'] = this.characterFilter;
            }
            if (this.playerStatusFilter) {
                params.params['closedPlayer.playerInfo.gameStatus'] = this.playerStatusFilter;
            }
            if (this.daedalusIdFilter) {
                if (['in_game'].includes(params.params['closedPlayer.playerInfo.gameStatus'])) {
                    params.params['player.daedalus.id'] = this.daedalusIdFilter;
                } else {
                    params.params['closedPlayer.closedDaedalus.id'] = this.daedalusIdFilter;
                }
            }
            if (this.mushPlayersFilter) {
                params.params['closedPlayer.isMush'] = this.mushPlayersFilter;
            }
            if (this.usernameFilter) {
                params.params['user.username'] = this.usernameFilter;
            }

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
                    this.loading = false;
                });
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
        }
    },
    beforeMount() {
        this.loadData();
    }
});
</script>

<style lang="scss" scoped>

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
    padding: 2px 15px 4px;
}

</style>
