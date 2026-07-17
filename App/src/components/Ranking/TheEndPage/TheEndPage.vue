<template>
    <ModerationActionPopup
        :moderation-dialog-visible="moderationDialogVisible"
        :action="currentAction"
        @close="closeModerationDialog"
        @submit-sanction="applySanction"
    />

    <ReportPopup
        :report-dialog-visible="reportPopupVisible"
        :select-player="false"
        @close=closeReportDialog
        @submit-report=submitReport
    />

    <PlayerHistoryPopup/>

    <div class="container" v-if="closedDaedalus">
        <div class="ending-screen">
            <img
                :src="getEndCauseConfig(closedDaedalus.endCause).img"
                :alt="$t(getEndCauseConfig(closedDaedalus.endCause).short_name)">
        </div>

        <div class="cheater-banner" v-if="closedDaedalus?.isCheater">
            <img :src="getImgUrl('ui_icons/noob.png')" alt="Cheater">
            {{ $t("theEnd.cheaterMessage") }}
        </div>

        <template v-if="players.length > 0">
            <h2>{{ $t("theEnd.headliner") }}</h2>
            <GoldPlayer
                :player="players[0]"
                @open-hide-dialog="openHideDialog"
                @open-edit-dialog="openEditDialog"
                @open-report-dialog="openReportDialog"
                @show-history="showPlayerDetailedHistory"
            />
        </template>

        <template v-if="players.length > 1">
            <h2>{{ $t('theEnd.mainRoles') }}</h2>
            <div class="section">
                <MainPlayer
                    v-for="(player, index) in players.slice(1, 7)"
                    :key="index"
                    :player="player"
                    :index="index"
                    @open-hide-dialog="openHideDialog"
                    @open-edit-dialog="openEditDialog"
                    @open-report-dialog="openReportDialog"
                    @show-history="showPlayerDetailedHistory"
                />
            </div>
        </template>

        <template v-if="players.length > 7">
            <h2>{{ $t('theEnd.extraPlayers') }}</h2>
            <div class="section">
                <ExtraPlayer
                    v-for="(player, key) in players.slice(7)"
                    :key="key"
                    :player="player"
                    @open-hide-dialog="openHideDialog"
                    @open-edit-dialog="openEditDialog"
                    @open-report-dialog="openReportDialog"
                    @show-history="showPlayerDetailedHistory"
                />
            </div>
        </template>

        <h2>{{ closedDaedalus.statistics.title }}</h2>
        <div class="ship">
            <table class="stats-table">
                <thead>
                    <tr>
                        <th v-for="statistic in closedDaedalus.statistics.lines" :key="statistic.name">
                            {{ statistic.name }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td v-for="statistic in closedDaedalus.statistics.lines" :key="statistic.name">
                            {{ statistic.value }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="progress">
                <div v-for="projectType in projectTypes" :key="projectType">
                    <p>{{ closedDaedalus.projects[projectType].title }}</p>
                    <div class="wrappedContent">
                        <DaedalusProjectCard
                            v-for="projectLine in closedDaedalus.projects[projectType].lines"
                            :key="projectLine.key"
                            :project="projectLine"
                            :display-project-type="false"
                        />
                    </div>
                </div>
            </div>

            <h2>{{ $t('theEnd.explorations') }}</h2>
            <div class="explorations">
                <table class="explorations-table">
                    <thead>
                        <tr>
                            <th class="no-wrap"><img :src="getImgUrl('comms/calendar.png')" alt="calendar" /></th>
                            <th class="no-wrap"><img :src="getImgUrl('ui_icons/planet.png')" alt="planet" /></th>
                            <th><img :src="getImgUrl('in_game.png')" alt="crew" /></th>
                            <th><img :src="getImgUrl('astro/unknown.png')" alt="sectors" /></th>
                            <th class="no-wrap"><img :src="getImgUrl('notes.gif')" alt="logs" /></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(exploration, i) in closedDaedalus.explorations" :key="i">
                            <td class="no-wrap">
                                <span class="normal">
                                    {{ $t('game.communications.day') }} {{ exploration.startDay ? exploration.startDay : "?" }}
                                    -
                                    {{ $t('game.communications.cycle') }} {{ exploration.startCycle ? exploration.startCycle : "?" }}
                                </span>
                                <span class="shrink">{{ exploration.startDay ? exploration.startDay : "?" }}-{{ exploration.startCycle ? exploration.startCycle : "?" }}</span>
                            </td>
                            <td class="no-wrap"  v-html="formatText(`**${exploration.planet}**`)"/>
                            <td>
                                <img
                                    v-for="(explorator, j) in exploration.explorators"
                                    :key="j"
                                    :src="getImgUrl(`char/body/${explorator.logName}.png`)"
                                    :alt="explorator.logName"
                                    class="normal"
                                />
                                <img
                                    v-for="(explorator, j) in exploration.explorators"
                                    :key="j"
                                    :src="getImgUrl(`char/head/${explorator.logName}.png`)"
                                    :alt="explorator.logName"
                                    class="shrink"
                                />
                            </td>
                            <td>
                                <img
                                    class="sector"
                                    v-for="(sector, j) in exploration.sectors"
                                    :key="j"
                                    :src="getImgUrl(`astro/${sector}.png`)"
                                    :alt="sector"
                                />
                            </td>
                            <td class="no-wrap">
                                <router-link class="logs" :to="{ name: 'ClosedExpeditionPanel', params: { uuid: exploration.uuid } }">
                                    <img :src="getImgUrl('ui_icons/right.png')" :alt="$t('theEnd.explorations.logs')" />
                                </router-link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <h2>{{ $t('theEnd.titles') }}</h2>
            <div class="titles">
                <div v-for="(titleHolder) in closedDaedalus.titleHolders" :key="titleHolder.title">
                    <p>{{ titleHolder.title }}</p>
                    <div class="wrappedContent">
                        <ul>
                            <CharacterSignature
                                v-for="characterKey in titleHolder.characterKeys"
                                :key="characterKey"
                                :character-key="characterKey"
                            />
                        </ul>
                    </div>
                </div>
            </div>

            <h2>{{ $t('theEnd.funFacts') }}</h2>
            <div class="honors" v-if="closedDaedalus.funFacts.length > 0">
                <div v-for="(funFact, name) in closedDaedalus.funFacts" :key="name">
                    <p>
                        <Tippy>
                            {{ funFact.title }}
                            <template #content>
                                <h1>{{ funFact.title }}</h1>
                                <p>{{ funFact.description }}</p>
                            </template>
                        </Tippy>
                    </p>
                    <ul>
                        <CharacterSignature :character-key="funFact.characterKey"/>
                    </ul>
                </div>
            </div>
        </div>
        <router-link class="back" :to="{}">
            <span @click="$router.go(-1)">{{ $t('util.goBack') }}</span>
        </router-link>
    </div>
</template>

<script setup lang="ts">
import urlJoin from "url-join";
import { onBeforeMount, ref } from "vue";
import { useRoute } from "vue-router";
import { useStore } from "vuex";
import { handleErrors } from "@/utils/apiValidationErrors";
import { ClosedDaedalus } from "@/entities/ClosedDaedalus";
import { ClosedPlayer } from "@/entities/ClosedPlayer";
import ApiService from "@/services/api.service";
import { AxiosError } from "axios";
import DaedalusService from "@/services/daedalus.service";
import ModerationService from "@/services/moderation.service";
import ModerationActionPopup from "@/components/Moderation/ModerationActionPopup.vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { useReportHandlers } from "@/utils/moderation/useReportHandlers";
import ReportPopup from "@/components/Moderation/ReportPopup.vue";
import PlayerHistoryPopup from "@/components/Ranking/PlayerHistoryPopup.vue";
import DaedalusProjectCard from "@/components/Game/DaedalusProjectCard.vue";
import CharacterSignature from "@/components/Game/CharacterSignature.vue";
import { getEndCauseConfig } from "@/enums/endcause.enum";
import GoldPlayer from "@/components/Ranking/TheEndPage/GoldPlayer.vue";
import MainPlayer from "@/components/Ranking/TheEndPage/MainPlayer.vue";
import ExtraPlayer from "@/components/Ranking/TheEndPage/ExtraPlayer.vue";
import { formatText } from "@/utils/formatText";

const route = useRoute();
const store = useStore();

const closedDaedalus = ref<ClosedDaedalus | null>(null);
const players = ref<ClosedPlayer[]>([]);
const moderationDialogVisible = ref(false);
const currentAction = ref<{ key: string, value: string }>({ key: "", value: "" });
const currentPlayer = ref<ClosedPlayer | null>(null);
const projectTypes = ['researchProjects', 'neronProjects', 'pilgredProjects'] as const;
const errors = ref<{[key: string]: string[]}>({});
function showPlayerDetailedHistory(player: ClosedPlayer) {
    store.dispatch('popup/openPlayerHistoryPopUp', {
        playerName: player.getCharacterCompleteName(),
        gains: player.triumphGains,
        highlights: player.highlights
    });
}

// Report
const { visible: reportPopupVisible, open: openReportDialogWith, close: closeReportDialog, submit: submitReport } = useReportHandlers();
function openReportDialog(player: ClosedPlayer) {
    window.scrollTo({ top: 0 });
    openReportDialogWith(async (params) => {
        await store.dispatch('moderation/reportClosedPlayer', { closedPlayerId: player.id, params });
    });
}

// Moderation
function openModerationDialog(action: { key: string, value: string }, player: ClosedPlayer) {
    currentAction.value = action;
    currentPlayer.value = player;
    moderationDialogVisible.value = true;
}
function openEditDialog(player: ClosedPlayer) {
    openModerationDialog({ key: 'moderation.sanction.delete_end_message', value: 'delete_end_message' }, player);
}
function openHideDialog(player: ClosedPlayer) {
    openModerationDialog({ key: 'moderation.sanction.hide_end_message', value: 'hide_end_message' }, player);
}
function closeModerationDialog() {
    moderationDialogVisible.value = false;
}
async function applySanction(params: URLSearchParams) {
    if (currentPlayer.value === null || currentPlayer.value.id === null) return;

    if (currentAction.value.value === 'hide_end_message') {
        await ModerationService.hideClosedPlayerEndMessage(currentPlayer.value.id, params);
    } else if (currentAction.value.value === 'delete_end_message') {
        await ModerationService.editClosedPlayerEndMessage(currentPlayer.value.id, params);
    }
    moderationDialogVisible.value = false;
    await loadData();
}

// Data fetching
async function loadData() {
    const closedDaedalusId = String(route.params.closedDaedalusId);

    try {
        closedDaedalus.value = await DaedalusService.loadClosedDaedalus(Number(closedDaedalusId));
        const result = await ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'closed_daedaluses', closedDaedalusId, 'players'));
        players.value = result.data['hydra:member'].map((datum: object) => new ClosedPlayer().load(datum));
        players.value.sort((a: ClosedPlayer, b: ClosedPlayer) => (b.score ?? 0) - (a.score ?? 0));
    } catch (error) {
        const axiosError = error as AxiosError<{ violations: { propertyPath: string, message: string }[] }>;
        if (axiosError.response?.data?.violations) {
            errors.value = handleErrors(axiosError.response.data.violations);
        }
    }
}
onBeforeMount(() => {
    loadData();
});
</script>

