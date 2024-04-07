<template>
    <ModerationActionPopup
        :moderation-dialog-visible="moderationDialogVisible"
        :action="currentAction"
        @close="closeModerationDialog"
        @submitSanction="applySanction" />
    <div class="container" v-if="closedDaedalus">
        <div class="ending-screen">
            <img :src="getImgUrl('ending-destroyed.png')" alt="destroyed!" >
        </div>
        <h2>{{ $t("theEnd.headliner") }}</h2>
        <div class="card star-card" v-if="goldNovaPlayer">
            <div>
                <img class="avatar" :src="getPlayerCharacterPortrait(goldNovaPlayer)" alt="Janice">
                <div class="dude">
                    <img class="body" :src="getPlayerCharacterBody(goldNovaPlayer)" alt="Janice">
                    <div>
                        <h3 class="char-name">
                            {{ getPlayerCharacterCompleteName(goldNovaPlayer) }}
                        </h3>
                        <p>
                            <router-link class="pseudo" :to="{ name: 'TheEndUserPage', params: {userId: goldNovaPlayer.userId}}">
                                {{ goldNovaPlayer.username }}
                            </router-link>
                            <span class="likes">
                                {{ goldNovaPlayer.likes }} <img :src="getImgUrl('dislike.png')">
                            </span>
                        </p>
                    </div>
                </div>
                <p class="epitaph" v-if="goldNovaPlayer.message">
                    <Tippy
                        tag="span"
                        :class="['message', {'hidden' : goldNovaPlayer.messageIsHidden && isModerator}]"
                        v-if="goldNovaPlayer.messageIsHidden"
                    >
                        <span v-html="formatEndMessage(goldNovaPlayer.message)" />
                        <template #content>
                            <h1>{{ $t('moderation.theEndPage.messageIsHidden')}}</h1>
                            <p>{{ $t('moderation.theEndPage.messageIsHiddenDescription') }}</p>
                        </template>
                    </Tippy>
                    <span v-html="formatEndMessage(goldNovaPlayer.message)" v-else />
                    <Tippy tag="span" v-if="isModerator && !goldNovaPlayer.messageHasBeenModerated" @click="openHideDialog(goldNovaPlayer)">
                        <img :src="getImgUrl('comms/discrete.png')" alt="Hide message">
                        <template #content>
                            <h1>{{ $t('moderation.theEndPage.hideMessage')}}</h1>
                            <p>{{ $t('moderation.theEndPage.hideMessageDescription') }}</p>
                        </template>
                    </Tippy>
                    <Tippy tag="span" v-if="isModerator && !goldNovaPlayer.messageHasBeenModerated" @click="openEditDialog(goldNovaPlayer)">
                        <img :src="getImgUrl('pa_core.png')" alt="Edit message">
                        <template #content>
                            <h1>{{ $t('moderation.theEndPage.editMessage')}}</h1>
                            <p>{{ $t('moderation.theEndPage.editMessageDescription') }}</p>
                        </template>
                    </Tippy>
                    <Tippy tag="span" v-if="!goldNovaPlayer.messageHasBeenModerated" @click="openReportPopup()">
                        <img :src="getImgUrl('comms/alert.png')" alt="Edit message">
                        <template #content>
                            <h1>{{ $t('moderation.report')}}</h1>
                            <p>{{ $t('moderation.reportDescription') }}</p>
                        </template>
                    </Tippy>
                </p>
                <div class="triumph">
                    <p class="score mush" v-if="goldNovaPlayer.isMush">
                        {{ goldNovaPlayer.cyclesSurvived }}
                    </p>
                    <p class="score" v-else>
                        {{ goldNovaPlayer.cyclesSurvived }}
                    </p>
                    <p class="death-cause">
                        <img :src="getImgUrl('dead.png')" alt="Dead">
                        {{ $t("theEnd.endCause." + goldNovaPlayer.endCause) }}
                    </p>
                    <p class="nova">
                        <img :src="getImgUrl('nova/first.png')" alt="First"> {{ $t('theEnd.goldSuperNova') }}
                    </p>
                    <!-- <ul>
                        <li>Vous avez éteint un incendie !</li>
                        <li>Vous avez découvert une nouvelle planète !</li>
                        <li>Vous avez pris une douche bien méritée !</li>
                    </ul> -->
                    <!-- <button>
                        <img :src="getImgUrl('notes.gif" alt="Historique et Triomphe">
                    </button> -->
                </div>
            </div>
        </div>

        <h2 v-if="mainRolesPlayers?.length > 0">{{ $t('theEnd.mainRoles') }}</h2>
        <div class="guests" v-if="mainRolesPlayers?.length > 0">
            <div
                v-for="(player, key) in mainRolesPlayers"
                :key="key"
                class="card guest-card">
                <div>
                    <img class="avatar" :src="getPlayerCharacterPortrait(player)" :alt="getPlayerCharacterCompleteName(player)">
                    <div class="dude">
                        <img class="body" :src="getPlayerCharacterBody(player)" :alt="getPlayerCharacterCompleteName(player)">
                        <div>
                            <h3 class="char-name">
                                {{ getPlayerCharacterCompleteName(player) }}
                            </h3>
                            <p>
                                <router-link class="pseudo" :to="{ name: 'TheEndUserPage', params: {userId: player.userId}}">
                                    {{ player.username }}
                                </router-link>
                                <span class="likes">
                                    {{ player.likes }} <img :src="getImgUrl('dislike.png')" alt="likes">
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="triumph">
                        <p class="score mush" v-if="player.isMush">
                            {{ player.cyclesSurvived }}
                        </p>
                        <p class="score" v-else>
                            {{ player.cyclesSurvived }}
                        </p>
                        <p class="death-cause">
                            <img :src="getImgUrl('dead.png')" alt="Dead">
                            {{ $t('theEnd.endCause.' + player.endCause) }}
                        </p>
                        <p class="nova" v-if="key === 0">
                            <img :src="getImgUrl('nova/second.png')" alt="Second"> {{ $t('theEnd.silverSuperNova') }}
                        </p>
                        <p class="nova" v-else-if="key === 1">
                            <img :src="getImgUrl('nova/third.png')" alt="Third"> {{ $t('theEnd.bronzeSuperNova') }}
                        </p>
                        <p class="nova" v-else-if="key === 2">
                            <img :src="getImgUrl('nova/fourth.png')" alt="Fourth"> {{ $t('theEnd.discoveredSuperNova') }}
                        </p>
                        <p class="nova" v-else-if="key >= 3">
                            <img :src="getImgUrl('nova/fifth.png')" alt="fifth"> {{ $t('theEnd.specialSuperNova') }}
                        </p>
                        <p class="epitaph" v-if="player.message">
                            <Tippy
                                tag="span"
                                :class="['message', {'hidden' : player.messageIsHidden && isModerator}]"
                                v-if="player.messageIsHidden"
                            >
                                <span v-html="formatEndMessage(player.message)" />
                                <template #content>
                                    <h1>{{ $t('moderation.theEndPage.messageIsHidden')}}</h1>
                                    <p>{{ $t('moderation.theEndPage.messageIsHiddenDescription') }}</p>
                                </template>
                            </Tippy>
                            <span v-html="formatEndMessage(player.message)" v-else />
                            <Tippy tag="span" v-if="isModerator && !player.messageHasBeenModerated" @click="openHideDialog(player)">
                                <img :src="getImgUrl('comms/discrete.png')" alt="Hide message">
                                <template #content>
                                    <h1>{{ $t('moderation.theEndPage.hideMessage')}}</h1>
                                    <p>{{ $t('moderation.theEndPage.hideMessageDescription') }}</p>
                                </template>
                            </Tippy>
                            <Tippy tag="span" v-if="isModerator && !player.messageHasBeenModerated" @click="openEditDialog(player)">
                                <img :src="getImgUrl('pa_core.png')" alt="Edit message">
                                <template #content>
                                    <h1>{{ $t('moderation.theEndPage.editMessage')}}</h1>
                                    <p>{{ $t('moderation.theEndPage.editMessageDescription') }}</p>
                                </template>
                            </Tippy>
                            <Tippy tag="span" v-if="!goldNovaPlayer.messageHasBeenModerated" @click="openReportPopup()">
                                <img :src="getImgUrl('comms/alert.png')" alt="Edit message">
                                <template #content>
                                    <h1>{{ $t('moderation.report')}}</h1>
                                    <p>{{ $t('moderation.reportDescription') }}</p>
                                </template>
                            </Tippy>
                        </p>
                        <!-- <ul>
                            <li>Vous avez éteint un incendie !</li>
                        </ul>
                        <button>
                            <img :src="getImgUrl('notes.gif" alt="Historique et Triomphe">
                        </button> -->
                    </div>
                </div>
            </div>
        </div>

        <h2 v-if="figurantPlayers?.length > 0">{{ $t('theEnd.figurantPlayers')}}</h2>
        <div class="extras" v-if="figurantPlayers?.length > 0">
            <div
                v-for="(player, key) in
                    figurantPlayers"
                :key="key"
                class="card extra-card">
                <div>
                    <!-- <img class="avatar" :src="getPlayerCharacterPortrait(player)" :alt="getPlayerCharacterCompleteName(player)"> -->
                    <div class="dude">
                        <img class="body" :src="getPlayerCharacterBody(player)" :alt="getPlayerCharacterCompleteName(player)">
                        <div>
                            <h3 class="char-name">
                                {{ getPlayerCharacterCompleteName(player) }}
                            </h3>
                            <p>
                                <router-link class="pseudo" :to="{ name: 'TheEndUserPage', params: {userId: player.userId}}">
                                    {{ player.username }}
                                </router-link>
                                <span class="likes">
                                    {{ player.likes }} <img :src="getImgUrl('dislike.png')" alt="likes">
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="triumph">
                        <p class="score mush" v-if="player.isMush">
                            {{ player.cyclesSurvived }}
                        </p>
                        <p class="score" v-else>
                            {{ player.cyclesSurvived }}
                        </p>
                        <p class="death-cause">
                            <img :src="getImgUrl('dead.png')" alt="Dead">
                            {{ $t('theEnd.endCause.' + player.endCause) }}
                        </p>
                        <p class="nova">
                            <img :src="getImgUrl('nova/sixth.png')" alt="sixth"> {{ $t('theEnd.normalSuperNova') }}
                        </p>
                        <p class="epitaph" v-if="player.message">
                            <Tippy
                                tag="span"
                                :class="['message', {'hidden' : player.messageIsHidden && isModerator}]"
                                v-if="player.messageIsHidden"
                            >
                                <span v-html="formatEndMessage(player.message)" />
                                <template #content>
                                    <h1>{{ $t('moderation.theEndPage.messageIsHidden')}}</h1>
                                    <p>{{ $t('moderation.theEndPage.messageIsHiddenDescription') }}</p>
                                </template>
                            </Tippy>
                            <span v-html="formatEndMessage(player.message)" v-else />
                            <Tippy tag="span" v-if="isModerator && !player.messageHasBeenModerated" @click="openHideDialog(player)">
                                <img :src="getImgUrl('comms/discrete.png')" alt="Hide message">
                                <template #content>
                                    <h1>{{ $t('moderation.theEndPage.hideMessage')}}</h1>
                                    <p>{{ $t('moderation.theEndPage.hideMessageDescription') }}</p>
                                </template>
                            </Tippy>
                            <Tippy tag="span" v-if="isModerator && !player.messageHasBeenModerated" @click="openEditDialog(player)">
                                <img :src="getImgUrl('pa_core.png')" alt="Edit message">
                                <template #content>
                                    <h1>{{ $t('moderation.theEndPage.editMessage')}}</h1>
                                    <p>{{ $t('moderation.theEndPage.editMessageDescription') }}</p>
                                </template>
                            </Tippy>
                            <Tippy tag="span" v-if="!goldNovaPlayer?.messageHasBeenModerated" @click="openReportPopup()">
                                <img :src="getImgUrl('comms/alert.png')" alt="Edit message">
                                <template #content>
                                    <h1>{{ $t('moderation.report')}}</h1>
                                    <p>{{ $t('moderation.reportDescription') }}</p>
                                </template>
                            </Tippy>
                        </p>
                        <!-- <ul>
                            <li>Vous avez éteint un incendie !</li>
                        </ul>
                        <button>
                            <img :src="getImgUrl('notes.gif" alt="Historique et Triomphe">
                        </button> -->
                    </div>
                </div>
            </div>
        </div>

        <h2>Daedalus :</h2>
        <div class="ship">
            <div class="stats">
                <!-- <div>
                    <p>Planètes trouvées</p>
                    <p>36</p>
                </div>
                <div>
                    <p>Explorations</p>
                    <p>18</p>
                </div>
                <div>
                    <p>Spores générés</p>
                    <p>0</p>
                </div> -->
                <div>
                    <p>{{ $t("theEnd.numberOfHuntersKilled")  }}</p>
                    <p>{{ closedDaedalus.numberOfHuntersKilled }}</p>
                </div>
                <div>
                    <p>{{ $t("theEnd.amountOfMushPlayers") }}</p>
                    <p>{{ getAmountOfMushPlayers() }}</p>
                </div>
            </div>

            <!-- <div class="progress">
                <div>
                    <p>Recherches</p>
                    <ul>
                        <li><img :src="getImgUrl('researches/mycoscan.png')" alt="Mycoscan"></li>
                        <li><img :src="getImgUrl('researches/pheromodem.png')" alt="Pheromodem"></li>
                    </ul>
                </div>
                <div>
                    <p>Projets NERON</p>
                    <ul>
                        <li><img :src="getImgUrl('projects/dismantling.png')" alt="Dismantling"></li>
                        <li><img :src="getImgUrl('projects/trash_load.png')" alt="Trash load"></li>
                    </ul>
                </div>
                <div>
                    <p>PILGRED</p>
                    <ul>
                        <li><img :src="getImgUrl('projects/pilgred.png')" alt="Pilgred"></li>
                    </ul>
                </div>
                <div>
                    <p>Bases rebelles</p>
                    <ul>
                        <li><img :src="getImgUrl('rebel_bases/bases_centauri.png')" alt="Centauri"></li>
                        <li><img :src="getImgUrl('rebel_bases/bases_cygni.png')" alt="Cygni"></li>
                    </ul>
                </div>
            </div>
            <div class="roles">
                <div>
                    <p>Les Commandants</p>
                    <ul>
                        <li><img :src="getImgUrl('char/body/jin_su.png')" alt="Jin Su"> Jin Su</li>
                        <li><img :src="getImgUrl('char/body/frieda.png')" alt="Frieda"> Frieda</li>
                    </ul>
                </div>
                <div>
                    <p>Les Responsables des Communications</p>
                    <ul>
                        <li><img :src="getImgUrl('char/body/jin_su.png')" alt="Jin Su"> Jin Su</li>
                        <li><img :src="getImgUrl('char/body/frieda.png')" alt="Frieda"> Frieda</li>
                    </ul>
                </div>
                <div>
                    <p>L'Administrateur NERON</p>
                    <ul>
                        <li><img :src="getImgUrl('char/body/jin_su.png')" alt="Jin Su"> Jin Su</li>
                        <li><img :src="getImgUrl('char/body/frieda.png')" alt="Frieda"> Frieda</li>
                    </ul>
                </div>
            </div>
            <div class="honors">
                <div>
                    <p>Bidouilleur Anonymous</p>
                    <ul>
                        <li><img :src="getImgUrl('char/body/terrence.png')" alt="Terrence"> Terrence</li>
                    </ul>
                </div>
                <div>
                    <p>« J'aurais pas dû venir »</p>
                    <ul>
                        <li><img :src="getImgUrl('char/body/terrence.png')" alt="Terrence"> Terrence</li>
                    </ul>
                </div>
                <div>
                    <p>La violence c'est le mal</p>
                    <ul>
                        <li><img :src="getImgUrl('char/body/terrence.png')" alt="Terrence"> Terrence</li>
                    </ul>
                </div>
                <div>
                    <p>Quand l'appétit va...</p>
                    <ul>
                        <li><img :src="getImgUrl('char/body/terrence.png')" alt="Terrence"> Terrence</li>
                    </ul>
                </div>
                <div>
                    <p>Dilapidateur d'énergie</p>
                    <ul>
                        <li><img :src="getImgUrl('char/body/terrence.png')" alt="Terrence"> Terrence</li>
                    </ul>
                </div>
            </div> -->
        </div>
        <router-link class="back" :to="{}">
            <span @click="$router.go(-1)">{{ $t('util.goBack') }}</span>
        </router-link>
    </div>
