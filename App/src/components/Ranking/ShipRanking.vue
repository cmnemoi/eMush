<template>
    <div class="ship_ranking_container box-container">
        <div class="label-container">
            <label>{{ $t('ranking.languages') }}
                <select v-model="language" @change="updateFilter">
                    <option
                        v-for="option in languagesOption"
                        :value=option.value
                        :key=option.key
                    >
                        {{ $t(option.key) }}
                    </option>
                </select>
            </label>
            <label>{{ $t('ranking.category') }}
                <select v-model="category" @change="updateCategory">
                    <option
                        v-for="option in categoryOption"
                        :value=option.value
                        :key=option.key
                    >
                        {{ $t(option.key) }}
                    </option>
                </select>
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
                {{ $t('ranking.linkToTheEnd') }}
            </template>
            <template #row-actions="slotProps">

                <router-link class="router" :to="{ name: 'TheEnd', params: { closedDaedalusId: slotProps.id } }">
                    <img :src="getImgUrl('ui_icons/right.png')" id="arrow" />  {{  $t('ranking.goToTheEnd') }}
                </router-link>
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
import { getImgUrl } from "@/utils/getImgUrl";

export default defineComponent({
    name: "ShipRanking",
    components: {
        Datatable
    },
    data() {
        return {
            languagesOption: [
                { key: 'ranking.all', value: '' },
                { key: 'ranking.french', value: 'fr' },
                { key: 'ranking.english', value: 'en' }
            ],
            language: '',
            categoryOption: [
                { key: 'ranking.timeSurvived', value: 'timeSurvived' },
                { key: 'ranking.humanTriumph', value: 'humanTriumph' },
                { key: 'ranking.mushTriumph', value: 'mushTriumph' }
            ],
            category: 'timeSurvived',
            fields: [
                {
                    key: 'endCause',
                    name: 'ranking.ship_end_cause',
                    sortable: false
                },
                {
                    key: 'daysSurvived',
                    name: 'ranking.daysSurvived',
                    sortable: false
                },
                {
                    key: 'cyclesSurvived',
                    name: 'ranking.cyclesSurvived',
                    sortable: false
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
        getImgUrl,
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
            if (this.language) {
                params.params['daedalusInfo.localizationConfig.language'] = this.language;
            }

            if (this.sortField) {
                qs.stringify(params.params['order'] = { [this.sortField]: this.sortDirection });
            }

            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+'closed_daedaluses'), params)
                .then((result) => {
                    for (const closedDaedalus of result.data['hydra:member']) {
                        closedDaedalus.endCause = this.$t('ranking.endCause.' + closedDaedalus.endCause);
                        closedDaedalus.humanTriumphIcon = this.getImgUrl('ui_icons/player_variables/triumph.png');
                        closedDaedalus.mushTriumphIcon = this.getImgUrl('ui_icons/player_variables/triumph_mush.png');
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
        updateCategory() {
            if (this.category === 'timeSurvived') {
                this.sortField = 'daysSurvived';
            } else if (this.category === 'humanTriumph') {
                this.sortField = 'humanTriumphSum';
            } else if (this.category === 'mushTriumph') {
                this.sortField = 'mushTriumphSum';
            }
            this.fields = this.getFieldFromCategory(this.category);
            this.updateFilter();
        },
        getFieldFromCategory(category: string): Array <{key: string; name: string; sortable: boolean; slot?: boolean; image?: string}> {
            switch (category) {
            case 'timeSurvived':
                return [
                    {
                        key: 'endCause',
                        name: 'ranking.ship_end_cause',
                        sortable: false
                    },
                    {
                        key: 'daysSurvived',
                        name: 'ranking.daysSurvived',
                        sortable: false
                    },
                    {
                        key: 'cyclesSurvived',
                        name: 'ranking.cyclesSurvived',
                        sortable: false
                    },
                    {
                        key: 'actions',
                        name: 'ranking.goToTheEnd',
                        sortable: false,
                        slot: true
                    }
                ];
            case 'humanTriumph':
                return [
                    {
                        key: 'endCause',
                        name: 'ranking.ship_end_cause',
                        sortable: false
                    },
                    {
                        key: 'humanTriumphSum',
                        name: 'ranking.triumphSum',
                        sortable: false,
                        image: 'humanTriumphIcon'
                    },
                    {
                        key: 'highestHumanTriumph',
                        name: 'ranking.highestTriumph',
                        sortable: false,
                        image: 'humanTriumphIcon'
                    },
                    {
                        key: 'actions',
                        name: 'ranking.goToTheEnd',
                        sortable: false,
                        slot: true
                    }
                ];
            case 'mushTriumph':
                return [
                    {
                        key: 'endCause',
                        name: 'ranking.ship_end_cause',
                        sortable: false
                    },
                    {
                        key: 'mushTriumphSum',
                        name: 'ranking.triumphSum',
                        sortable: false,
                        image: 'mushTriumphIcon'
                    },
                    {
                        key: 'highestMushTriumph',
                        name: 'ranking.highestTriumph',
                        sortable: false,
                        image: 'mushTriumphIcon'
                    },
                    {
                        key: 'actions',
                        name: 'ranking.goToTheEnd',
                        sortable: false,
                        slot: true
                    }
                ];
            }
            return [];
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

.label-container {
    display: inline-flex;
    flex-direction: row;
    flex-wrap: wrap;

    & > label {
        margin-right: 5px;
        margin-bottom: 5px;
    }
}

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