<style lang="scss" scoped>

.container {
    max-width: 785px;
    width: 100%;
    margin: 0 auto;
    align-items: center;
}

p {
    margin: 0;
}

.cheater-banner {
    margin: 10px 10px 20px 10px;
    padding: 10px 15px;
    border: 1px solid $red;
    background-color: #222b6b;
    font-size: 14px;
    text-align: center;
    border-radius: 3px;
    flex-direction: row;
}

.ending-screen {
    width: 100%;
    overflow: hidden;
    align-items: center;
}

h2 {
    font-size: 1rem;
    font-weight: normal;
    text-transform: uppercase;
    text-align: center;
}

.section {
    flex-flow: row wrap;
    justify-content: center;
    max-width: 785px;
    align-self: center;
}

.ship {
    align-self: stretch;
    margin: 0 0.5rem;
}

.stats-table {
    width: 100%;
    border-collapse: collapse;
    text-align: center;
    background-color: #222b6b;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 1em;

    th, td {
        padding: 0.75em;
        border-bottom: 1px dotted #0f0f43;
        font-weight: bold;
    }

    th {
        opacity: 0.6;
        font-size: 0.9em;
    }

    td {
        font-size: 1.4rem;
    }
}

.explorations-table {
    width: 100%;
    border-collapse: collapse;
    background-color: #222b6b;
    border-radius: 3px;
    overflow: hidden;

    th, td {
        padding: 0.75em 0.5em;
        border-bottom: 1px dotted #0f0f43;
        text-align: center;
        font-size:0.8em;

        &.no-wrap {
            white-space: nowrap;
            width: 1%;
        }
    }

    th:nth-child(4) > img {
        max-height: 1.7em;
    }

    span {
        display: inline-flex;
        gap: 3px;
        white-space: nowrap;
    }

    .logs {
        @include button-style();
        padding: 2px 2px 0;
    }

    .normal { display: initial; }
    .shrink { display: none; }
    .sector { max-height: 2.8em; }

    @media only screen and (max-width: $breakpoint-desktop-s) {
        .sector { max-height: 1.7em; }
        .normal { display: none; }
        .shrink { display: initial; }
    }
}

