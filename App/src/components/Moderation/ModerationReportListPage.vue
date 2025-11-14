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
                {{ getDaedalusId(report.playerId) }}
            </template>

            <template #header-authorName>
                {{ $t('moderation.sanction.author') }}
            </template>
            <template #row-authorName="report">
                <Tippy>
                    {{ report.authorName }}
                    <template #content>
                        <h1 v-html="report.authorId" />
                    </template>
                </Tippy>
            </template>

            <template #header-username>
                {{ $t('moderation.sanction.target') }}
            </template>
            <template #row-username="report">
                <img :src="getCharacterBodyFromKey(report?.playerName)" alt="Character Image" style="max-width: 16px;" />
                {{ report.username }}
                <div v-if="getPlayerStatus(report.playerId)" class="alive">
                    {{ $t('moderation.sanction.alive') }}
                </div>
                <div v-if="getPlayerMush(report.playerId)" class="isMush">
                    MUSH
                </div>
            </template>

            <template #header-reason>
                {{ $t('moderation.sanction.reason') }}
            </template>
            <template #row-reason="report">
                {{  getReasonTranslation(report.reason) }}
            </template>

            <template #header-evidence>
                {{ $t('moderation.sanctionDetail.message') }}
            </template>
            <template #row-evidence="report">
                {{ report.message }}
            </template>

            <template #header-startDate>
                {{ $t('moderation.sanction.date') }}
            </template>
            <template #row-startDate="report">
                {{ formatDate(report.startDate) }}
            </template>

            <template #header-actions>
                {{ $t('moderation.sanction.actions') }}
            </template>
            <template #row-actions="report">
                <button class="action-button" @click="showSanctionDetails(report)">{{ $t('moderation.sanctionDetail.report') }}</button>
                <button
                    class="action-button"
                    @click="goToSanctionEvidence(report)">
                    {{ $t('moderation.report.seeContext') }}
                </button>
            </template>

        </Datatable>
        <SanctionDetailPage
            :is-open="showDetailPopup"
            :moderation-sanction="selectedSanction"
            @close="closeDetailPopUp"
            @update="closeDetailAndUpdate"
        />
    </div>
</template>

<script lang="ts">
import SanctionDetailPage from "@/components/Moderation/SanctionDetailPage.vue";
import Datatable from "@/components/Utils/Datatable/Datatable.vue";
import { ClosedPlayer } from "@/entities/ClosedPlayer";
import { ModerationSanction } from "@/entities/ModerationSanction";
import { ModerationViewPlayer } from "@/entities/ModerationViewPlayer";
import { characterEnum } from "@/enums/character";
import { moderationReasons, moderationSanctionTypes } from "@/enums/moderation_reason.enum";
import router from "@/router";
import ApiService from "@/services/api.service";
import ModerationService from "@/services/moderation.service";
import qs from "qs";
import urlJoin from "url-join";
import { defineComponent } from "vue";
import { mapGetters } from "vuex";

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
    name: "SanctionListPage",
    components: {
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
                    key: 'authorName',
                    name: 'moderation.sanctionDetail.author',
                    slot:true,
                    sortable: true
                },
                {
                    key: 'username',
                    name: 'admin.user.username',
                    slot:true,
                    sortable: false
                },
                {
                    key: 'reason',
                    name: 'moderation.sanctionReason',
                    slot: true,
                    sortable: true
                },
                {
                    key: 'evidence',
                    name: 'moderation.sanctionDetail.evidence',
                    slot: true,
                    sortable: false
                },
                {
                    key: 'startDate',
                    name: 'moderation.sanctionDetail.date',
                    slot: true,
                    sortable: true
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
        getDaedalusId(playerId: number) {
            const player = this.playerInfo.find(player => player.id === playerId);
            if(player) {
                return player.daedalusId;
            } else {
                return null;
            }
        },
        getPlayerStatus(playerId: number) {
            const player = this.playerInfo.find(player => player.id === playerId);
            if(player) {
                return player.isAlive;
            } else {
                return null;
            }
        },
        getPlayerMush(playerId: number) {
            const player = this.playerInfo.find(player => player.id === playerId);
            if(player) {
                return player.isMush;
            } else {
                return null;
            }
        },
        async loadPlayerInfo(playerId: number) {
            if (!this.playerInfo.find(player => player.id === playerId)) {
                try {
                    const response = await ModerationService.getModerationViewPlayer(playerId);
                    this.playerInfo.push(new ModerationViewPlayer().load(response.data));
                } catch (error) {
                    console.error(error);
                }
            }
        },
        getCharacterBodyFromKey(characterKey: string) {
            return characterEnum[characterKey].body;
        },
        getReasonTranslation(reason) {
            const reasonObj = moderationReasons.find(item => item.value === reason);
            return reasonObj ? this.$t(reasonObj.key) : reason;
        },
        formatDate(date: string): string {
            const currentDate = new Date();
            const reportDate = new Date(date);

            // if today or yesterday, special format
            if(currentDate.toDateString() === reportDate.toDateString()) {
                return `${this.$t('moderation.sanctionDetail.today')} ${this.$t('moderation.sanctionDetail.to')} ${reportDate.toLocaleTimeString(this.currentLocale, { hour: "numeric", minute: "numeric" })}`;
            }

            if(new Date(currentDate.setDate(currentDate.getDate() - 1)).toDateString() === reportDate.toDateString()) {
                return `${this.$t('moderation.sanctionDetail.yesterday')} ${this.$t('moderation.sanctionDetail.to')} ${reportDate.toLocaleTimeString(this.currentLocale, { hour: "numeric", minute: "numeric" })}`;
            }

            return reportDate.toLocaleDateString(this.currentLocale, { month: "long", day: "numeric", hour: "numeric", minute: "numeric" });
        },
        closeDetailAndUpdate() {
            this.showDetailPopup = false;
            this.loadData();
        },
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
        async loadData() {
            this.loading = true;
            this.rowData = [];

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

            try {
                const result = await ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL, 'moderation_sanctions'), params);
                const remoteRowData = result.data;

                for (const reportData of remoteRowData['hydra:member']) {
                    if (reportData) {
                        const moderationSanction = new ModerationSanction().load(reportData);
                        await this.loadPlayerInfo(reportData.playerId);
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
        async getClosedDaedalusId(closedPlayerId: number): Promise<number>
        {
            try {
                const result = await ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL, 'closed_players', String(closedPlayerId)));
                const closedPlayer = (new ClosedPlayer()).load(result.data);
                return closedPlayer.closedDaedalusId;
            } catch (error) {
                throw error;
            }
        },
        async goToSanctionEvidence(sanction: any)
        {
            const sanctionEvidence = sanction.sanctionEvidence;
            const evidenceClass = sanctionEvidence.className;

            if (
                evidenceClass === 'message' ||
                evidenceClass === 'roomLog' ||
                evidenceClass === 'commanderMission' ||
                evidenceClass === 'comManagerAnnouncement'
            ) {
                router.push({ name: 'ModerationViewPlayerDetail', params: { playerId: sanction.playerId } });
            } else if (evidenceClass === 'closedPlayer') {
                const closedDaedalusId = await this.getClosedDaedalusId(sanctionEvidence.id);
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

  .alive {
    color:red;
  }

  .isMush {
    color: pink;
  }
</style>
