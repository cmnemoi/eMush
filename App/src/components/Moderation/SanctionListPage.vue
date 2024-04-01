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
            @paginationClick="paginationClick"
            @sortTable="sortTable"
        >
            <template #header-actions>
                Actions
            </template>
            <template #row-actions="sanction">
                <div v-if="isModerator">
                    <Tippy tag="button"
                           class="action-button"
                           @click="removeSanction(sanction.id)">
                        {{ $t('moderation.removeSanction') }}
                        <template #content>
                            <h1>{{ $t('moderation.removeSanction') }}</h1>
                            <p>{{ $t('moderation.removeSanctionDescription') }}</p>
                        </template>
                    </Tippy>
                    <Tippy tag="button"
                           class="action-button"
                           @click="suspendSanction(sanction.id)">
                        {{ $t('moderation.suspendSanction') }}
                        <template #content>
                            <h1>{{ $t('moderation.suspendSanction') }}</h1>
                            <p>{{ $t('moderation.suspendSanctionDescription') }}</p>
                        </template>
                    </Tippy>
                </div>
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
import { mapGetters } from "vuex";
import ModerationService from "@/services/moderation.service";
import { User } from "@/entities/User";
import ModerationActionPopup from "@/components/Moderation/ModerationActionPopup.vue";
import { Tippy } from "vue-tippy";
import {moderationReasons, moderationSanctionTypes} from "@/enums/moderation_reason.enum";

interface SanctionListData {
    userId: string,
    username: string,
    fields: [
        { key: string; name: string; },
        { key: string; name: string; },
        { key: string; name: string; },
        { key: string; name: string; },
        { key: string; name: string; },
        { key: string; name: string; sortable: false; slot: true; }
    ],
    pagination: { currentPage: number; pageSize: number; totalItem: number; totalPage: number; };
    rowData: never[];
    filter: string;
    sortField: string;
    sortDirection: string;
    loading: boolean;
    pageSizeOptions: { text: number; value: number; }[];
    typeFilter: string,
    reasonFilter: string,
    isActiveFilter: boolean,
}

export default defineComponent({
    name: "SanctionListPage",
    components: {
        Tippy,
        ModerationActionPopup,
        Datatable
    },
    computed: {
        ...mapGetters({
            isAdmin: 'auth/isAdmin',
            isModerator: 'auth/isModerator',
        }),
    },
    data(): SanctionListData {
        return {
            userId: '',
            username: '',
            fields: [
                {
                    key: 'moderationAction',
                    name: 'moderation.sanctionType',
                },
                {
                    key: 'reason',
                    name: 'moderation.sanctionReason',
                },
                {
                    key: 'message',
                    name: 'moderation.adminMessage',
                },
                {
                    key: 'startDate',
                    name: 'moderation.startDate',
                },
                {
                    key: 'endDate',
                    name: 'moderation.endDate',
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
                {text: 5, value: 5},
                {text: 10, value: 10},
                {text: 20, value: 20}
            ],
            typeFilter: '',
            reasonFilter: '',
            isActiveFilter: false,
        };
    },
    methods: {
        moderationReasons() {
            return moderationReasons
        },
        moderationSanctionTypes() {
            return moderationSanctionTypes
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

            ApiService.get(urlJoin(process.env.VUE_APP_API_URL + 'moderation_sanctions'), params)
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
        this.userId = this.$route.params.userId;
        this.username = this.$route.params.username;
        console.log(this.username);
        console.log(this.userId);
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