</template>

<script lang="ts">
import urlJoin from "url-join";

import { characterEnum } from "@/enums/character";
import { defineComponent } from "vue";
import { handleErrors } from "@/utils/apiValidationErrors";
import { ClosedDaedalus } from "@/entities/ClosedDaedalus";
import { ClosedPlayer } from "@/entities/ClosedPlayer";
import ApiService from "@/services/api.service";
import DaedalusService from "@/services/daedalus.service";
import ModerationService from "@/services/moderation.service";
import { mapActions, mapGetters } from "vuex";
import { formatText } from "@/utils/formatText";
import ModerationActionPopup from "@/components/Moderation/ModerationActionPopup.vue";
import { getImgUrl } from "@/utils/getImgUrl";

interface ClosedDaedalusState {
    closedDaedalus: ClosedDaedalus|null
    errors: any
    goldNovaPlayer: ClosedPlayer|null,
    mainRolesPlayers: ClosedPlayer[]|null,
    figurantPlayers: ClosedPlayer[]|null,
    moderationDialogVisible: boolean,
    currentAction: { key: string, value: string },
    currentPlayer: ClosedPlayer|null,
}

export default defineComponent ({
    name: 'TheEnd',
    components: { ModerationActionPopup },
    computed: {
        ...mapGetters({
            isModerator: 'auth/isModerator',
            reportPopup: 'popup/reportPopup'
        })
    },
    data: function (): ClosedDaedalusState {
        return {
            closedDaedalus: null,
            errors: {},
            goldNovaPlayer: null,
            mainRolesPlayers: [],
            figurantPlayers: [],
            moderationDialogVisible: false,
            currentAction: { key: "", value: "" },
            currentPlayer: null
        };
    },
    emits: {
        report: null
    },
    methods: {
        ...mapActions('popup', [
            'openReportPopup'
        ]),
        getImgUrl,
        async loadData() {
            const closedDaedalusId = String(this.$route.params.closedDaedalusId);
            await DaedalusService.loadClosedDaedalus(Number(closedDaedalusId))
                .then((response: ClosedDaedalus | null) => {
                    this.closedDaedalus = response;
                    ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'closed_daedaluses', closedDaedalusId, 'players'))
                        .then((result) => {
                            const closedPlayers : ClosedPlayer[] = [];
                            result.data['hydra:member'].forEach((datum: any) => {
                                const currentClosedPlayer = (new ClosedPlayer()).load(datum);
                                closedPlayers.push(currentClosedPlayer);
                            });
                            if (this.closedDaedalus instanceof ClosedDaedalus) {
                                this.closedDaedalus.players = closedPlayers;
                            }
                            this.goldNovaPlayer = this.getNthPlayer(1);
                            this.mainRolesPlayers = this.getPlayersInRange(2, 7);
                            this.figurantPlayers = this.getPlayersInRange(8, 16);
                        });
                })
                .catch((error) => {
                    if (error.response) {
                        if (error.response.data.violations) {
                            this.errors = handleErrors(error.response.data.violations);
                        }
                    } else if (error.request) {
                        // The request was made but no response was received
                        console.error(error.request);
                    } else {
                        // Something happened in setting up the request that triggered an Error
                        console.error('Error', error.message);
                    }
                });
        },
        openEditDialog(player: ClosedPlayer) {
            this.currentAction = { key: 'moderation.sanction.delete_end_message', value: 'delete_end_message' };
            this.currentPlayer = player;
            this.moderationDialogVisible = true;
        },
        openHideDialog(player: ClosedPlayer) {
            this.currentAction = { key: 'moderation.sanction.hide_end_message', value: 'hide_end_message' };
            this.currentPlayer = player;
            this.moderationDialogVisible = true;
        },
        closeModerationDialog() {
            this.moderationDialogVisible = false;
        },
        async applySanction(params: any) {
            if (this.currentPlayer === null || this.currentPlayer.id === null) return;

            if (this.currentAction.value === 'hide_end_message') {
                await ModerationService.hideClosedPlayerEndMessage(this.currentPlayer.id, params);
            } else if (this.currentAction.value === 'delete_end_message') {
                await ModerationService.editClosedPlayerEndMessage(this.currentPlayer.id, params);
            }
            this.moderationDialogVisible = false;
            await this.loadData();
        },
        getAmountOfMushPlayers() {
            if (this.closedDaedalus && this.closedDaedalus.players) {
                return this.closedDaedalus.players.filter((player: ClosedPlayer) => player.isMush).length;
            }

            return 0;
        },
        getNthPlayer(n: number) {
            if (n < 1) return null;
            if (this.closedDaedalus && this.closedDaedalus.players) {
                return this.sortPlayersByCycleSurvived(this.closedDaedalus.players)[n-1];
            }

            return null;
        },
        getPlayerCharacterCompleteName(player: ClosedPlayer) {
            if (player.characterKey === null) return;
            return characterEnum[player.characterKey].completeName;
        },
        getPlayerCharacterBody(player: ClosedPlayer) {
            if (player.characterKey === null) return;
            return characterEnum[player.characterKey].body;
        },
        getPlayerCharacterPortrait(player: ClosedPlayer) {
            if (player.characterKey === null) return;
            return characterEnum[player.characterKey].portrait;
        },
        getPlayersInRange(start: number, end: number) {
            if (start < 1 || end > 16) return null;
            if (this.closedDaedalus && this.closedDaedalus.players) {
                return this.sortPlayersByCycleSurvived(this.closedDaedalus.players).slice(start - 1, end);
            }
            return null;
        },
        formatEndMessage(message: string) {
            return `« ${formatText(message)} »`;
        },
        sortPlayersByCycleSurvived(players: ClosedPlayer[], descending = true) {
            return players.sort((a, b) => {
                if (a.cyclesSurvived === null) return 1;
                if (b.cyclesSurvived === null) return -1;
                if (descending) {
                    return b.cyclesSurvived - a.cyclesSurvived;
                }
                return a.cyclesSurvived - b.cyclesSurvived;
            });
        }
    },
    beforeMount() {
        this.loadData();
    }
});
</script>

