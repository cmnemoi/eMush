<template>
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
    <div class="ship_userShips_container">
        <h2>{{ username }}</h2>
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
                {{ $t('userShips.linkToTheEnd') }}
            </template>
            <template #row-actions="slotProps">
                <router-link class="router" :to="{ name: 'TheEnd', params: { closedDaedalusId: slotProps.closedDaedalusId } }"> 
                    <img :src="getImgUrl('src/assets/images/right.png')" id="arrow" />  {{  $t('userShips.goToTheEnd') }}
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
import { characterEnum } from "@/enums/character";
import UserService from "@/services/user.service";
import { getImgUrl } from "@/utils/getImgUrl";

export default defineComponent({
    name: "UserPage",
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
            fields: [
                {
                    key: 'character',
                    name: 'userShips.character',
                    sortable: false,
                    image: 'characterBody'
                },
                {
                    key: 'dayDeath',
                    name: 'userShips.daysSurvived',
                    sortable: true
                },
                {
                    key: 'cyclesSurvived',
                    name: 'userShips.cyclesSurvived',
                    sortable: false
                },
                {
                    key: 'endCause',
                    name: 'userShips.endCauses',
                    sortable: false
                },
                {
                    key: 'actions',
                    name: 'userShips.goToTheEnd',
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
            sortField: 'id',
            sortDirection: 'DESC',
            loading: false,
            pageSizeOptions: [
                { text: 5, value: 5 },
                { text: 10, value: 10 },
                { text: 20, value: 20 }
            ],
            username: ''
        };
    },
    methods: {
        getImgUrl,
        loadData() {
            this.loading = true;
            if (typeof this.$route.params.userId !== 'string') {
                throw new Error('userId is undefined');
            }
            UserService.loadUser(this.$route.params.userId).then((user) => {
                if (!user.username) {
                    throw new Error('User should have a username');
                }
                this.username = user.username;
            });
            const params: any = {
                header: {
                    'accept': 'application/ld+json'
                },
                params: { },
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
            if (this.language) {
                params.params['closedDaedalus.daedalusInfo.localizationConfig.language'] = this.language;
            }

            params.params['playerInfo.user.userId'] = this.$route.params.userId;

            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+'closed_players'), params)
                .then((result) => {
                    for (const closedPlayer of result.data['hydra:member']) {
                        closedPlayer.endCause = this.$t('userShips.endCause.' + closedPlayer.endCause);
                        closedPlayer.character = this.getCharacterNameFromKey(closedPlayer.characterKey);
                        closedPlayer.characterBody = this.getCharacterBodyFromKey(closedPlayer.characterKey);
                        closedPlayer.dayDeath = closedPlayer.daysSurvived; // hack to use API Platform filters...
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
        },
        getCharacterNameFromKey(characterKey: string) {
            return characterEnum[characterKey].name;
        },
        getCharacterBodyFromKey(characterKey: string) {
            return characterEnum[characterKey].body;
        },
        resetOrder() {
            this.sortField = 'id';
            this.sortDirection = 'DESC';
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
