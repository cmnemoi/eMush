<template>
    <SkillSelectionMenu :player="player" />
    <LearnSkillMenu :player="player" />
    <div class="game-banner" v-if="player">
        <div class="character-banner">
            <a class="in-game-level">{{ playerLevel }}</a>
            <div class="in-game-level-progress">
                <div />
            </div>
            <div class="character-info">
                <span class="character-name">{{ player.character.name }}</span>
                <div class="titles">
                    <Tippy tag="ul" v-for="(key) in player.titles" :key="key">
                        <div class="title"><TitleImage :title="key" /><span class="title-name">{{ key.name }}</span></div>
                        <template #content>
                            <h1 v-html="formatContent(key.name)" />
                            <p v-html="formatContent(key.description)" />
                        </template>
                    </Tippy>
                </div>
            </div>
        </div>

        <div class="daedalus-banner">
            <Alerts :daedalus="daedalus" />

            <div class="daedalus-info" v-if="daedalus">
                <ul>
                    <Tippy tag="li">
                        <span>{{ daedalus.oxygen.quantity }} <img :src="getImgUrl('o2.png')"></span>
                        <template #content>
                            <h1 v-html="formatContent(daedalus.oxygen.name)" />
                            <p v-html="formatContent(daedalus.oxygen.description)" />
                        </template>
                    </Tippy>
                    <Tippy tag="li">
                        <span>{{ daedalus.fuel.quantity }} <img :src="getImgUrl('ui_icons/fuel.png')"></span>
                        <template #content>
                            <h1 v-html="formatContent(daedalus.fuel.name)" />
                            <p v-html="formatContent(daedalus.fuel.description)" />
                        </template>
                    </Tippy>
                    <Tippy tag="li">
                        <span>{{ daedalus.hull.quantity }} <img :src="getImgUrl('shield.png')"></span>
                        <template #content>
                            <h1 v-html="formatContent(daedalus.hull.name)" />
                            <p v-html="formatContent(daedalus.hull.description)" />
                        </template>
                    </Tippy>
                    <Tippy tag="li" v-if="daedalus.shield">
                        <span>
                            {{ daedalus.shield.quantity }}
                            <img :src="getImgUrl('plasma.png')" v-if="daedalus.shield.quantity > 0">
                            <img :src="getImgUrl('plasma_off.png')" v-else>
                        </span>
                        <template #content>
                            <h1 v-html="formatContent(daedalus.shield.name)" />
                            <p v-html="formatContent(daedalus.shield.description)" />
                        </template>
                    </Tippy>
                    <Tippy tag="li">
                        <span>
                            {{ player.triumph?.quantity }}
                            <img v-if="player.isMush" :src="getImgUrl('ui_icons/player_variables/triumph_mush.png')">
                            <img v-else :src="getImgUrl('ui_icons/player_variables/triumph.png')">
                        </span>
                        <template #content>
                            <h1 v-html="formatContent(player?.triumph?.name)" />
                            <p v-html="formatContent(player?.triumph?.description)" />
                        </template>
                    </Tippy>
                </ul>
            </div>
            <div class="daedalus-crew">
                <Tippy tag="div">
                    <ol>
                        <li v-for="(key) in daedalus.humanPlayerAlive" :key="key">
                            <img class="alive" :src="getImgUrl('p_alive.png')" alt="crewmate">
                        </li>
                        <li v-for="(key) in daedalus.mushPlayerAlive" :key="key">
                            <img class="mush" :src="getImgUrl('p_mush.png')" alt="mush">
                        </li>
                        <li v-for="(key) in daedalus.cryogenizedPlayers" :key="key">
                            <img class="cryo" :src="getImgUrl('p_cryo.png')" alt="in cryosleep">
                        </li>
                        <li v-for="(key) in daedalus.mushPlayerDead" :key="key">
                            <img class="deadmush" :src="getImgUrl('p_deadmush.png')" alt="dead mush">
                        </li>
                        <li v-for="(key) in daedalus.humanPlayerDead" :key="key">
                            <img class="dead" :src="getImgUrl('p_dead.png')" alt="dead crewmate">
                        </li>
                    </ol>
                    <template #content>
                        <h1 v-html="formatContent(daedalus.crewPlayer.name)" />
                        <p v-html="formatContent(daedalus.crewPlayer.description)" />
                    </template>
                </Tippy>
            </div>
            <div class="cycle-time">
                <Tippy tag="div">
                    <ul>
                        <li>
                            <countdown-timer :end-date="daedalus?.timer?.timerCycle">
                                <template #default="countdownTimer">
                                    <div v-if="!isCycleChangeAvailable(daedalus) && !countdownTimer.isCountdownFinished" class="timer">
                                        <li><img class="casio-img" :src="getImgUrl('casio.png')"></li>
                                        <span v-show="countdownTimer.hour > 0" class="cycle-time-left">{{ countdownTimer.hour }}h</span>
                                        <span class="cycle-time-left">{{ countdownTimer.min }}m</span>
                                        <span class="cycle-time-left">{{ countdownTimer.sec }}s</span>
                                    </div>
                                    <div v-else>
                                        <button class="new-cycle-button flashing" @click="triggerCycleChange(player)">{{ $t('game.communications.newCycle') }}</button>
                                    </div>
                                </template>
                            </countdown-timer>
                        </li>
                    </ul>
                    <template #content>
                        <h1 v-html="formatContent(daedalus?.timer?.name)" />
                        <p v-html="formatContent(daedalus?.timer?.description)" />
                    </template>
                </Tippy>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Daedalus } from "@/entities/Daedalus";
