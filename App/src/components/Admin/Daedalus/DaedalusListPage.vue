<template>
    <div class="daedalus_list_container">
        <div class="daedalus_filter_options">
            <label>{{$t("admin.show")}}
                <select v-model="pagination.pageSize" @change="updateFilter">
                    <option
                        v-for="option in pageSizeOptions"
                        :value="option.value"
                        :key=option.value
                    >
                        {{ option.text }}
                    </option>
                </select>
            </label>
            <label>{{$t("admin.search")}}
                <input
                    v-model="filter"
                    type="search"
                    class=""
                    placeholder=""
                    aria-controls="example"
                    @change="updateFilter"
                >
            </label>
            <DropList class="align-right" :name="$t('admin.daedalus.globalActions')">
                <router-link :to="{ name: 'AdminDaedalusCreate' }">{{$t("admin.daedalus.create")}}</router-link>
                <router-link :to="{ name: 'AdminNeronAnnouncement' }">{{$t("admin.neronAnnouncement.sendNeronAnnouncement")}}</router-link>
                <button type="button" @click="destroyAllDaedaluses">
                    {{$t("admin.daedalus.destroyAllDaedaluses")}}
                </button>
                <button
                    type="button"
                    @click="removeGameFromMaintenance"
                    v-if="gameInMaintenance()">
                    {{$t("admin.daedalus.maintenanceOff")}}
                </button>
                <button
                    type="button"
                    @click="putGameInMaintenance"
                    v-else>
                    {{$t("admin.daedalus.maintenanceOn")}}
                </button>
            </DropList>
        </div>
        <Datatable
            :headers='fields'
            :uri="uri"
            :loading="loading"
            :row-data="rowData"
            :pagination="pagination"
            :filter="filter"
            @pagination-click="paginationClick"
            @sort-table="sortTable"
        >
            <template #header-cycle>
                Cycle/Day
            </template>
            <template #row-cycle="slotProps">
                {{ slotProps.cycle }} / {{ slotProps.day }} ( {{ $t('admin.updatedAt') }} {{formatDate(slotProps.updatedAt)}})
            </template>
            <template #header-actions>
                Actions
            </template>
            <template #row-actions="slotProps">
                <DropList class="align-right" v-if="!daedalusIsFinished(slotProps)">
                    <button
                        type="button"
                        @click="destroyDaedalus(slotProps.id)">
                        {{ $t("admin.daedalus.destroy") }}
                    </button>
                    <button
                        type="button"
                        @click="unlockDaedalus(slotProps.id)">
                        {{ $t("admin.daedalus.unlock") }}
                    </button>
                    <button
                        type="button"
                        @click="addNewRoomsToDaedalus(slotProps.id)">
                        {{ $t("admin.daedalus.addNewRooms") }}
                    </button>
                    <button
                        type="button"
                        @click="deleteDaedalusDuplicatedAlertElements(slotProps.id)">
                        {{ $t("admin.daedalus.deleteDuplicatedAlertElements") }}
                    </button>
                    <button
                        type="button"
                        @click="createAPlanet(slotProps.id)">
                        {{ $t("admin.daedalus.createAPlanet") }}
                    </button>
                </DropList>
            </template>

        </Datatable>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import urlJoin from "url-join";
import Datatable from "@/components/Utils/Datatable/Datatable.vue";
import DropList from "@/components/Utils/DropList.vue";
import qs from "qs";
import ApiService from "@/services/api.service";
import { format } from "date-fns";
import { fr } from "date-fns/locale";
import AdminService from "@/services/admin.service";
import DaedalusService from "@/services/daedalus.service";
import { mapActions, mapGetters } from "vuex";


export default defineComponent({
    name: "DeadalusListPage",
    components: {
        Datatable,
        DropList
    },
    data() {
        return {
            fields: [
                {
                    key: 'id',
                    name: 'id',
                    sortable: true
                },
                {
                    key: 'name',
                    name: 'Name',
                    sortable: true
                },
                {
                    key: 'gameStatus',
                    name: 'gameStatus',
                    sortable: true
                },
                {
                    key: 'cycle',
                    name: 'Cycle/Day',
                    sortable: false,
                    slot: true
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
            filter: '',
            pageSizeOptions: [
                { text: 5, value: 5 },
                { text: 10, value: 10 },
                { text: 20, value: 20 }
            ]
        };
    },
    methods: {
        ...mapGetters({
            gameInMaintenance: 'admin/gameInMaintenance'
        }),
        ...mapActions({
            loadGameMaintenanceStatus: 'admin/loadGameMaintenanceStatus'
        }),
        formatDate: (date: string): string => {
            const dateObject = new Date(date);
            return format(dateObject, 'PPPPpp', { locale: fr });
        },
        async loadData() {
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
            if (this.filter) {
                params.params['name'] = this.filter;
            }
            await ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+'daedaluses'), params)
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
        paginationClick(page: number) {
            this.pagination.currentPage = page;
            this.loadData();
        },
        addNewRoomsToDaedalus(id: number) {
            AdminService.addNewRoomsToDaedalus(id).then(() => {
                this.loadData();
            });
        },
        daedalusIsFinished(daedalus: any) {
            return daedalus.gameStatus === 'finished' || daedalus.gameStatus === 'closed';
        },
        deleteDaedalusDuplicatedAlertElements(id: number) {
            AdminService.deleteDaedalusDuplicatedAlertElements(id).then(() => {
                this.loadData();
            });
        },
        destroyDaedalus(id: number) {
            DaedalusService.destroyDaedalus(id).then(() => {
                this.loadData();
            });
        },
        destroyAllDaedaluses() {
            DaedalusService.destroyAllDaedaluses().then(() => {
                this.loadData();
            });
        },
        putGameInMaintenance() {
            AdminService.putGameInMaintenance().then(() => {
                this.loadGameMaintenanceStatus();
                this.loadData();
            });
        },
        removeGameFromMaintenance() {
            AdminService.removeGameFromMaintenance().then(() => {
                this.loadGameMaintenanceStatus();
                this.loadData();
            });
        },
        unlockDaedalus(id: number) {
            AdminService.unlockDaedalus(id).then(() => {
                this.loadData();
            });
        },
        createAPlanet(id: number) {
            DaedalusService.createAPlanet(id).then(() => {
                this.loadData();
            });
        }
    },
    beforeMount() {
        this.loadGameMaintenanceStatus();
        this.loadData();
    }
});
</script>

<style lang="scss" scoped>

.daedalus_filter_options {
    display: flex;
    flex-grow: 1;
    flex-direction: row;
    justify-content: space-between;
    padding: 10px;
}
</style>
