<template>
    <div class="sanction_list_container">
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
            <template #header-evidence>
                {{ $t('moderation.sanctionDetail.evidence') }}
            </template>
            <template #row-evidence="report">
                {{ report.sanctionEvidence.message }}
                <button
                    class="action-button"
                    @click="goToSanctionEvidence(report)">
                    {{ $t('moderation.report.seeContext') }}
                </button>
            </template>
            <template #header-actions>
                Actions
            </template>
            <template #row-actions="report">
                <button class="action-button" @click="showSanctionDetails(report)">{{ $t('moderation.sanctionDetail.report') }}</button>
            </template>
        </Datatable>
        <SanctionDetailPage
            :is-open="showDetailPopup"
            :moderation-sanction="selectedSanction"
            @close="closeDetailPopUp"
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
import SanctionDetailPage from "@/components/Moderation/SanctionDetailPage.vue";
import { moderationReasons, moderationSanctionTypes } from "@/enums/moderation_reason.enum";
import { ModerationSanction } from "@/entities/ModerationSanction";
import { useRouter } from "vue-router";
import { ClosedPlayer } from "@/entities/ClosedPlayer";
import { ClosedDaedalus } from "@/entities/ClosedDaedalus";
import router from "@/router";

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
                    key: 'username',
                    name: 'admin.user.username'
                },
                {
                    key: 'moderationAction',
                    name: 'moderation.sanctionType'
                },
                {
                    key: 'reason',
                    name: 'moderation.sanctionReason'
                },
                {
                    key: 'evidence',
                    name: 'moderation.sanctionDetail.evidence',
                    slot: true
                },
                {
                    key: 'startDate',
                    name: 'moderation.sanctionDetail.date'
                },
                {
                    key: 'actions',
                    name: 'Actions',
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
            typeFilter: '',
            reasonFilter: '',
            isActiveFilter: false,
            showDetailPopup: false,
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
        closeDetailPopUp() {
            this.showDetailPopup = false;
            this.loadData();
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
            if (this.sortField) {
                qs.stringify(params.params['order'] = { [this.sortField]: this.sortDirection });
            }

            params.params['moderationAction'] = 'report';

            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL, 'moderation_sanctions'), params)
                .then((result) => {
                    return result.data;
                })
                .then((remoteRowData: any) => {
                    this.rowData = remoteRowData['hydra:member'].map((reportData: object) => {
                        return (new ModerationSanction()).load(reportData);
                    });
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
            this.showDetailPopup = true;
        },
        paginationClick(page: number) {
            this.pagination.currentPage = page;
            this.loadData();
        },
        getClosedDaedalusId(closedPlayerId: number): Promise<number>
        {
            const closedPlayer = new ClosedPlayer();
            try {
                const result = ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL, 'closed_players', String(closedPlayerId)));
                closedPlayer.load(result.data);
                return closedPlayer.closedDaedalusId;
            } catch (error) {
                throw error;
            }
        },
        goToSanctionEvidence(sanction: any)
        {
            const sanctionEvidence = sanction.sanctionEvidence;
            const evidenceClass = sanctionEvidence.className;

            if (
                evidenceClass === 'message' ||
                evidenceClass === 'roomLog'
            ) {
                router.push({ name: 'ModerationViewPlayerDetail', params: { playerId: sanction.playerId } });
            } else if (evidenceClass === 'closedPlayer') {
                const closedDaedalusId = this.getClosedDaedalusId(sanctionEvidence.id);
                router.push({ name: 'TheEnd', params: { closedDaedalusId } });
            }
        }
    },
    beforeMount() {
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