<style lang="scss" scoped>

.container {
    max-width: 785px;
    width: 100%;
    margin: 0 auto;
    align-items: center;
}

p { margin: 0; }

.ending-screen {
    width: 100%;
    overflow: hidden;
    align-items: center;
}

h2 {
    margin-top: 2em;
    font-size: 1rem;
    font-weight: normal;
    text-transform: uppercase;
    text-align: center;
}

.card {
    position: relative;
    z-index: 2;

    &::after { //background with fadeout
        content:"";
        position: absolute;
        width: 100%;
        height: 100%;
        z-index: -99;
    }

    & > div {
        padding: .6rem;
        flex: 1;
    }
}

.avatar {
    position: absolute;
    top: 0;
    left: 0;
    width: fit-content;
    height: fit-content;
    padding: .6rem;
    opacity: .6;
    mask-image: radial-gradient(ellipse 100% 100%, black 30%, transparent 50%);
    pointer-events: none;
    z-index: -1;
}

.likes {
    color: white;
    font-weight: bold;
    padding: .2em .4em;
    margin: 0 .1em;
    background: rgba(17,84,165,0.5);
    border-radius: 4px;
    font-size: .9rem;
    white-space: pre;

    .img { margin-top: -0.25em; }
}

.dude {
    flex-direction: row;
    align-items: center;

    .body {
        width: fit-content;
        height: fit-content;
        margin-right: .8em;
    }

    .char-name {
        margin: 0.1em 0;
        font-size: 1.6rem;
        font-weight: 400;
    }

    p { margin: 0; }

    .pseudo {
        padding: .2rem .4rem;
        margin: 0 .1em;
        background: rgba(17,84,165,0.5);
        border-radius: 4px;
        font-size: 1.2rem;
        color: white;
    }
}

