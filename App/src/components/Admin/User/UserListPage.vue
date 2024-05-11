<template>
    <ModerationActionPopup
        :moderation-dialog-visible="moderationDialogVisible"
        :action="{ value: 'ban_user', key: 'moderation.sanction.ban_user' }"
        @close="closeModerationDialog"
        @submit-sanction="banUser" />
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
            <label>{{ $t('moderation.searchByUsername') }}
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
            <template #row-actions="user">
                <router-link :to="{ name: 'SanctionListPage', params: { username: user.username, userId : user.userId } }">{{ $t('moderation.sanctionList') }}</router-link>
                <router-link :to="{ name: 'ModerationUserListUserPage', params: { userId : user.userId } }">{{ $t('moderation.goToUserProfile') }}</router-link>
                <Tippy
                    tag="button"
                    class="action-button"
                    v-if="!user.isBanned"
                    @click="openModerationDialog(user)">
                    {{ $t('moderation.sanction.ban_user') }}
                    <template #content>
                        <h1>{{ $t('moderation.sanction.ban_user') }}</h1>
                        <p>{{ $t('moderation.sanction.banDescription') }}</p>
                    </template>
                </Tippy>
                <router-link :to="{ name: 'AdminUserDetail', params: { userId : user.userId } }" v-if="isAdmin">{{ $t('admin.edit') }}</router-link>
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

interface UserListData {
    fields: [
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
    moderationDialogVisible: boolean,
    currentUser: User|null,
}

export default defineComponent({
    name: "UserListPage",
    components: {
        ModerationActionPopup,
        Datatable
    },
    computed: {
        ...mapGetters({
            isAdmin: 'auth/isAdmin',
            isModerator: 'auth/isModerator'
        })
    },
    data(): UserListData {
        return {
            fields: [
                {
                    key: 'username',
                    name: 'moderation.playerList.user'
                },
                {
                    key: 'userId',
                    name: 'moderation.userList.userId'
                },
                {
                    key: 'roles',
                    name: 'moderation.userList.roles'
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
            ],
            moderationDialogVisible: false,
            currentUser: null
        };
    },
    methods: {
        openModerationDialog(user: User) {
            this.currentUser = user;
            this.moderationDialogVisible = true;
        },
        closeModerationDialog() {
            this.moderationDialogVisible = false;
        },
        banUser(param: any) {
            if (this.currentUser === null || this.currentUser.id === null) {
                return;
            }
            ModerationService.banUser(this.currentUser.id, param)
                .then(() => {
                    this.loadData();
                })
                .catch((error) => {
                    console.error(error);
                });
            this.moderationDialogVisible = false;
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
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+'users'), params)
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