import { Player } from "@/entities/Player";
import CountdownTimer from "@/components/Utils/CountdownTimer.vue";
import TitleImage from "@/components/Utils/TitleImage.vue";
import Alerts from "@/components/Game/Ship/Alerts.vue";
import LearnSkillMenu from "@/components/Game/LearnSkillMenu.vue";
import SkillSelectionMenu from "@/components/Game/SkillSelectionMenu.vue";
import { defineComponent } from "vue";
import PlayerService from "@/services/player.service";
import { getImgUrl } from "@/utils/getImgUrl";
import { mapGetters } from "vuex";


export default defineComponent({
    name: "BannerPanel",
    components: { Alerts, CountdownTimer, LearnSkillMenu, SkillSelectionMenu, TitleImage },
    props: {
        player: Player,
        daedalus: Daedalus
    },
    computed: {
        ...mapGetters('player', ['displayMushSkills']),
        playerLevel(): number | undefined {
            return this.displayMushSkills ? this.player?.character.mushLevel : this.player?.character.humanLevel;
        }
    },
    methods: {
        getImgUrl,
        isCycleChangeAvailable(daedalus: Daedalus | undefined): boolean {
            if (!daedalus?.timer?.timerCycle) {
                return false;
            }
            return (daedalus.timer.timerCycle).getTime() - (new Date()).getTime() <= 0;
        },
        triggerCycleChange(player: Player) {
            PlayerService.triggerCycleChange(player);
        }
    }
});
</script>

<style  lang="scss" scoped>
.game-banner,
.daedalus-banner,
.character-banner,
.daedalus-players,
.flashing,
.cycle-time {
    flex-direction: row;
    justify-content: flex-end;
    min-width: 7em;
}

.timer { flex-direction: row; }

p,
a,
li,
ul,
span {
    letter-spacing: 0.03em;
    font-variant: small-caps;
}

span.tippy-tooltip {
    margin-right: 5px;
    margin-left: 5px;
}

.game-banner {
    margin-bottom: 8px;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
}

.character-banner {
    display: flex;
    flex-direction: row;
    align-self: flex-start;
}

.in-game-level {
    @include button-style(1.4em);
    @include corner-bezel(12px, 0);

    width: 52px;
    height: 35px;
    border: 1px solid rgba(4, 21, 156, 1);
    border-radius: 4px;
    margin: auto;
    padding-right: 4px;
    font-family: $font-days-one;
}

.in-game-level-progress {
    position: relative;
    width: 11px;
    height: 33px;
    z-index: 2;
    margin: 1px;
    margin-left: -10px;
    border-radius: 4px;
    border: 1px solid #4077b5;
    background: #4077b5;
    box-shadow: 0 0 5px 1px #15273c inset, 0 0 0 1px #234164;

    &>div {
        position: relative;
        width: 100%;
        height: 100%;
        border-radius: 3px;
        background: rgb(128, 72, 112);
        background: linear-gradient(90deg, rgba(255, 137, 204, 1) 0%, rgba(255, 137, 204, 1) 25%, rgba(213, 38, 89, 1) 94%, rgba(128, 18, 47, 1) 100%);
        box-shadow: 0 0 2px 1px rgba(71, 10, 26, 0.7) inset;
    }

    &::after {
        content: "";
        position: absolute;
        bottom: 2px;
        width: 9px;
        height: 11px;
    }
}

.character-info {
    display: flex;
    justify-content: center;
    margin-left: 6px;

    .character-name {
        margin-bottom: 1px;
        font-size: 1.05em;
        font-weight: 700;
        text-transform: uppercase;
    }

    .titles {
        display: flex;
        flex-direction: row;

        .title {
            flex-direction: row;
            align-items: center;
            margin-right: 6px;

            .title-name {
                margin-left: 4px;
                font-size: 0.8em;
                font-weight: bold;
                font-variant: small-caps;
                white-space: nowrap;
                color: rgba(255, 255, 255, 0.75);
            }
        }
    }

}

.daedalus-banner {
    align-items: center;
    flex-wrap: wrap;
    row-gap: 0.5em;

    &>div {
        margin: 0 12px;
        align-items: center;
    }

    .daedalus-info {
        :not(:last-child) {
            padding-right: 1em;
        }

        img {
            padding: 2px 0 0 4px;
        }

        li {
            display: flex;
        }
    }

    .daedalus-crew img.cryo {
        opacity: 0.5;
    }

    .cycle-time img {
        margin-right: 0.4em;
    }

    .cycle-time ul {
        align-items: center;
    }
}

.new-cycle-button {
    @include button-style();
    display: block;
}
</style>