.triumph {
    display: block;
    position: relative;

    .score {
        margin: 0;
        color: #1be0fb;
        float: left;
        font-weight: bold;
        text-shadow: 0 0 3px black;

        &.mush { color: #ff4059; }
    }

    .nova { margin: 1em 0; }

    ul {
        display: initial;
        float: left;
        margin-bottom: 1em;
        list-style: disc inside url("/src/assets/images/point.png')");
    }

    button {
        position: absolute;
        right: 0;
        bottom: 0;
        padding: .6em;
        background: rgba(17,84,165,0.5);
        border-radius: 4px;

        transition: all .15s;

        &:hover, &:focus, &:active {
            background: rgba(17, 84, 165, 1);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .15);
        }
    }
}

.star-card {
    margin: 0 0.5rem 2rem;
    align-self: stretch;
    min-height: 300px;

    & > div { padding: 1rem 1rem 1rem 15rem }

    &::after { //background with fadeout
        border: 16px solid transparent;
        border-image: url("/src/assets/images/nova/star-border.png')") 16 round;
        background: #283378;
        background-clip: padding-box;
        // background: linear-gradient(0deg, rgba(77,108,210,1) 30%, rgba(39,49,117,1) 100%);
        // box-shadow: inset 0 0 10px #90ADBE;
        // @include corner-bezel(16px);
        mask-image: linear-gradient(0deg, transparent 5%, rgba(0,0,0,.5) 40%, black 100%);
    }

    .epitaph {
    position: relative;
    margin: 1rem 1.2rem 1rem 0;
    padding: 1em 0.8em;
    border: 1px solid #5f67bf;
    background-color: #2d377a;
    font-style: italic;
    font-size: 1.3em;
    box-shadow: 0px 8px 6px -6px rgba(23, 68, 142, .6);

        &::before { //diamond pointer
        content:"";
        position: absolute;
        top: 6px;
        left: -7px;
        width: 14px;
        height: 14px;
        border: 1px solid #5f67bf;
        background-color: #2d377a;
        transform: rotate(-45deg);
        clip-path: polygon(0 0, 100% 0, 0 100%);
        }
    }

    .triumph {
        .score {
            position: relative;
            width: 140px;
            min-height: 112px;
            margin: 0.3em;
            padding: 0.6em 0;
            font-family: $font-days-one;
            font-size: 3.2em;
            text-align: center;
            z-index: 2;
            background: url("/src/assets/images/nova/podium.png')") no-repeat center bottom;

            &.mush { background-image:  url("/src/assets/images/nova/podium_mush.png')") }
        }

        .nova { font-size: 1.1em; }

        ul li { margin: 0 0 1.2em 1em; }
    }
}

