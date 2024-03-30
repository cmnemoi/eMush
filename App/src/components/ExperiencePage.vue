<template>
    <div class="purgatory-container">
        <div v-if="deadPlayerInfo" class="death-summary">
            <h1>{{ $t('deathpage.title') }}</h1>
            <div class="char-sheet">
                <img class="avatar" :src="characterPortrait" alt="avatar">
                <div>
                    <div class="char-card">
                        <img class="body" :src="characterBody(player.character.key)" alt="">
                        <div>
                            <h3 class="char-name">
                                {{ player.characterValue }}
                            </h3>
                            <p class="pseudo">
                                {{ getUserInfo.username }}
                            </p>
                        </div>
                        <p class="score">
                            {{ player.triumph }}<img :src="getAssetUrl('triumph.png')" alt="triumph">
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
                            <img :src="getAssetUrl('dead.png')" alt="dead"> {{ deadPlayerInfo.endCauseValue }}
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
                    <tr v-for="(player,key) in deadPlayerInfo.players" :key="key">
                        <td><img :src="characterBody(player.character.key)" class="char hua"> <span class="charname">{{ player.characterValue }}</span></td>
                        <td>{{ player.deathTime ? player.deathTime : '-' }}</td>
                        <td>{{ player.endCauseValue ? player.endCauseValue : "Pas Encore" }}</td>
                        <td>
                            <button class="like">
                                1 <img :src="getAssetUrl('dislike.png')">
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
import { getAssetUrl } from "@/utils/getAssetUrl";

interface PurgatoryState {
    deadPlayerInfo: DeadPlayerInfo | null,
    maxChar: number,
    epitaph: string
};

export default defineComponent ({
    name: 'Purgatory',
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
            maxChar: 300,
            epitaph: ''
        };
    },
    methods: {
        characterBody: function(characterKey: string): string {
            return characterEnum[characterKey].body;
        },
        endGame: function(): void {
            PlayerService.sendEndGameRequest(this.player, this.epitaph, []);
        },
        getAssetUrl
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

.purgatory-container {
    flex-flow: row wrap;
    max-width: $breakpoint-desktop-l;
    width: 100%;
    margin: 36px auto;
    padding: 12px 12px 42px 12px;
    z-index: 10;
    font-size: .9rem;

    .death-summary {
        flex: 1;
        padding-right: 10px;
    }

    h1 {
        font-size: 1.4em;
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
                font-size: 1.6em;
                font-weight: 400;
            }

            .pseudo { font-size: 1.05em; }

            .score {
                flex: 1;
                font-family: Days-One;
                color: #01c3df;
                text-align: right;
                font-size: 2.2em;
                letter-spacing: .05em;
                text-shadow: 0 0 2px black;

                img {
                    vertical-align: middle;
                    margin-left: .25em;
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
                font-size: 1.2em;
                line-height: 1.6em;
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
                font-size: .8em;
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
    }

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
            font-size: .85em;
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
        @include button-style($font-size: 1em);
        margin: .5em auto;
        padding: .2em 1em .4em;
    }


    .chat { /* PROVISIONAL */
        width: 406px;
        height: 435px;
        margin-left: 15px;
        margin-top: 2em;
        color: red;
        background: #c2f3fc;
    }
}



</style>



