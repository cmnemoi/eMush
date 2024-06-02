<template>
    <div class="user_list_container">
        <h2 class="sanction_heading">{{ $t('moderation.sanctionsFor', { username: username }) }}</h2>
        <div class="sanction_filter_options">
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
            <label>{{ $t('moderation.sanctionType') }}
                <select v-model="typeFilter" @change="updateFilter">
                    <option value="">{{ $t('moderation.sanction.allTypes') }}</option>
                    <option v-for="type in moderationSanctionTypes()" :value="type.value" :key="type.key">{{ $t(type.key) }}</option>
                </select>
            </label>
            <label>{{ $t('moderation.sanctionReason') }}
                <select v-model="reasonFilter" @change="updateFilter">
                    <option value="">{{ $t('moderation.reason.allReasons') }}</option>
                    <option v-for="reason in moderationReasons()" :value="reason.value" :key="reason.key">{{ $t(reason.key) }}</option>
                </select>
            </label>
            <label>{{ $t('moderation.isSanctionActive') }}
                <input
                    type="checkbox"
                    class=""
                    placeholder=""
                    aria-controls="example"
                    v-model="isActiveFilter"
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
            @row-click="showSanctionDetails"
        >
            <template #header-actions>
                Actions
            </template>
            <template #row-actions="sanction">
                <button class="action-button" @click="showSanctionDetails(sanction)">Voir Détails</button>
            </template>
        </Datatable>
        <SanctionDetailPage
            :isOpen="showModal"
            :moderationSanction="selectedSanction"
            :username="username"
            @close="showModal = false"
        />
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import urlJoin from "url-join";
import Datatable from "@/components/Utils/Datatable/Datatable.vue";
import qs from "qs";
import ApiService from "@/services/api.service";
import { mapGetters } from "vuex";
import ModerationService from "@/services/moderation.service";
import { Tippy } from "vue-tippy";
import SanctionDetailPage from "@/components/Moderation/SanctionDetailPage.vue";
import { moderationReasons, moderationSanctionTypes } from "@/enums/moderation_reason.enum";

interface SanctionListData {
    userId: string,
    username: string,
    fields: Array<{ key: string; name: string; sortable?: boolean; slot?: boolean }>,
    pagination: { currentPage: number; pageSize: number; totalItem: number; totalPage: number },
    rowData: never[],
    filter: string,
    sortField: string,
    sortDirection: string,
    loading: boolean,
    pageSizeOptions: { text: number; value: number }[],
    typeFilter: string,
    reasonFilter: string,
    isActiveFilter: boolean,
    showModal: boolean,
    selectedSanction: any
}

export default defineComponent({
    name: "SanctionListPage",
    components: {
        Tippy,
        Datatable,
        SanctionDetailPage
    },
    computed: {
        ...mapGetters({
            isAdmin: 'auth/isAdmin',
            isModerator: 'auth/isModerator'
        })
    },
    data(): SanctionListData {
        return {
            userId: '',
            username: '',
            fields: [
                {
                    key: 'moderationAction',
                    name: 'moderation.sanctionType'
                },
                {
                    key: 'reason',
                    name: 'moderation.sanctionReason'
                },
                {
                    key: 'endDate',
                    name: 'moderation.endDate'
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
            ],
            typeFilter: '',
            reasonFilter: '',
            isActiveFilter: false,
            showModal: false,
            selectedSanction: {}
        };
    },
    methods: {
        moderationReasons() {
            return moderationReasons;
        },
        moderationSanctionTypes() {
            return moderationSanctionTypes;
        },
        removeSanction(sanctionId: number) {
            ModerationService.removeSanction(sanctionId)
                .then(() => {
                    this.loadData();
                })
                .catch((error) => {
                    console.error(error);
                });
        },
        suspendSanction(sanctionId: number) {
            ModerationService.suspendSanction(sanctionId)
                .then(() => {
                    this.loadData();
                })
                .catch((error) => {
                    console.error(error);
                });
        },
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
                params.params['username'] = this.filter;
            }
            if (this.sortField) {
                qs.stringify(params.params['order'] = { [this.sortField]: this.sortDirection });
            }
            if (this.typeFilter) {
                params.params['moderationAction'] = this.typeFilter;
            }
            if (this.reasonFilter) {
                params.params['reason'] = this.reasonFilter;
            }
            if (this.isActiveFilter) {
                params.params['startDate[before]'] = 'now';
                params.params['endDate[after]'] = 'now';
            }

            params.params['user.userId'] = this.userId;

            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL, 'moderation_sanctions'), params)
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
        showSanctionDetails(sanction: any) {
            this.selectedSanction = sanction;
            this.showModal = true;
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
        const userId = this.$route.params.userId;
        const username = this.$route.params.username;

        if (typeof userId === 'string') {
            this.userId = userId;
        } else {
            console.error('userId is not a string');
        }

        if (typeof username === 'string') {
            this.username = username;
        } else {
            console.error('username is not a string');
        }

        this.loadData();
    }
});
</script>

<style lang="scss" scoped>
.sanction_filter_options {
    display: flex;
    flex-grow: 1;
    flex-direction: row;
    justify-content: space-between;
    padding: 10px;
}
</style>
