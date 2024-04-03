<template>
    <div class="box-container">
        <div v-if="deadPlayerInfo" class="death-summary">
            <div class="char-sheet">
                <img class="avatar" :src="characterPortrait" alt="avatar">
                <div>
                    <div class="char-card">
                        <img class="body" :src="characterBody(player.character.key)" alt="">
                        <div>
                            <h3 class="char-name">
                                {{ player.character.name }}
                            </h3>
                            <p class="pseudo">
                                {{ getUserInfo.username }}
                            </p>
                        </div>
                        <p class="score">
                            {{ player.triumph?.quantity }}<img src="@/assets/images/triumph.png" alt="triumph">
                        </p>
                    </div>
                    <div class="epitaph-form">
                        <textarea
                            id="epitaph"
                            v-model="epitaph"
                            maxlength="300"
                            placeholder="Laissez vos impressions sur la partie ici !"
                        />
                        <p :class="{ limit: !(maxChar - epitaph.length) }" class="char-count">
                            {{ (maxChar - epitaph.length) }} char.
                        </p>
                    </div>
                    <div>
                        <p class="death-cause">
                            <img src="@/assets/images/dead.png" alt="dead"> {{ deadPlayerInfo.endCauseValue }}
                        </p>
                    </div>
                    <HistoryLogs />
                </div>
            </div>
            <table class="crew-summary">
                <tbody>
                    <tr>
                        <th>{{ $t('deathpage.name') }}</th>
                        <th>{{ $t('deathpage.death') }}</th>
                        <th>{{ $t('deathpage.reason') }}</th>
                        <th>{{ $t('deathpage.like') }}</th>
                    </tr>
                    <tr v-for="crewPlayer in deadPlayerInfo.players" :key="crewPlayer.id">
                        <td><img :src="characterBody(crewPlayer.character.key)" class="char hua"> <span class="charname">{{ crewPlayer.character.name }}</span></td>
                        <td>{{ formatDeathDate(crewPlayer.deathDay, crewPlayer.deathCycle) }}</td>
                        <td>{{ crewPlayer.endCauseValue }}</td>
                        <td>
                            <button class="like" :class="isPlayerLiked(crewPlayer.id) ? 'liked' : ''" @click="toggleLike(crewPlayer.id)">
                                {{ getNumberLikes(crewPlayer) }} <img src="@/assets/images/dislike.png">
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p><em>{{ $t('deathpage.notyet') }}</em></p>
            <a href="#" class="validate" @click="endGame">{{ $t('deathpage.endgame') }}</a>
        </div>
        <CommsPanel :calendar="player.daedalus.calendar" />
    </div>
</template>

<script lang="ts">

import { Player } from "@/entities/Player";
import { characterEnum } from "@/enums/character";
import { mapGetters } from "vuex";
import PlayerService from "@/services/player.service";
import HistoryLogs from "@/components/Game/HistoryLogs.vue";
import CommsPanel from "@/components/Game/Communications/CommsPanel.vue";
import { defineComponent } from "vue";
import { DeadPlayerInfo } from "@/entities/DeadPlayerInfo";

interface PurgatoryState {
    deadPlayerInfo: DeadPlayerInfo | null,
    maxChar: number,
    epitaph: string,
    likedPlayers: number[],
};

export default defineComponent ({
    name: 'Purgatory',
    head() {
        return {
            title: this.$t('deathpage.title')
        };
    },
    components: { CommsPanel, HistoryLogs },
    props: {
        player: {
            type: Player,
            required: true
        }
    },
    data: function (): PurgatoryState {
        return {
            deadPlayerInfo: null,
            maxChar: 250,
            epitaph: '',
            likedPlayers: []
        };
    },
    methods: {
        toggleLike: function(playerId: number): void {
            if (!playerId) return;
            if (this.likedPlayers.includes(playerId)) {
                this.likedPlayers = this.likedPlayers.filter((player: number) => player != playerId);
            } else {
                this.likedPlayers.push(playerId);
            }
        },
        characterBody: function(characterKey: string): string {
            return characterEnum[characterKey].body;
        },
        endGame: function(): void {
            PlayerService.sendEndGameRequest(this.player, this.epitaph, this.likedPlayers);
        },
        formatDeathDate: function(deathDay: number|null, deathCycle: number|null): string {
            if (!deathDay || !deathCycle) {
                return '-';
            }
            return `${deathDay}.${deathCycle}`;
        },
        isPlayerLiked: function(playerId: number): boolean {
            return this.likedPlayers.includes(playerId);
        },
        getNumberLikes: function(crewPlayer: DeadPlayerInfo): number {
            if (this.isPlayerLiked(crewPlayer.id)) {
                return crewPlayer.likes + 1;
            }
            return crewPlayer.likes;
        }
    },
    computed: {
        characterPortrait: function(): string {
            return characterEnum[this.player.character.key].portrait ?? "";
        },
        ...mapGetters('auth', [
            'getUserInfo'
        ])
    },
    beforeMount(): void {
        PlayerService.loadDeadPlayerInfo(this.player.id).then((res: DeadPlayerInfo|null) => {
            this.deadPlayerInfo = res;
        });
    }
});
</script>