.hidden {
    opacity: 20%;
}

.guests, .extras {
    flex-flow: row wrap;
    justify-content: center;
    max-width: 785px;
    align-self: center;
}

.guest-card, .extra-card {
    width: 223px;
    min-height: 320px;
    margin: 0 1rem 3rem;

    &::after { //background with fadeout
        border: 16px solid transparent;
        border-image: url("/src/assets/images/nova/guest-border.png')") 16 round;
        background: #1d2d72;
        background-clip: padding-box;
        // @include corner-bezel(16px, 0);
        mask-image: linear-gradient(0deg, transparent 5%, rgba(0,0,0,.5) 65%, black 100%);
    }

    .avatar { top: 1.6em; }

    .triumph {
        margin-top: 8em;
        padding-bottom: 3em;
        font-size: .9rem;

        .score {
            font-size: 1.75rem;
            margin-right: 0.25em;
        }

        .death-cause { margin: 0; }

        .epitaph {
            font-style: italic;
            margin: 0.6em 0;
            clear: both;
        }
    }
}

.extra-card {
    width: 223px;
    min-height: 130px;

    .triumph {
        margin-top: 1.5em;
        padding-bottom: 0;
    }

    &::after {
        max-height: 130px;
        border: 1px solid #387fff;
        background: #2e408f;
        @include corner-bezel(16px, 0);
    }
}


