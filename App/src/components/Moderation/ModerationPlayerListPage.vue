<template>
    <div class="player_list_container">
        <div class="player_filter_options">
            <label>{{ $t('moderation.display only mush players') }}
                <input
                    type="checkbox"
                    class=""
                    placeholder=""
                    aria-controls="example"
                    v-model="mushPlayersFilter"
                    @change="updateFilter"
                >
            </label>
            <label>{{ $t('moderation.display only alive players') }}
                <input
                    type="checkbox"
                    class=""
                    placeholder=""
                    aria-controls="example"
                    v-model="alivePlayersFilter"
                    @change="updateFilter"
                >
            </label>
            <label>{{ $t('admin.search')  }} by username:
                <input
                    v-model="usernameFilter"
                    type="search"
                    class=""
                    placeholder=""
                    aria-controls="example"
                    @change="updateFilter"
                >
            </label>
            <label>{{ $t('admin.search') }} by Daedalus ID:
                <input
                    v-model="daedalusIdFilter"
                    type="search"
                    class=""
                    placeholder=""
                    aria-controls="example"
                    @change="updateFilter"
                >
            </label>
        </div>
        <Datatable
            :headers='fields'
            :uri="uri"
            :loading="loading"
            :row-data="rowData"
            :pagination="pagination"
            :daedalusIdFilter="daedalusIdFilter"
            :usernameFilter="usernameFilter"
            @paginationClick="paginationClick"
            @sortTable="sortTable"
        >
            <template #header-actions>
                Actions
            </template>
            <template #row-actions="slotProps">
                <router-link :to="{ name: 'ModerationViewPlayerDetail', params: {'playerId': slotProps.id} }">Voir les détails du joueur</router-link>
            </template>
        </Datatable>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import Datatable from "@/components/Utils/Datatable/Datatable.vue";
import qs from "qs";
import AdminService from "@/services/admin.service";
import { format } from "date-fns";
import { fr } from "date-fns/locale";
import ModerationService from "@/services/moderation.service";

export default defineComponent({
    name: "ModerationPlayerListPage",
    components: {
        Datatable
    },
    data() {
        return {
            fields: [
                {
                    key: 'id',
                    name: 'id',
                },
                {
                    key: 'gameStatus',
                    name: 'gameStatus',
                },
                {
                    key: 'daedalusId',
                    name: 'Daedalus ID',
                },
                {
                    key: 'characterConfig',
                    subkey: 'characterName',
                    name: 'Character',
                },
                {
                    key: 'user',
                    subkey: 'username',
                    name: 'Username',
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
            alivePlayersFilter: true,
            daedalusIdFilter: '',
            mushPlayersFilter: false,
            usernameFilter: '',
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
            if (this.daedalusIdFilter) {
                params.params['closedPlayer.closedDaedalus.id'] = this.daedalusIdFilter;
            }
            if (this.usernameFilter) {
                params.params['user.username'] = this.usernameFilter;
            }
            if (this.alivePlayersFilter) {
                params.params['closedPlayer.playerInfo.gameStatus'] = 'in_game';
            } 
            params.params['closedPlayer.isMush'] = this.mushPlayersFilter;

            ModerationService.getPlayerInfoList(params)
                .then((result) => {
                    return result.data;
                })
                .then((remoteRowData: any) => {
                    this.rowData = remoteRowData['hydra:member'];
                    this.pagination.totalItem = remoteRowData['hydra:totalItems'];
                    this.pagination.totalPage = this.pagination.totalItem / this.pagination.pageSize;
                    this.loading = false;
                });
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
        quarantinePlayer(playerId: number) {
            ModerationService.quarantinePlayer(playerId)
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
}

button {
    @include button-style();
    padding: 2px 15px 4px;
}

</style>
