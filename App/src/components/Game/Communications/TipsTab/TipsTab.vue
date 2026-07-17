<template>
    <ReportPopup
        :report-dialog-visible="reportPopupVisible"
        :select-player="false"
        @close="closeReportDialog"
        @submit-report="submitReport"
    />

    <TabContainer id="tips-tab" :channel="channel" v-if="channel?.tips">
        <MissionsSection :missions="channel.tips.missions" @report-mission="openMissionReportDialog" />

        <AnnouncementSection :announcement="channel.tips.announcement" @report-announcement="openAnnouncementReportDialog" />

        <ObjectivesSection
            :channel-name="channel.name"
            :team-objectives="channel.tips.teamObjectives"
            :character-objectives="channel.tips.characterObjectives"
            :external-resources="channel.tips.externalResources"
            :is-mush="player.isMush"
        />
    </TabContainer>
</template>

<script setup lang="ts">
import TabContainer from "@/components/Game/Communications/TabContainer.vue";
import MissionsSection from "@/components/Game/Communications/TipsTab/MissionsSection.vue";
import AnnouncementSection from "@/components/Game/Communications/TipsTab/AnnouncementSection.vue";
import ObjectivesSection from "@/components/Game/Communications/TipsTab/ObjectivesSection.vue";
import ReportPopup from "@/components/Moderation/ReportPopup.vue";
import { CommanderMission, ComManagerAnnouncementElement } from "@/entities/Channel";
import { computed } from "vue";
import { useStore } from "vuex";
import { useReportHandlers } from "@/utils/moderation/useReportHandlers";

const store = useStore();

const channel = computed(() => store.getters['communication/currentChannel']);
const player = computed(() => store.getters['player/player']);

const { visible: reportPopupVisible, open: openReportDialogWith, close: closeReportDialog, submit: submitReport } = useReportHandlers();

const openReportDialogForContent = (
    action: string,
    idParams: Record<string, number>,
    playerId: number
): void => {
    openReportDialogWith(async (params) => {
        params.set('player', String(playerId));
        await store.dispatch(action, { ...idParams, params });
    });
};

const openMissionReportDialog = (mission: CommanderMission): void => {
    openReportDialogForContent(
        'moderation/reportCommanderMission',
        { missionId: mission.id },
        mission.commander.id
    );
};

const openAnnouncementReportDialog = (announcement: ComManagerAnnouncementElement): void => {
    openReportDialogForContent(
        'moderation/reportComManagerAnnouncement',
        { announcementId: announcement.id },
        announcement.comManager.id
    );
};
</script>

<style lang="scss" scoped>
#tips-tab {
    .unit {
        padding: 1px 0 !important;
    }
}
</style>
