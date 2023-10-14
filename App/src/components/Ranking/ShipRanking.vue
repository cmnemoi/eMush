<template>
    <div class="ship_ranking_container box-container">
        <!--        <div class="ship_ranking_options">
            <select v-model="pagination.pageSize" @change="updateFilter">
                <option
                    v-for="option in pageSizeOptions"
                    :value="option.value"
                    :key=option.value
                >
                    {{ option.text }}
                </option>
            </select>
        </div>-->
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
            <template #header-actions>
                {{ $t('ranking.linkToTheEnd') }}
            </template>
            <template #row-actions="slotProps">
                
                <router-link class="router" :to="{ name: 'TheEnd', params: { closedDaedalusId: slotProps.id } }"> <img :src="require('@/assets/images/right.png')" id="arrow" />  {{  $t('ranking.goToTheEnd') }}</router-link>
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
    name: "ShipRanking",
    components: {
        Datatable
    },
    data() {
        return {
            fields: [
                {
                    key: 'endCause',
                    name: 'ranking.ship_end_cause',
                    sortable: false
                },
                {
                    key: 'daysSurvived',
                    name: 'ranking.day',
                    sortable: true,
                },
                {
                    key: 'cyclesSurvived',
                    name: 'ranking.cycle',
                    sortable: false,
                },
                {
                    key: 'actions',
                    name: 'ranking.goToTheEnd',
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
            sortField: 'endDay',
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
            if (this.sortField) {
                qs.stringify(params.params['order'] = { [this.sortField]: this.sortDirection });
            }
            ApiService.get(urlJoin(import.meta.env.VITE_API_URL+'closed_daedaluses'), params)
                .then((result) => {
                    for (const closedDaedalus of result.data['hydra:member']) {
                        closedDaedalus.endCause = this.$t('ranking.endCause.' + closedDaedalus.endCause);
                        closedDaedalus.daysSurvived = closedDaedalus.endDay - 1;
                        closedDaedalus.cyclesSurvived = (closedDaedalus.endDay - 1) * 8 + closedDaedalus.endCycle - closedDaedalus.startCycle;
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

.router{
    padding-left: 5px;
}

#arrow {
    top: 0;
}
</style>
