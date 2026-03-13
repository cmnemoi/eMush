<template>
    <ModerationActionPopup
        :moderation-dialog-visible="moderationDialogVisible"
        :action="{ value: 'ban_user', key: 'moderation.sanction.ban_user' }"
        @close="closeModerationDialog"
        @submit-sanction="banUser" />
    <div class="table-filter-container">
        <label>{{ $t('admin.show') }}
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
            <DropList class="align-right">
                <router-link :to="{ name: 'SanctionListPage', params: { username: user.username, userId : user.userId } }">{{ $t('moderation.sanctionList') }}</router-link>
                <router-link :to="{ name: 'ModerationUserListUserPage', params: { userId : user.userId } }">{{ $t('moderation.goToUserProfile') }}</router-link>
                <Tippy
                    tag="button"
                    v-if="!user.isBanned"
                    @click="openModerationDialog(user)">
                    {{ $t('moderation.sanction.ban_user') }}
                    <template #content>
                        <h1>{{ $t('moderation.sanction.ban_user') }}</h1>
                        <p>{{ $t('moderation.sanction.banDescription') }}</p>
                    </template>
                </Tippy>
                <router-link :to="{ name: 'AdminUserDetail', params: { userId : user.userId } }" v-if="isAdmin">{{ $t('admin.edit') }}</router-link>
                <router-link :to="{ name: 'ModerationUserDetail', params: { userId : user.userId } }" v-if="isModerator && !isAdmin">{{ $t('admin.edit') }}</router-link>
            </DropList>
        </template>
    </Datatable>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import urlJoin from "url-join";
import Datatable from "@/components/Utils/Datatable/Datatable.vue";
import DropList from "@/components/Utils/DropList.vue";
import { mapGetters } from "vuex";
import ModerationService from "@/services/moderation.service";
import { User } from "@/entities/User";
import ModerationActionPopup from "@/components/Moderation/ModerationActionPopup.vue";
import DataTableMixin from "@/mixin/dataTableMixin";

export default defineComponent({
    name: "UserListPage",
    mixins: [DataTableMixin],
    components: {
        ModerationActionPopup,
        Datatable,
        DropList
    },
    computed: {
        ...mapGetters({
            isAdmin: 'auth/isAdmin',
            isModerator: 'auth/isModerator'
        })
    },
    data() {
        return {
            endpoint: urlJoin(import.meta.env.VITE_APP_API_URL + 'users'),
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
                    key: 'createdAt',
                    name: 'moderation.userList.createdAt'
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
            filterField: "username",
            moderationDialogVisible: false,
            currentUser: null as User | null
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
        }
    }
});
</script>
