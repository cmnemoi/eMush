<template>
    <div class="heading">
        <h2>{{ $t('moderation.sanctionsFor', {username: username}) }}</h2>
        <router-link
            :to="{ name: 'ModerationUserListUserPage', params: { userId: userId } }"
            class="action-button"
        >
            {{ $t('moderation.goToUserProfile') }}
        </router-link>
    </div>

    <div class="table-filter-container">
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
        :headers='sanctionFields'
        :uri="uri"
        :loading="loading"
        :row-data="rowData"
        :pagination="pagination"
        :filter="filter"
        @pagination-click="paginationClick"
        @sort-table="sortTable"
    >
        <template #header-id>
            #
        </template>
        <template #row-id="sanction">
            {{ sanction.id }}
        </template>

        <template #header-moderationAction>
            {{ $t("moderation.table.type") }}
        </template>
        <template #row-moderationAction="sanction">
            {{ $t(`moderation.type.${sanction.moderationAction}`) }}
        </template>

        <template #header-authorName>
            {{ $t('moderation.table.moderator') }}
        </template>
        <template #row-authorName="sanction">
            <ModerationActorCell :actor="sanction.author"/>
        </template>

        <template #header-reason>
            {{ $t("moderation.table.reason") }}
        </template>
        <template #row-reason="sanction">
            {{ $t("moderation.reason." + sanction.reason) }}
        </template>

        <template #header-message>
            {{ $t("moderation.table.moderatorMessage") }}
        </template>
        <template #row-message="sanction">
            <ModerationCollapsibleMessage :message="sanction.message"/>
        </template>

        <template #header-startDate>
            {{ $t("moderation.table.sanctionStartDate") }}
        </template>
        <template #row-startDate="sanction">
            {{ formatModerationDate(sanction.startDate, currentLocale, $t) }}
        </template>

        <template #header-endDate>
            {{ $t("moderation.table.sanctionEndDate") }}
        </template>

        <template #row-endDate="sanction">
            <template v-if="sanction.moderationAction === 'quarantine_player'">
                N/A
            </template>
            <template v-else>
                {{ formatModerationDate(sanction.endDate, currentLocale, $t) }}
            </template>
        </template>

        <template #header-actions>
            {{ $t("moderation.table.actions") }}
        </template>
        <template #row-actions="sanction">
            <ModerationRowActions
                :sanction="sanction"
                @detail="showSanctionDetails"
            />
        </template>
    </Datatable>

    <h2 class="sanction_heading">{{ $t('moderation.reportsFor', {username: username}) }}</h2>

    <Datatable
        :headers='reportFields'
        :uri="uri"
        :loading="loading"
        :row-data="reportRowData"
        :pagination="pagination"
        :filter="filter"
        @pagination-click="paginationClick"
        @sort-table="sortTable"
    >
        <template #header-id>
            #
        </template>
        <template #row-id="sanction">
            {{ sanction.id }}
        </template>

        <template #header-daedalusId>
            Daedalus
        </template>
        <template #row-daedalusId="report">
            <router-link
                v-if="getDaedalusId(playerInfo, report.user.playerId)"
                :to="{ name: 'ModerationShipView', params: { daedalusId: getDaedalusId(playerInfo, report.user.playerId) } }"
                class="action-button"
            >
                {{ getDaedalusId(playerInfo, report.user.playerId) }}
            </router-link>
        </template>

        <template #header-authorName>
            {{ $t('moderation.table.complainant') }}
        </template>
        <template #row-authorName="sanction">
            <ModerationActorCell
                :actor="sanction.author"
                :status="{ isAlive: getPlayerStatus(playerInfo, sanction.author.playerId), isMush: getPlayerMush(playerInfo, sanction.author.playerId) }"
            />
        </template>

        <template #header-reason>
            {{ $t("moderation.table.reason") }}
        </template>
        <template #row-reason="sanction">
            {{ $t("moderation.reason." + sanction.reason) }}
        </template>

        <template #header-message>
            {{ $t("moderation.table.reporterMessage") }}
        </template>
        <template #row-message="sanction">
            <ModerationCollapsibleMessage :message="sanction.message"/>
        </template>

        <template #header-context>
            {{ $t('moderation.table.context') }}
        </template>
        <template #row-context="sanction">
            <span>{{ $t(`moderation.context.${sanction.sanctionEvidence.className}`) }}</span>
            <button class="action-button" @click="goToReportEvidence(sanction)">
                {{ $t('moderation.report.seeContext') }}
            </button>
        </template>

        <template #header-startDate>
            {{ $t("moderation.table.reportDate") }}
        </template>
        <template #row-startDate="sanction">
            {{ formatModerationDate(sanction.startDate, currentLocale, $t) }}
        </template>

        <template #header-actions>
            {{ $t("moderation.table.actions") }}
        </template>
        <template #row-actions="sanction">
            <ModerationRowActions
                :sanction="sanction"
                go-to-player
                @detail="showSanctionDetails"
            />
        </template>
    </Datatable>

    <SanctionDetailPage
        :is-open="showDetailPopup"
        :moderation-sanction="selectedSanction"
        @close="showDetailPopup = false"
        @update="closeDetailAndUpdate"
    />
