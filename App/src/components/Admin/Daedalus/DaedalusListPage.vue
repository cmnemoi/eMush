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
            <router-link :to="{ name: 'AdminDaedalusCreate' }">{{$t("admin.daedalus.create")}}</router-link>
            <button class = "action-button" type="button" @click="destroyAllDaedaluses">
                {{$t("admin.daedalus.destroyAllDaedaluses")}}
            </button>
        </div>
        <Datatable
            :headers='fields'
            :uri="uri"
            :loading="loading"
            :row-data="rowData"
            :pagination="pagination"
            :filter="filter"
            @paginationClick="paginationClick"
            @sortTable="sortTable"
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
                <button v-if="slotProps.gameStatus != 'finished'"
                        class="action-button"
                        type="button"
                        @click="destroyDaedalus(slotProps.id)">
                    {{ $t("admin.daedalus.destroy") }}
                </button>
            </template>

        </Datatable>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import urlJoin from "url-join";
import Datatable from "@/components/Utils/Datatable/Datatable.vue";
import qs from "qs";
import ApiService from "@/services/api.service";
import { format } from "date-fns";
import { fr } from "date-fns/locale";
import DaedalusService from "@/services/daedalus.service";

export default defineComponent({
    name: "DeadalusListPage",
    components: {
        Datatable
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
            if (this.filter) {
                params.params['name'] = this.filter;
            }
            ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'daedaluses?XDEBUG_SESSION_START=PHPSTORM'), params)
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
        destroyDaedalus(id: number) {
            DaedalusService.destroyDaedalus(id).then(() => {
                this.loadData();
            });
        },
        destroyAllDaedaluses() {
            DaedalusService.destroyAllDaedaluses().then(() => {
                this.loadData();
            });
        }
    },
    beforeMount() {
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

    a {
        @include button-style();
        padding: 2px 15px 4px;

    }
}

</style>
