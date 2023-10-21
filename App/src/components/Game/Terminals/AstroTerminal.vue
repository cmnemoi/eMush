<template>
    <div class="planet-container">
        <section
            v-for="planet in planets"
            :key="planet.id"
            class="planet" 
        >
            <h3>{{ planet.name }}</h3>
            <div class="card">
                <img :src="getPlanetImage(planet)">
                <p><span>{{ terminal.sectionTitles.orientation }}</span> {{ planet.orientation }}</p>
                <p><span>{{ terminal.sectionTitles.distance }}</span> {{ planet.distance }}</p>
            </div>
            <div class="analysis">
                <ul>
                    <li v-for="sector in planet.sectors" :key="sector.id">
                        <Tippy tag="img" :src="getSectorImage(sector.key)">
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
                    <Tippy v-if="deleteAction(planet)" 
                           tag="button" 
                           class="delete" 
                           @click="executeTargetAction(planet, deleteAction(planet))">
                        <template #content>
                            <h1 v-html="formatText(deleteAction(planet)?.name)" />
                            <p v-html="formatText(deleteAction(planet)?.description)" />
                        </template>
                        <img src="@/assets/images/bin.png">
                    </Tippy>
                </div>
            </div>
        </section>
        <section
            v-for="i in numberOfUnknownPlanets"
            :key="i"
            class="planet unknown"
        >
            <h3>???</h3>
            <div class="card">
                <img src="@/assets/images/astro/planet_unknown.png">
            </div>
            <div class="analysis">
                <!-- <p>Scanning impossible...</p> -->
                <p>{{ scanAction.successRate }}% <img src="@/assets/images/astro/thermosensors.png"></p>
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
</template>

<script lang="ts">
import { Planet } from "@/entities/Planet";
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
        },
    },
    computed: {
        ...mapGetters({
            'confirmPopup': 'player/confirmPopup',
        }),
        scanAction(): Action {
            const action = this.terminal?.actions.find(action => action.key === ActionEnum.SCAN);
            if (!action) throw new Error(`No ${ActionEnum.SCAN} action found for terminal ${this.terminal?.key}`);

            return action;
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
            'confirmAction': 'player/openConfirmPopup',
            'clearConfirmPopup': 'player/clearConfirmPopup',
        }),
        analyzeAction(planet: Planet): Action | null {
            const action = this.getPlanetTargetById(planet.id).actions.find(action => action.key === ActionEnum.ANALYZE_PLANET);
            return action ? action : null;
        },
        deleteAction(planet: Planet): Action | null {
            const action = this.getPlanetTargetById(planet.id).actions.find(action => action.key === ActionEnum.DELETE_PLANET);
            return action ? action : null;
        },
        async executeTargetAction(target: Terminal | Planet, action: Action): Promise<void> {
            if (!target) throw new Error(`No target found for action ${action.key}`);
            if (action.canExecute) {
                if(action.toConfirm) {
                    this.confirmAction();
                }

                console.log(this.confirmPopup);

                if (this.confirmPopup.accepted) {
                    this.clearConfirmPopup();
                    await this.executeAction({ target, action });
                }
            }
        },
        getPlanetTargetById(id: number): Planet {
            const planet = this.planets.find(planet => planet.id === id);
            if (!planet) throw new Error(`No planet found for id ${id}`);

            return planet;
        },
        getPlanetSeedFromName(name: string): number {
            return name.split('').reduce((acc, char) => acc + char.charCodeAt(0), 0);
        },
        getPlanetImage(planet: Planet): string {
            const id = this.getPlanetSeedFromName(planet.name) % this.numberOfPlanetImages;
            return require(`@/assets/images/astro/planet_${id}_small.png`);
        },
        getSectorImage(sector: string): string {
            return require(`@/assets/images/astro/${sector}.png`);
        },
        formatText(text: string | null): string {
            if (!text)
                return '';
            return formatText(text);
        }
    },
    data() {
        return {
            ActionEnum,
            numberOfPlanetImages: 5,
        };
    },
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