.progress, .titles, .honors, .explorations {
    margin-bottom: 15px;

    & > div {
        flex-direction: row;
        background-color: #222b6b;
        padding: 0.5em;

        &:not(:last-child) {
            border-bottom: 1px dotted #0f0f43;
        }

        p {
            width: 20%;
            min-width: 20%;
            max-width: 156px;
            margin: auto 0;
            padding: .8em;
            opacity: .6;
            font-size: .9em;
            font-weight: bold;
            text-align: center;
        }

        .wrappedContent {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
        }

        li {
            margin: .1em .6em;
            display: flex;
            align-items: center;

            img {
                margin-right: 0.4em;
            }
        }
    }
}

.progress div li, img {
    margin: .1em;
}

a.back {
    @include button-style;

    & {
        width: 85%;
        max-width: 300px;
        margin: auto;
        margin-top: 1em;
    }
}

@media only screen and (max-width: $breakpoint-mobile-l) {

    .stats {
        flex-direction: column;

        div p:first-child {
            border-bottom: none;
        }

        div:not(:last-child) {
            margin-bottom: .6em;
            border-bottom: 1px solid #0f0f43;
        }
    }

    .progress, .titles, .honors {
        & > div {
            flex-direction: column;

            p {
                width: 100%;
                max-width: none;
                padding-bottom: 0;
                text-align: left;
            }
        }
    }
}

</style>
