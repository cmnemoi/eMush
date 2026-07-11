<template>
    <div class="table-filter-container">
        <label>{{$t("admin.show")}}
            <select v-model="pagination.pageSize" @change="updateFilter">
                <option
                    v-for="option in pageSizeOptions"
                    :value="option.value"
                    :key=option.value
                >
                    {{ option.label }}
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
                <button
                    type="button"
                    @click="markDaedalusAsCheater(slotProps.id)">
                    {{ $t("admin.daedalus.markAsCheater") }}
                </button>
                <router-link :to="{ name: 'ModerationShipView', params: { daedalusId : slotProps.id } }">{{ $t('moderation.shipView') }}</router-link>
            </DropList>
            <DropList class="align-right" v-else>
                <button
                    type="button"
                    @click="markDaedalusAsCheater(slotProps.id)">
                    {{ $t("admin.daedalus.markAsCheater") }}
                </button>
                <button
                    type="button"
                    @click="deliverStats(slotProps.id)">
                    {{ $t("admin.daedalus.deliverStats") }}
                </button>
            </DropList>
        </template>
    </Datatable>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import urlJoin from "url-join";
import Datatable from "@/components/Utils/Datatable/Datatable.vue";
import DropList from "@/components/Utils/DropList.vue";
import { format } from "date-fns";
import { fr } from "date-fns/locale";
import AdminService from "@/services/admin.service";
import DaedalusService from "@/services/daedalus.service";
import { mapActions, mapGetters } from "vuex";
import DataTableMixin from "@/mixin/dataTableMixin";


export default defineComponent({
    name: "DeadalusListPage",
    mixins: [DataTableMixin],
    components: {
        Datatable,
        DropList
    },
    data() {
        return {
            endpoint: urlJoin(import.meta.env.VITE_APP_API_URL + 'daedaluses'),
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
            ]
        };
    },
    methods: {
        ...mapGetters({
            gameInMaintenance: 'admin/gameInMaintenance'
        }),
        ...mapActions({
            loadGameMaintenanceStatus: 'admin/loadGameMaintenanceStatus',
            banDaedalus: 'adminActions/markDaedalusAsCheater'
        }),
        formatDate: (date: string): string => {
            const dateObject = new Date(date);
            return format(dateObject, 'PPPPpp', { locale: fr });
        },
        addNewRoomsToDaedalus(id: number) {
            AdminService.addNewRoomsToDaedalus(id).then(() => {
                this.loadData();
            });
        },
        daedalusIsFinished(daedalus: { gameStatus: string }) {
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
        },
        async markDaedalusAsCheater(id: number) {
            await this.banDaedalus({ closedDaedalusId: id }).then(() => {
                this.loadData();
            });
        },
        async deliverStats(id: number) {
            await AdminService.deliverStats(id).then(() => {
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