</template>

<script lang="ts">
import ModerationActorCell from "@/components/Moderation/ModerationDatatable/ModerationActorCell.vue";
import ModerationCollapsibleMessage from "@/components/Moderation/ModerationDatatable/ModerationCollapsibleMessage.vue";
import ModerationRowActions from "@/components/Moderation/ModerationDatatable/ModerationRowActions.vue";
import {defineComponent} from "vue";
import urlJoin from "url-join";
import Datatable from "@/components/Utils/Datatable/Datatable.vue";
import qs from "qs";
import ApiService from "@/services/api.service";
import {mapGetters} from "vuex";
import SanctionDetailPage from "@/components/Moderation/SanctionDetailPage.vue";
import {moderationReasons, moderationSanctionTypes} from "@/enums/moderation_reason.enum";
import {ModerationSanction} from "@/entities/ModerationSanction";
import {ModerationViewPlayer} from "@/entities/ModerationViewPlayer";
import {formatModerationDate} from "@/utils/moderation/formatModerationDate";
import {getDaedalusId, getPlayerMush, getPlayerStatus, loadPlayerInfo} from "@/utils/moderation/playerStatusLookup";
import {goToReportEvidence} from "@/utils/moderation/sanctionEvidenceNavigation";
import ModerationActionPopup from "@/components/Moderation/ModerationActionPopup.vue";

interface SanctionListData {
    userId: string,
    username: string,
    sanctionFields: Array<{ key: string; name: string; sortable?: boolean; slot?: boolean }>,
    reportFields: Array<{ key: string; name: string; sortable?: boolean; slot?: boolean }>,
    pagination: { currentPage: number; pageSize: number; totalItem: number; totalPage: number },
    rowData: ModerationSanction[],
    reportRowData: ModerationSanction[],
    playerInfo: ModerationViewPlayer[],
    filter: string,
    sortField: string,
    sortDirection: string,
    loading: boolean,
    pageSizeOptions: { text: number; value: number }[],
    typeFilter: string,
    reasonFilter: string,
    isActiveFilter: boolean,
    showDetailPopup: boolean,
    selectedSanction: ModerationSanction
}

