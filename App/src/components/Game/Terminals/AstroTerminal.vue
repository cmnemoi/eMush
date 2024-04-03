<template>
    <div class="planet-container">
        <section
            v-for="planet in planets"
            :key="planet.id"
            class="planet"
        >
            <h3>{{ planet.name }}</h3>
            <div class="card">
                <img :src="planet.getSmallImage()" :alt="`planet_${planet.imageId}`">
                <p class="in-orbit" v-if="terminal.infos.inOrbit">
                    <img src="@/assets/images/infoalert.png" alt="info">
                    <span v-html="formatText(terminal.infos.inOrbit)"/>
                </p>
                <p><span>{{ terminal.sectionTitles.orientation }}</span> {{ planet.orientation }}</p>
                <p><span>{{ terminal.sectionTitles.distance }}</span> {{ planet.distance }}</p>
            </div>
            <div class="analysis">
                <ul>
                    <li v-for="sector in planet.sectors" :key="sector.id">
                        <Tippy tag="img" :src="getSectorImage(sector)" :alt="sector.name">
                            <template #content>
                                <h1 v-html="formatText(sector.name)" />
                                <p v-html="formatText(sector.description)" />
                            </template>
                        </Tippy>
                    </li>
                </ul>
                <div class="actions">
                    <ActionButton
                        v-if="analyzeAction(planet)"
                        :css-class="'wide'"
                        :key="analyzeAction(planet)?.key"
                        :action="analyzeAction(planet)"
                        @click="executeTargetAction(planet, analyzeAction(planet))"
                    />
                    <Tippy
                        v-if="deleteAction(planet)"
                        tag="button"
                        class="delete"
                        @click="executeTargetAction(planet, deleteAction(planet))">
                        <template #content>
                            <h1 v-html="formatText(deleteAction(planet)?.name)" />
                            <p v-html="formatText(deleteAction(planet)?.description)" />
                        </template>
                        <img src="@/assets/images/bin.png">
                    </Tippy>
                    <Tippy
                        tag="button"
                        class="delete"
                        @click="sharePlanet(planet)">
                        <template #content>
                            <h1 v-html="formatText(terminal.buttons.sharePlanet.name)" />
                            <p v-html="formatText(terminal.buttons.sharePlanet.description)" />
                        </template>
                        <img src="@/assets/images/planet.png">
                    </Tippy>
                </div>
            </div>
        </section>
        <div v-if="scanAction">
            <section
                v-for="i in numberOfUnknownPlanets"
                :key="i"
                class="planet unknown"
            >
                <h3>???</h3>
                <div class="card">
                    <img src="@/assets/images/astro/planet_unknown.png" alt="unknown planet">
                </div>
                <div class="analysis" >
                    <!-- <p>Scanning impossible...</p> -->
                    <p>{{ scanAction.successRate }}% <img src="@/assets/images/astro/thermosensors.png" alt="thermosensors"></p>
                    <div class="actions">
                        <ActionButton
                            :key="scanAction.key"
                            :action="scanAction"
                            @click="executeTargetAction(terminalTarget, scanAction)"
                        />
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>

<script lang="ts">
import { Planet } from "@/entities/Planet";
import { PlanetSector } from "@/entities/PlanetSector";
import { Terminal } from "@/entities/Terminal";
import { defineComponent } from "vue";
import { ActionEnum } from "@/enums/action.enum";
import { Action } from "@/entities/Action";
import { formatText } from "@/utils/formatText";
import { mapActions, mapGetters } from "vuex";
import ActionButton from "@/components/Utils/ActionButton.vue";

export default defineComponent ({
    name: "AstroTerminal",
    components : {
        ActionButton
    },
    props: {
        terminal: {
            type: Terminal,
            required: true
        }
    },
    computed: {
        ...mapGetters({
            'typedMessage': 'communication/typedMessage'
        }),
        scanAction(): Action | null {
            return this.terminal.getActionByKey(ActionEnum.SCAN);
        },
        terminalTarget() : Terminal {
            return this.terminal;
        },
        planets(): Planet[] {
            const planets = this.terminal?.infos.planets;
            if (!planets) throw new Error(`No planets found for terminal ${this.terminal?.key}`);

            return planets;
        },
        numberOfUnknownPlanets(): number {
            const maxDiscoverablePlanets = this.terminal.infos?.maxDiscoverablePlanets;
            if (!maxDiscoverablePlanets) {
                throw new Error(`No maxDiscoverablePlanets found for terminal ${this.terminal?.key}`);
            }

            return maxDiscoverablePlanets - this.planets.length;
        }
    },
    methods: {
        ...mapActions({
            'executeAction': 'action/executeAction',
            'updateTypedMessage': 'communication/updateTypedMessage'
        }),
        analyzeAction(planet: Planet): Action | null {
            return this.getPlanetTargetById(planet.id).getActionByKey(ActionEnum.ANALYZE_PLANET);
        },
        deleteAction(planet: Planet): Action | null {
            return this.getPlanetTargetById(planet.id).getActionByKey(ActionEnum.DELETE_PLANET);
        },
        async executeTargetAction(target: Terminal | Planet, action: Action): Promise<void> {
            if (!target) throw new Error(`No target found for action ${action.key}`);
            if (action.canExecute) {
                await this.executeAction({ target, action });
            }
        },
        getPlanetTargetById(id: number): Planet {
            const planet = this.planets.find(planet => planet.id === id);
            if (!planet) throw new Error(`No planet found for id ${id}`);

            return planet;
        },
        getSectorImage(sector: PlanetSector): string {
            return require(`@/assets/images/astro/${sector.key}.png`);
        },
        formatText,
        sharePlanet(planet: Planet) {
            const publicChannelTab = document.getElementsByClassName('tabs')[0].getElementsByClassName('public')[0] as HTMLDivElement;
            publicChannelTab.click();
            if (this.typedMessage) {
                this.updateTypedMessage(`${this.typedMessage}\n\n${planet.toString()}`);
            } else {
                this.updateTypedMessage(planet.toString());
            }
        }
    },
    data() {
        return {
            ActionEnum
        };
    }
});
</script>

<style  lang="scss" scoped>

.planet {
    @extend %terminal-section;
    background-image: url("~@/assets/images/astro/astro_bg.svg");

    &.unknown { border-left-color: #aad4e5; }

    .card {
        margin: 1.5em 1em 0.4em;
        align-items: center;

        img {
            width: fit-content;
            height: fit-content;
            margin: 0.8em 0;
        }

        p {
            font-size: 0.92em;
            letter-spacing: 0.02em;
            font-variant: small-caps;
            margin: 0.2em 0;
        }

        span {
            color: #2c74d1;
            margin-right: 0.6em;;
        }

        .in-orbit {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4em;
            margin: 0;

            img {
                margin: 0
            }

            span {
                margin: 0;
            }
        }
    }

    .analysis {
        flex: 1;
        padding: 1em 2em;
        text-align: center;

        ul {
            flex-wrap: wrap;
            gap: 0.2em 1.2em;
            margin-bottom: 0.8em;
        }
    }

    .actions {
        flex-direction: row;
        justify-content: space-evenly;

        button {
            @include button-style;
            margin: 1px 4px;

            &:not(.delete) { min-width: 10em; }
        }
    }
}

</style>
