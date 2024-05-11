<template>
    <div class="news_list_container">
        <div class="news_filter_options">
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
            <router-link :to="{ name: 'AdminNewsWrite'}">
                {{$t("admin.newsList.create")}}
            </router-link>
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
                <router-link :to="{ name: 'AdminNewsEdit', params: { newsId : slotProps.id } }">{{ $t('admin.edit') }}</router-link>
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
    name: "NewsListPage",
    components: {
        Datatable
    },
    data() {
        return {
            fields: [
                {
                    key: 'frenchTitle',
                    name: 'admin.newsList.newsTitle',
                    sortable: true
                },
                {
                    key: 'publicationDate',
                    name: 'admin.newsList.publicationDate',
                    sortable: true
                },
                {
                    key: 'updatedAt',
                    name: 'admin.newsList.updatedAt',
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
                params.params['newsName'] = this.filter;
            }
            if (this.sortField) {
                qs.stringify(params.params['order'] = { [this.sortField]: this.sortDirection });
            }
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+'news'), params)
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
.news_filter_options {
    display: flex;
    flex-grow: 1;
    flex-direction: row;
    justify-content: space-between;
    padding: 10px;
}

a {
        @include button-style();
        padding: 2px 15px 4px;

    }
</style>
