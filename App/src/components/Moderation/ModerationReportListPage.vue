<template>
    <h2 class="sanction_heading">{{ $t('moderation.reportToAddress') }}</h2>
    <Datatable
        :headers='fields'
        :uri="uri"
        :loading="loading"
        :row-data="rowData"
        :pagination="pagination"
        @pagination-click="paginationClick"
        @sort-table="sortTable"
    >
        <template #header-id>
            #
        </template>
        <template #row-id="report">
            {{ report.id }}
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
        <template #row-authorName="report">
            <ModerationActorCell
                :actor="report.author"
                :is-alive="getPlayerStatus(playerInfo, report.author.playerId)"
                :is-mush="getPlayerMush(playerInfo, report.author.playerId)"
            />
        </template>

        <template #header-username>
            {{ $t('moderation.table.target') }}
        </template>
        <template #row-username="report">
            <ModerationActorCell
                :actor="report.user"
                :is-alive="getPlayerStatus(playerInfo, report.user.playerId)"
                :is-mush="getPlayerMush(playerInfo, report.user.playerId)"
            />
        </template>

        <template #header-reason>
            {{ $t('moderation.table.reason') }}
        </template>
        <template #row-reason="report">
            {{ $t(`moderation.reason.${report.reason}`) }}
        </template>

        <template #header-message>
            {{ $t('moderation.table.reporterMessage') }}
        </template>
        <template #row-message="report">
            <div class="text">
                <span>{{ report.message }}</span>
            </div>
        </template>

        <template #header-context>
            {{ $t('moderation.table.context') }}
        </template>
        <template #row-context="report">
            <span>{{ $t(`moderation.context.${report.sanctionEvidence.className}`) }}</span>
            <button class="action-button" @click="goToReportEvidence(report)">
                {{ $t('moderation.report.seeContext') }}
            </button>
        </template>

        <template #header-startDate>
            {{ $t('moderation.table.reportDate') }}
        </template>
        <template #row-startDate="report">
            {{ formatModerationDate(report.startDate, currentLocale, $t) }}
        </template>

        <template #header-actions>
            {{ $t('moderation.table.actions') }}
        </template>
        <template #row-actions="report">
            <ModerationRowActions
                :sanction="report"
                go-to-player
                sanction-list
                @detail="showSanctionDetails"
            />
        </template>

    </Datatable>
    <SanctionDetailPage
        :is-open="showDetailPopup"
        :moderation-sanction="selectedSanction"
        @close="closeDetailPopUp"
        @update="closeDetailAndUpdate"
    />
</template>

<script lang="ts">
import ModerationActorCell from "@/components/Moderation/ModerationDatatable/ModerationActorCell.vue";
import ModerationRowActions from "@/components/Moderation/ModerationDatatable/ModerationRowActions.vue";
import SanctionDetailPage from "@/components/Moderation/SanctionDetailPage.vue";
import Datatable, { Header } from "@/components/Utils/Datatable/Datatable.vue";
import { ModerationSanction } from "@/entities/ModerationSanction";
import { ModerationViewPlayer } from "@/entities/ModerationViewPlayer";
import { moderationReasons, moderationSanctionTypes } from "@/enums/moderation_reason.enum";
import ApiService from "@/services/api.service";
import qs from "qs";
import urlJoin from "url-join";
import { defineComponent } from "vue";
import { mapGetters } from "vuex";
import { formatModerationDate } from "@/utils/moderation/formatModerationDate";
import { getDaedalusId, getPlayerMush, getPlayerStatus, loadPlayerInfo } from "@/utils/moderation/playerStatusLookup";
import { goToReportEvidence } from "@/utils/moderation/sanctionEvidenceNavigation";

interface SanctionListData {
    userId: string,
    username: string,
    fields: Array<{ key: string; name: string; sortable?: boolean; slot?: boolean }>,
    pagination: { currentPage: number; pageSize: number; totalItem: number; totalPage: number },
    rowData: ModerationSanction[],
    filter: string,
    playerInfo: ModerationViewPlayer[],
    sortField: string,
    sortDirection: string,
    loading: boolean,
    pageSizeOptions: { text: number; value: number }[],
    typeFilter: string,
    reasonFilter: string,
    isActiveFilter: boolean,
    showModal: boolean,
    showDetailPopup: boolean,
    selectedSanction: ModerationSanction
}

export default defineComponent({
    name: "ModerationReportListPage",
    components: {
        ModerationActorCell,
        ModerationRowActions: ModerationRowActions,
        Datatable,
        SanctionDetailPage
    },
    computed: {
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
            fields: [
                {
                    key: 'id',
                    name: 'moderation.sanction.id',
                    slot:true,
                    sortable: false
                },
                {
                    key: 'daedalusId',
                    name: 'Daedalus',
                    slot:true,
                    sortable: false
                },
                {
                    key: 'startDate',
                    name: 'moderation.table.reportDate',
                    slot: true,
                    sortable: true
                },
                {
                    key: 'authorName',
                    name: 'moderation.table.complainant',
                    slot:true,
                    sortable: true
                },
                {
                    key: 'username',
                    name: 'moderation.table.target',
                    slot:true,
                    sortable: false
                },
                {
                    key: 'reason',
                    name: 'moderation.table.reason',
                    slot: true,
                    sortable: true
                },
                {
                    key: 'message',
                    name: 'moderation.table.reporterMessage',
                    slot: true,
                    sortable: false
                },
                {
                    key: 'context',
                    name: 'moderation.table.context',
                    slot: true,
                    sortable: false
                },
                {
                    key: 'actions',
                    name: 'moderation.table.actions',
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
            playerInfo: [],
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
            showDetailPopup: false,
            selectedSanction: new ModerationSanction()
        };
    },
    methods: {
        formatModerationDate,
        getDaedalusId,
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
        closeDetailPopUp() {
            this.showDetailPopup = false;
            this.loadData();
        },
        async loadData() {
            this.loading = true;
            this.rowData = [];

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
            if (this.sortField) {
                qs.stringify(params.params['order'] = { [this.sortField]: this.sortDirection });
            }

            params.params['moderationAction'] = 'report';

            try {
                const result = await ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL, 'moderation_sanctions'), params);
                const remoteRowData = result.data;

                for (const reportData of remoteRowData['hydra:member']) {
                    if (reportData) {
                        const moderationSanction = new ModerationSanction().load(reportData);
                        await loadPlayerInfo(this.playerInfo, reportData.user?.playerId);
                        await loadPlayerInfo(this.playerInfo, reportData.author?.playerId);
                        this.rowData.push(moderationSanction);
                    }
                }
                this.pagination.totalItem = remoteRowData['hydra:totalItems'];
                this.pagination.totalPage = this.pagination.totalItem / this.pagination.pageSize;
            } catch (error) {
                console.error(error);
            } finally {
                this.loading = false;
            }
        },
        sortTable(selectedField: Header): void {
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
:deep(th), :deep(td) {
    text-align: center !important;

    .text {
        text-align: left !important;
        min-width: 300px !important;
        font-style: italic;
    }
}

.action-button {
    width: 100%;

    @include button-style();
}
.sanction_filter_options {
    display: flex;
    flex-grow: 1;
    flex-direction: row;
    justify-content: space-between;
    padding: 10px;
}
</style>
