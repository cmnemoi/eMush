<template>
    <div class="panel" v-if="exploration">
        <TerminalTips :content="exploration.uiElements.tips" />
        <section class="planet">
            <h3>{{ exploration.planet.name }}</h3>
            <span class="estimate">{{ exploration.estimatedDuration }}</span>
            <div class="card">
                <img class="planet-img" src="@/assets/images/astro/planet_unknown.png">
                <ul class="crew">
                    <li v-for="(explorator,) in exploration.explorators" :key="explorator.key">
                        <img :src="explorator.getExploratorBody()" :alt="explorator.name">
                        <p v-if="explorator.isDead">
                            <img src="@/assets/images/dead.png">
                        </p>
                        <p v-else-if="explorator.isLost">
                            <img src="@/assets/images/status/lost_on_planet.png">
                        </p>
                        <p v-else-if="explorator.isStuck">
                            <img src="@/assets/images/status/stuck_in_ship.png">
                        </p>
                        <p v-else>
                            <img src="@/assets/images/lp.png"> {{ explorator.healthPoints }}
                        </p>
                    </li>
                </ul>
            </div>
            <span class="info-trigger" @click="show = !show"><img src="@/assets/images/down.png" :class="{ revert: show }"> {{ exploration.uiElements.recoltedInfos }}</span>
            <ul class="analysis" v-if="show">
                <Tippy
                    tag="li"
                    v-for="(sector, i) in exploration.planet.sectors"
                    :key="i"
                    :class="sector.isVisited ? '' : 'unexplored'">
                    <img :src="getSectorImage(sector.key)" :alt="sector.name">
                    <template #content>
                        <h1 v-html="formatText(sector.name)" />
                        <p v-html="formatText(sector.description)" />
                    </template>
                </Tippy>
            </ul>
            <div v-if="player.hasStatusByKey('lost')" class="lost">
                <img src="@/assets/images/att.png" alt="warning">
                <p>{{ exploration.uiElements.lost }}</p>
            </div>
        </section>
        <section class="logs" v-if="exploration.logs.length > 0">
            <CountdownTimer class="estimate" :end-date="exploration.timer?.timerCycle" v-if="exploration.timer?.timerCycle">
                <template #default="slotProps">
                    <div v-if="!isCycleChangeAvailable(exploration)" class="timer">
                        <img src="@/assets/images/casio.png">
                        <span v-show="slotProps.hour > 0" class="cycle-time-left">{{ slotProps.hour
                        }}h</span>
                        <span class="cycle-time-left">{{ slotProps.min }}m</span>
                        <span class="cycle-time-left">{{ slotProps.sec }}s</span>
                    </div>
                    <div v-else>
                        <button class="new-cycle-button flashing" @click="triggerCycleChange(player)">{{ exploration.uiElements.newStep }}</button>
                    </div>
                </template>
            </CountdownTimer>
            <Tippy tag="div" class="estimate" v-else>
                <div class="timer">
                    <img src="@/assets/images/casio.png">
                    <span>{{ exploration.uiElements.finished }}</span>
                </div>
            </Tippy>
            <div v-for="(log, i) in exploration.logs" :key=i class="event">
                <img :src="getSectorImage(log.planetSectorKey)">
                <div>
                    <h3 v-html="formatText(`${log.planetSectorName} - ${log.eventName}`)"/>
                    <p class="flavor" v-html="formatText(log.eventDescription)"/>
                    <p class="details" v-html="formatText(log.eventOutcome)"/>
                </div>
            </div>
        </section>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { formatText } from "@/utils/formatText";
import CountdownTimer from "@/components/Utils/CountdownTimer.vue";
import TerminalTips from "@/components/Game/Terminals/TerminalTips.vue";
import { Exploration } from "@/entities/Exploration";
import { Player } from "@/entities/Player";
import PlayerService from "@/services/player.service";