export default defineComponent({
    name: "SanctionListPage",
    components: {
        ModerationActorCell,
        ModerationCollapsibleMessage,
        ModerationRowActions: ModerationRowActions,
        Datatable,
        SanctionDetailPage
    },
    computed: {
        ModerationActionPopup() {
            return ModerationActionPopup
        },
        ...mapGetters({
            isAdmin: 'auth/isAdmin',
            isModerator: 'auth/isModerator'
        }),
        currentLocale() {
            return this.$i18n.locale;
        }
    },
    data(): SanctionListData {
        return {
            userId: '',
            username: '',
            sanctionFields: [
                {
                    key: 'id',
                    name: 'moderation.sanctionId',
                    slot: true
                },
                {
                    key: 'moderationAction',
                    name: 'moderation.table.type',
                    slot: true
                },
                {
                    key: 'startDate',
                    name: 'moderation.table.sanctionStartDate',
                    slot: true
                },
                {
                    key: 'endDate',
                    name: 'moderation.table.sanctionEndDate',
                    slot: true
                },
                {
                    key: 'authorName',
                    name: 'moderation.table.moderator',
                    slot: true
                },
                {
                    key: 'reason',
                    name: 'moderation.table.reason',
                    slot: true
                },
                {
                    key: 'message',
                    name: 'moderation.table.moderatorMessage',
                    slot: true
                },
                {
                    key: 'actions',
                    name: 'moderation.table.actions',
                    slot: true
                },
            ],
            reportFields: [
                {
                    key: 'id',
                    name: 'moderation.sanctionId',
                    slot: true
                },
                {
                    key: 'daedalusId',
                    name: 'Daedalus',
                    slot: true,
                    sortable: false
                },
                {
                    key: 'startDate',
                    name: 'moderation.table.reportDate',
                    slot: true
                },
                {
                    key: 'authorName',
                    name: 'moderation.table.moderator',
                    slot: true
                },
                {
                    key: 'reason',
                    name: 'moderation.table.reason',
                    slot: true
                },
                {
                    key: 'message',
                    name: 'moderation.table.reporterMessage',
                    slot: true
                },
                {
                    key: 'context',
                    name: 'moderation.table.context',
                    slot: true
                },
                {
                    key: 'actions',
                    name: 'moderation.table.actions',
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
            reportRowData: [],
            playerInfo: [],
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
            showDetailPopup: false,
            selectedSanction: new ModerationSanction()
        };
    },
    methods: {
        getDaedalusId,
        formatModerationDate,
        getPlayerStatus,
        getPlayerMush,
        goToReportEvidence,
        moderationReasons() {
            return moderationReasons;
        },
        moderationSanctionTypes() {
            return moderationSanctionTypes;
        },
        closeDetailAndUpdate() {
            this.showDetailPopup = false;
            this.loadData();
        },
        async loadData() {
            this.loading = true;
            const params: { header: Record<string, string>; params: Record<string, unknown>; paramsSerializer: typeof qs.stringify } = {
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
                qs.stringify(params.params['order'] = {[this.sortField]: this.sortDirection});
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

            // sanctions
            params.params['isReport'] = false;

            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL, 'moderation_sanctions'), params)
                .then((result) => {
                    return result.data;
                })
                .then((remoteRowData: { 'hydra:member': object[]; 'hydra:totalItems': number }) => {
                    this.rowData = remoteRowData['hydra:member'].map((reportData: object) => {
                        return (new ModerationSanction()).load(reportData);
                    });
                    this.pagination.totalItem = remoteRowData['hydra:totalItems'];
                    this.pagination.totalPage = this.pagination.totalItem / this.pagination.pageSize;
                    this.loading = false;
                });

            // reports
            params.params['isReport'] = true;

            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL, 'moderation_sanctions'), params)
                .then((result) => {
                    return result.data;
                })
                .then(async (remoteRowData: { 'hydra:member': object[]; 'hydra:totalItems': number }) => {
                    this.reportRowData = [];
                    for (const reportData of remoteRowData['hydra:member']) {
                        const sanction = (new ModerationSanction()).load(reportData);
                        await loadPlayerInfo(this.playerInfo, sanction.author.playerId);
                        await loadPlayerInfo(this.playerInfo, sanction.user.playerId);
                        this.reportRowData.push(sanction);
                    }
                    this.pagination.totalItem = remoteRowData['hydra:totalItems'];
                    this.pagination.totalPage = this.pagination.totalItem / this.pagination.pageSize;
                    this.loading = false;
                });

        },
        sortTable(selectedField: { key: string; name: string; sortable?: boolean; slot?: boolean }): void {
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
        showSanctionDetails(sanction: ModerationSanction) {
            this.selectedSanction = sanction;
            this.showDetailPopup = true;
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
.action-button {
    width: 300px;
    margin: 0.2rem;

    @include button-style();
}

:deep(th), :deep(td) {
    text-align: center !important;

    .action-button {
        width: 100%;
    }
}

.heading {
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.sanction_filter_options {
    display: flex;
    flex-grow: 1;
    flex-direction: row;
    justify-content: space-between;
    padding: 10px;
}
</style>