.ship {
    align-self: stretch;
    margin: 0 0.5rem;

    & > div {
        background-color: #222b6b;
        margin-bottom: 1.2em;
        border-radiuS: 3px;
    }
}

.stats {
    flex-direction: row;
    justify-content: space-evenly;
    text-align: center;

    div {
        flex: 1;

        p:first-child {
            padding: 0.5em;
            opacity: .6;
            font-size: .9em;
            font-weight: bold;
            border-bottom: 1px solid #0f0f43;
        }

        p:last-child {
            margin: .5em 0;
            font-size: 1.4rem;
            font-weight: bold;
        }
    }

    div p:first-child {
        font-size: .9em;
        font-weight: bold;
        opacity: .6;
    }
}

.progress, .roles, .honors {
    & > div {
        flex-direction: row;

        &:not(:last-child) { border-bottom: 1px solid #0f0f43; }

        p {
            width: 25%;
            max-width: 156px;
            margin: auto 0;
            padding: .8em;
            opacity: .6;
            font-size: .9em;
            font-weight: bold;
            text-align: center;
        }

        ul {
            flex-flow: row wrap;
            padding: .8em;
        }

        li {
            margin: .1em .6em;
            display: flex;
            align-items: center;

            img { margin-right: 0.4em; }
        }
    }
}

.progress div li, img { margin: .1em; }

a.back {
    @include button-style;
    width: 85%;
    max-width: 300px;
    margin: auto;
}

@media only screen and (max-width: 560px) {
    // ARBITRARY, NEEDED

        .star-card .triumph .score {
            width: 100%;
            margin: 0 auto 0.8em;
        }
}

@media only screen and (max-width: $breakpoint-mobile-l) {

    .star-card {
        & > div { padding: .6rem; }

        .avatar { top: 1.6em; }

        .dude { margin-bottom: 12rem; }

        .epitaph {
            margin-right: 0;
            margin-bottom: 0;
            font-size: 1.15em;
            padding: .6em;

            &::before {
                top: -7px;
                left: 14px;
                transform: rotate(45deg);
            }
        }
    }

    .stats {
        flex-direction: column;

        div p:first-child { border-bottom: none; }

        div:not(:last-child) {
            margin-bottom: .6em;
            border-bottom: 1px solid #0f0f43;
        }
    }

    .progress, .roles, .honors {
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
