<template>
    <div class="user_list_container">
        <div class="user_filter_options">
            <label>{{ $t('admin.show') }}
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
            <label>{{ $t('admin.class') }}
                <select v-model="entityType" @change="updateFilter">
                    <option
                        v-for="option in entityTypeOption"
                        :value=option.value
                        :key=option.value
                    >
                        {{ $t(option.key) }}
                    </option>
                </select>
            </label>
            <label>{{ $t('admin.search') }}:
                <input
                    v-model="filter"
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
            :filter="filter"
            @pagination-click="paginationClick"
            @sort-table="sortTable"
        >
            <template #header-actions>
                Actions
            </template>
            <template #row-actions="slotProps">
                <router-link :to="{ name: 'AdminMechanicsDetail', params: { mechanicsId : slotProps.id } }">{{ $t('admin.edit') }}</router-link>
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

export default defineComponent({
    name: "GameConfigListPage",
    components: {
        Datatable
    },


    data() {
        return {
            entityType: 'blueprints',
            entityTypeOption: [
                { key: 'admin.mechanics.blueprint', value: 'blueprints' },
                { key: 'admin.mechanics.book', value: 'books' },
                { key: 'admin.mechanics.document', value: 'documents' },
                { key: 'admin.mechanics.drug', value: 'drugs' },
                { key: 'admin.mechanics.fruit', value: 'fruits' },
                { key: 'admin.mechanics.gear', value: 'gears' },
                { key: 'admin.mechanics.plant', value: 'plants' },
                { key: 'admin.mechanics.ration', value: 'rations' },
                { key: 'admin.mechanics.weapon', value: 'weapons' },
                { key: 'admin.mechanics.patrolShip', value: 'patrol_ships' }
            ],
            fields: [
                {
                    key: 'id',
                    name: 'Id',
                    sortable: true
                },
                {
                    key: 'name',
                    name: 'Name',
                    sortable: true
                },
                {
                    key: 'actions',
                    name: 'Action',
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
            filter: '',
            sortField: '',
            sortDirection: 'DESC',
            loading: false,
            pageSizeOptions: [
                { text: 5, value: 5 },
                { text: 10, value: 10 },
                { text: 20, value: 20 }
            ]
        };
    },
    methods: {
        loadData() {
            this.loading = true;
            const params: any = {
                header: {
                    'accept': 'application/ld+json'
                },
                params: {},
                paramsSerializer: qs.stringify
            };
            if (this.pagination.currentPage) {
                params.params['page'] = this.pagination.currentPage;
            }
            if (this.pagination.pageSize) {
                params.params['itemsPerPage'] = this.pagination.pageSize;
            }
            if (this.filter) {
                params.params['name'] = this.filter;
            }
            if (this.sortField) {
                qs.stringify(params.params['order'] = { [this.sortField]: this.sortDirection });
            }
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+this.entityType), params)
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
        }
    },
    beforeMount() {
        this.loadData();
    }
});
</script>

<style lang="scss" scoped>
.user_filter_options {
    display: flex;
    flex-grow: 1;
    flex-direction: row;
    justify-content: space-between;
    padding: 10px;
}
</style>