export default defineComponent ({
    name: "ExpeditionPanel",
    components: {
        CountdownTimer,
        TerminalTips
    },
    computed: {
        exploration(): Exploration {
            if (!this.player.exploration) {
                throw new Error("No exploration found");
            }

            return this.player.exploration;
        }
    },
    props: {
        player: {
            type: Player,
            required: true
        }
    },
    methods: {
        getSectorImage(sectorKey: string): string {
            return require(`@/assets/images/astro/${sectorKey}.png`);
        },
        formatText,
        isCycleChangeAvailable(exploration: Exploration) {
            if (!exploration.timer.timerCycle) {
                return false;
            }

            return exploration.timer.timerCycle.getTime() < Date.now();
        },
        triggerCycleChange(player: Player) {
            PlayerService.triggerExplorationCycleChange(player);
        }
    },
    data() {
        return {
            show: false
        };
    }
});

</script>

<style  lang="scss" scoped>

.panel {
    position: relative;
    flex-direction: column;
    width: 424px;
    height: 460px;
    padding: 5px 8px;
    color: $deepBlue;
    background: $brightCyan;

    @include corner-bezel(6.5px, 6.5px, 0);
}

.planet {
    @extend %terminal-section;
    flex-direction: column;
    align-items: flex-start;
    background-image: url("/src/assets/images/astro/astro_bg.svg");

    .estimate { font-style: italic; }
}

.estimate {
    position: absolute;
    top: 0.1em;
    right: 0.1em;
    font-size: 0.9em;
    letter-spacing: 0.02em;

    .timer {
        display: inline;
        align-self: flex-end;
    }

    img {
        width: fit-content;
        height: fit-content;
        vertical-align: middle;
    }
}

.card {
    margin: 2.5em 0.5em 0.4em;
    flex-direction: row;
    align-items: center;

    .planet-img { width: 68px; }

    img {
        width: fit-content;
        height: fit-content;
    }
}

.crew li {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    margin-left: 0.5em;

    p {
        align-items: center;
        margin: 0.3em 0 0;
        padding: 0 0.3em;
        min-height: 18px;
        font-size: 0.9em;
        color: white;
        border: 1px solid #3e72b7;
        border-radius: 3px;
        background: #3a6aab;
        box-shadow: 0 0 4px 1px inset rgb(28, 29, 56);

        @include corner-bezel(5px);
    }
}

.info-trigger {
    align-self: flex-end;
    padding: 0.4em 0.6em;
    font-size: 0.9em;
    font-style: italic;
    color: $deepGreen;
    text-decoration: underline;
    cursor: pointer;

    img.revert { transform: scale(1, -1); }
}

.lost {
    flex-direction: row;
    align-items: center;
    gap: 0.6em;
    padding: 0.8em 0.6em;
    background: lighten($brightCyan, 6.5);
    border: solid #aad4e5;
    border-width: 1px 0;

    img {
        width: fit-content;
        height: fit-content;
    }

    p {
        margin: 0;
        font-size: 0.91em;
    }
}

.analysis {
    flex-wrap: wrap;
    gap: .4em;
    margin: 0.5em;

    li img { width: 32px; }

    .unexplored {
        filter: grayscale(1);
        opacity: 0.5;
    }
}

.logs {
    overflow-y: auto;
    @extend %game-scrollbar;

    .estimate {
        position: relative;
        font-variant: small-caps;
    }
}

.event {
    position: relative;
    display: block;
    padding: 0.4em 0.3em 0 0;
    border-bottom: 1px solid #aad4e5;

    .estimate {
        position: relative;
        font-variant: small-caps;
    }

    & > img {
        width: 32px;
        float: left;
        margin-right: 0.5em;
    }

    h3 {
        margin: 0.1em 0;
        font-weight: normal;
    }

    p { margin: 0 0 0.75em; }

    .details {
        font-style: italic;
        color: $red;
    }
}

.new-cycle-button {
    @include button-style();
    align-self: center;
    min-width: 45%;
}

</style>
