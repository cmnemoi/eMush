<template>
    <div class="box-container">
        <div class="panel" v-if="!error && closedExploration">
            <TerminalTips :content=closedExploration.tips />
            <section class="planet">
                <h3>{{ closedExploration.planet }}</h3>
                <div class="card">
                    <img class="planet-img" src="@/assets/images/astro/planet_unknown.png">
                    <ul class="crew">
                        <li v-for="(explorator, i) in closedExploration.explorators" :key="i">
                            <img :src="getExploratorBody(explorator)" :alt="explorator">
                            <p>
                                <img class="explorator-status" src="@/assets/images/in_game.png" v-if="explorator.isAlive">
                                <img class="explorator-status" src="@/assets/images/dead.png" v-else>
                            </p>
                        </li>
                    </ul>
                </div>
                <ul class="analysis">
                    <li v-for="(sector, key) in closedExploration.sectors" :key="key">
                        <img :src="getSectorImageByKey(sector)" :alt="sector">
                    </li>
                </ul>
            </section>
            <section class="logs">
                <div v-for="(log, i) in closedExploration.logs.toReversed()" :key=i class="event">
                    <img :src="getSectorImageByKey(log.planetSectorKey)">
                    <div>
                        <h3 v-html="formatText(`${log.planetSectorName} - ${log.eventName}`)"/>
                        <p class="flavor" v-html="formatText(log.eventDescription)"/>
                        <p class="details" v-html="formatText(log.eventOutcome)"/>
                    </div>
                </div>
            </section>
        </div>
        <div class="error" v-else-if="error">
            <h1 class="title">{{ $t("errors.title") }}</h1>
            <div class="error-container">
                <img class="neron-img" src="@/assets/images/neron_eye.gif" alt="Neron">
                <span class="neron-message" v-html="$t('errors.neronMessage')"></span>
                <p class="error">{{ $t('errors.cannotAccessExploration') }}</p>
                <p class="community" v-html="$t('errors.consultCommunity')"></p>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import TerminalTips from "@/components/Game/Terminals/TerminalTips.vue";
import { ClosedExploration } from "@/entities/ClosedExploration";
import { characterEnum } from "@/enums/character";
import store from "@/store";
import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { mapState } from "vuex";
import { formatText } from "@/utils/formatText";
import { ClosedExplorator } from "@/entities/ClosedExplorator";

export default defineComponent ({
    name: "ClosedExpeditionPanel",
    components: {
        TerminalTips
    },
    computed: {
        ...mapState('error', [
            'error'
        ]),
    },
    data() {
        return {
            closedExploration: null as ClosedExploration | null,
        };
    },
    methods: {
        getClosedExplorationById: async(id: number): Promise<ClosedExploration| null> => {
            store.dispatch('gameConfig/setLoading', { loading: true });
            const apiBaseUrl = process.env.VUE_APP_API_URL;
            if (!apiBaseUrl) {
                throw new Error('VUE_APP_API_URL is undefined');
            }
            const closedExplorationData = await ApiService.get(urlJoin(apiBaseUrl, 'closed_explorations', String(id))).then((response) => {
                return response.data;
            }).catch((error) => {
                store.dispatch('error/setError', { error });
                return null;
            }).finally(() => {
                store.dispatch('gameConfig/setLoading', { loading: false });
            });
            
            let closedExploration = null;
            if (closedExplorationData) {
                closedExploration = (new ClosedExploration()).load(closedExplorationData);
            }

            return closedExploration;
        },
        getExploratorBody(explorator: ClosedExplorator): string {
            return characterEnum[explorator.logName].body;
        },
        getSectorImageByKey(key: string): string {
            return require(`@/assets/images/astro/${key}.png`);
        },
        formatText
    },
    beforeMount() {
        const id = Number(this.$route.params.id);
        this.getClosedExplorationById(id)
            .then((closedExploration: ClosedExploration | null) => {
                if (closedExploration) {
                    this.closedExploration = closedExploration;
                }
            });
    }
});
</script>

<style lang="scss" scoped>
.panel {
    position: relative;
    flex-direction: column;
    width: 100%;
    height: 100%;
    padding: 5px 8px;
    margin: auto;
    color: $deepBlue;
    background: $brightCyan;

    @include corner-bezel(6.5px, 6.5px, 6.5px, 6.5px);
}

.planet {
    @extend %terminal-section;
    flex-direction: column;
    align-items: flex-start;
    background-image: url("~@/assets/images/astro/astro_bg.svg");

    .estimate { font-style: italic; }
}

.estimate {
    position: absolute;
    top: 0.1em;
    right: 0.1em;
    font-size: 0.9em;
    letter-spacing: 0.02em;

    img {
        width: fit-content;
        height: fit-content;
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

    .explorator-status {
        margin-bottom: 1px;
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
}

.event {
    position: relative;
    display: block;
    padding: 0.4em 0.3em 0 0;
    border-bottom: 1px solid #aad4e5;

    .estimate { font-variant: small-caps; }

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

.error-container {
    display: block;
    margin: 5px 0 10px 0;
    font-size: 11pt;

    &::v-deep(strong) {
    color: $cyan;
    }

    &::v-deep(a) {
        color: $green;
    }

    .title {
        font-size: 17pt;
        margin-bottom: 15px;
    }

    .neron-img {
        float: left;
        width: 100px;
        height: 100px;
        margin-right: 10px;
    }

    .neron-message {
        display: inline;
    }

    .error {
        margin: 10px 10px;
        margin-left: 110px;
        padding: 5px 10px;
        border: 1px solid $red;
        background-color: #222b6b;
    }
}
</style>