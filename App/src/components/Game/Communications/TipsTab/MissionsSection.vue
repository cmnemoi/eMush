<template>
    <section class="unit" v-if="missions.elements.length > 0">
        <div class="banner">
            <span><img :src="getImgUrl('comms/notebook.png')"> {{ missions.title }} <img :src="getImgUrl('comms/notebook.png')"></span>
        </div>
        <div
            class="mission"
            v-for="mission in missions.elements"
            :key="mission.commander.key"
            @mouseover="markMissionAsRead(mission)">
            <div :class="['message', { new: mission.isUnread }]">
                <div class="char-portrait">
                    <img :src="getImgUrl(`char/body/${mission.commander.key}.png`)">
                </div>
                <p>
                    <span class="author">{{ mission.commander.name }} :</span>
                    <span class="mission-text" v-html="formatText(mission.mission)"></span>
                    <span class="timestamp">{{ mission.date }}</span>
                    <ReportButton class="report-button" @click="$emit('reportMission', mission)" />
                </p>
            </div>
            <span class="mission-actions" v-if="mission.isPending && acceptMissionAction && rejectMissionAction">
                {{ missions.buttons.accept }}
                <a v-html="formatText(acceptMissionAction.name)" @click="acceptMission(mission.id)" /> /
                <a v-html="formatText(rejectMissionAction.name)" @click="rejectMission(mission.id)" />
            </span>
            <span class="mission-completion" v-else>
                <img
                    :src="getImgUrl('comms/check.png')"
                    alt="Completed"
                    v-if="mission.isCompleted"
                    @click="toggleMissionCompletion(mission)"/>
                <img
                    :src="getImgUrl('comms/uncheck.png')"
                    alt="Pending"
                    v-else
                    @click="toggleMissionCompletion(mission)"/>
            </span>
        </div>
    </section>
</template>

<script setup lang="ts">
import { Missions, CommanderMission } from "@/entities/Channel";
import { computed } from "vue";
import { useStore } from "vuex";
import { getImgUrl } from "@/utils/getImgUrl";
import { formatText } from "@/utils/formatText";
import { ActionEnum } from "@/enums/action.enum";
import { Action } from "@/entities/Action";
import ReportButton from "@/components/Moderation/ReportButton.vue";

defineProps<{ missions: Missions }>();

defineEmits<{
    reportMission: [mission: CommanderMission]
}>();

const store = useStore();

const player = computed(() => store.getters['player/player']);
const acceptMissionAction = computed((): Action | undefined => {
    return player.value.getActionByKey(ActionEnum.ACCEPT_MISSION);
});
const rejectMissionAction = computed((): Action | undefined => {
    return player.value.getActionByKey(ActionEnum.REJECT_MISSION);
});

const acceptMission = async (missionId: number): Promise<void> => {
    await store.dispatch('action/executeAction', { target: null, action: acceptMissionAction.value, params: { missionId } });
};
const rejectMission = async (missionId: number): Promise<void> => {
    await store.dispatch('action/executeAction', { target: null, action: rejectMissionAction.value, params: { missionId } });
};
const toggleMissionCompletion = async (mission: CommanderMission): Promise<void> => {
    await store.dispatch('player/toggleMissionCompletion', { mission });
};
const markMissionAsRead = async (mission: CommanderMission): Promise<void> => {
    if (!mission.isUnread) {
        return;
    }

    await store.dispatch('communication/acquireReadMessageMutex');

    try {
        if (mission.isUnread) {
            await store.dispatch('player/markMissionAsRead', { mission });
        }
    } finally {
        await store.dispatch('communication/releaseReadMessageMutex');
    }
};
</script>

<style lang="scss" scoped>
.message {
    @extend %comm-message-bubble;
}

#tips-tab a { color: $deepGreen; }

.mission-actions {
    a {
        color: $mushRed;
        text-decoration: underline;
        cursor: pointer;
    }
}

.mission-completion {
    display: flex;
    justify-content: right;
}

.report-button {
    float: right;
}
</style>