<style lang="scss" scoped>

.box-container { flex-flow: row wrap; }

.death-summary {
    flex: 1;
    padding-right: 10px;
    font-size: 1.05rem;
}

h1 {
    font-size: 1.65em;
    letter-spacing: .04em;
    text-transform: uppercase;
    margin: .6em 1.2em 1.2em;
}

.char-sheet {
    flex-direction: row;

    .avatar {
        width: fit-content;
        height: fit-content;
        margin-right: -1.4em;
        opacity: .6;
        mask-image: radial-gradient(ellipse 100% 100%, black 30%, transparent 50%);
    }

    .char-card {
        flex-direction: row;
        align-items: center;

        .body {
            width: fit-content;
            height: fit-content;
            margin-right: .8em;
        }

        .char-name, .pseudo, .score { margin: .1em 0; }

        .char-name {
            font-size: 1.85em;
            font-weight: 400;
        }

        .pseudo { font-size: 1.25em; }

        .score {
            flex: 1;
            font-family: $font-days-one;
            color: #01c3df;
            text-align: right;
            font-size: 2.6em;
            letter-spacing: .05em;
            text-shadow: 0 0 2px black;

            img {
                vertical-align: middle;
                margin-left: .25em;
            }
        }
    }
}

.epitaph-form {
    position: relative;
    margin: 1.4em 0;
    border: 1px solid #5f67bf;
    background-color: rgba(31, 39, 104, .5);
    box-shadow: 0px 8px 6px -6px rgba(23, 68, 142, .6);

    &::before {
        content:"";
        position: absolute;
        left: -7px;
        top: 6px;
        width: 14px;
        height: 14px;
        border: 1px solid #5f67bf;
        background-color: #1b215c;
        transform: rotate(-45deg);
        clip-path: polygon(0 0, 100% 0, 0 100%);
    }

    #epitaph {
        padding: 1em .8em;
        color: white;
        font-size: 1.4em;
        line-height: 1.15;
        font-style: italic;
        border: none;
        background: none;
        resize: vertical;
    }

    /* do not group these rules */
    #epitaph::-webkit-input-placeholder {
        color: rgba(255, 255, 255, .8);
    }
    #epitaph:-moz-placeholder {
        /* FF 4-18 */
        color: rgba(255, 255, 255, .8);
        opacity: 1;
    }
    #epitaph::-moz-placeholder {
        /* FF 19+ */
        color: rgba(255, 255, 255, .8);
        opacity: 1;
    }
    #epitaph:-ms-input-placeholder {
        /* IE 10+ */
        color: rgba(255, 255, 255, .8);
    }
    #epitaph::-ms-input-placeholder {
        /* Microsoft Edge */
        color: rgba(255, 255, 255, .8);
    }
    #epitaph::placeholder {
        /* modern browser */
        color: rgba(255, 255, 255, .8);
    }

    .char-count {
        position: absolute;
        top: -.8em;
        right: 1.2em;
        margin: 0;
        padding: .2em .6em;
        font-size: .95em;
        font-style: italic;
        font-weight: bold;
        letter-spacing: .05em;
        text-shadow: 0 0 4px #092f6d;
        /* border: 1px solid #5f67bf; */
        border-radius: 4px;
        background-color: #5f67bf;

        transition: all .2s;

        &.limit { background-color: #ff4e64; }
    }
}

.death-cause { margin-top: 0; }

.crew-summary {
    width: 100%;
    background: #222b6b;
    border-radius: 5px;
    border-collapse: collapse;

    img { vertical-align: middle; }

    tr:not(:first-of-type) {
        border-top: 1px solid rgba(0,0,0,0.2);

        &:hover { background: rgba(255, 255, 255, .03); }
    }

    th, td {
        padding: 1em .5em;
        text-align: center;
        vertical-align: middle;
    }

    th {
        opacity: .6;
        letter-spacing: .05em;
    }

    button.like {
        cursor: pointer;
        color: white;
        font-weight: bold;
        padding: .2em .4em;
        margin: 0 auto;
        background: rgba(17,84,165,0.5);
        border-radius: 4px;
        white-space: pre;

        transition: all .15s;

        &:hover, &:focus, &:active {
            background: rgba(17, 84, 165, 1);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .15);
        }

        &.liked {
            background: rgba(255, 54, 118, .5);

            &:hover, &:focus, &:active { background: rgba(255, 54, 118, .7); }
        }
    }
}

.validate {
    @include button-style;
    margin: .5em auto;
    padding: .2em 1em .4em;
}


.chat { /* PROVISIONAL */
    width: 406px;
    height: 435px;
    margin-left: 15px;
    margin-top: 2em;
    color: red;
    background: $brightCyan;
}

@media screen and (max-width: $breakpoint-desktop-m) and (orientation: portrait) {

    .char-sheet .avatar {
        margin-top: 2em;
        margin-right: -1em;
        width: 110px;
    }

    .epitaph-form #epitaph { font-size: 1.25em; }

    .validate {
        font-size: 1em;
        margin-bottom: 2.5em;
    }
}

</style>



