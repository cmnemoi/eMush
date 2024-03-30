<!-- OBSOLETE -->

<template>
    <div class="neron-core-terminal">
        <h1><img :src="getAssetUrl('spot2.svg')"> Cœur de NERON V4.54</h1>
        <div class="tips">
            <input id="tips" type="checkbox" name="tips">
            <label for="tips">
                Vous êtes dans le Coeur de <em class="red">NERON</em>. Ici vous pouvez le mettre à jour et <strong>débloquer des fonctionnalités</strong> avancées bénéfiques pour toute l'équipage. Ces fonctionnalités font partie du projet original <em class="red">Magellan</em>.
                <br><br>
                Les projets avanceront mieux si vous possédez <strong>les compétences adéquates</strong>.
                <br><br>
                Une seule personne, même si elle possède les compétences conseillées, peut difficilement accomplir un projet toute seule. En effet, si vous avancez un projet plus d'une fois à la suite, l'efficacité de votre action diminuera. <strong>Le travail alterné avec un camarade est la clé !</strong>
                <br><br>
                Et ce n'est pas tout : si plus d'un projet avance en parallèle, le premier fini annulera les progrès des autres.
            </label>
        </div>
        <div class="project-container">
            <div class="project">
                <h3>Anti-gravity Thrusters</h3>
                <div class="card">
                    <img :src="getAssetUrl('projects/icarus_antigrav_propeller.png')">
                    <div class="progress-container">
                        <div>
                            <img class="skill" :src="getAssetUrl('skills/human/cook.png')">
                            <img class="skill" :src="getAssetUrl('skills/human/sturdy.png')">
                        </div>
                        <span class="completion">0%</span>
                    </div>
                </div>
                <p class="description">
                    Increases your chances of successfully landing on a planet
                </p>
                <p class="efficiency">
                    Efficacité : 18% - 18%
                </p>
                <button class="action-participate">
                    Participer
                </button>
            </div>

            <div class="project ongoing">
                <h3>Launch Propulsion System</h3>
                <div class="card">
                    <img :src="getAssetUrl('projects/patrol_ship_launcher.png')">
                    <div class="progress-container">
                        <div>
                            <img class="skill" :src="getAssetUrl('skills/human/sturdy.png')">
                        </div>
                        <span class="completion">25%</span>
                    </div>
                </div>
                <p class="description">
                    Takeoff manœuvres cost less :ap: .
                </p>
                <p class="efficiency">
                    Efficacité : 3% - 3%
                </p>
                <button class="action-participate">
                    Participer
                </button>
            </div>
        </div>
        <button class="exit">
            Quitter
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { getAssetUrl } from "@/utils/getAssetUrl";

export default defineComponent ({
    name: "NeronCoreTerminal",
    props: {
    },
    methods: {
        getAssetUrl
    }
});
</script>

<style  lang="scss" scoped>

.neron-core-terminal {
    position: relative;
    flex-direction: column;
    width: 424px;
    height: 460px;
    padding: 5px 8px;
    color: $deepBlue;
    background: $brightCyan;

    clip-path: polygon(6.5px 0, calc(100% - 6.5px ) 0, 100% 6.5px, 100% calc(100% + 2em), 0 calc(100% + 2em), 0 6.5px);

    h1 {
        font-size: 1em;
        text-transform: uppercase;
        margin: 0 .2em;
    }
}

.tips { @extend %retracted-tips; }

.project-container {
    flex-direction: row;
    align-items: stretch;
    padding-bottom: .3em;
    min-height: 276px;
    overflow: auto;

    scroll-snap-type: x mandatory; // scroll will snap to projects

    --scrollbarBG: white;
    --thumbBG: rgba(0, 116, 223, 1);
    --border-radius: 6px;

    scrollbar-width: medium;
    scrollbar-color: var(--thumbBG) var(--scrollbarBG);

    &::-webkit-scrollbar {
        height: 8px;
        border-radius: var(--border-radius);
    }

    &::-webkit-scrollbar-track {
        background: var(--scrollbarBG);
        border-radius: var(--border-radius);
    }

    &::-webkit-scrollbar-thumb {
        background-color: var(--thumbBG);
        border-radius: var(--border-radius);
    }
}

.project {
    min-width: 132px;
    width: 132px;
    padding-bottom: .4em;
    margin-right: 6px;
    background: $lightCyan url("/src/assets/images/neroncore_bg.svg") no-repeat right bottom;
    border-left: 2px solid #aad4e5;

    scroll-snap-align: start; // to control scroll snapping

    @include corner-bezel(0, 6.5px, 0);

    h3 {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 42px;
        margin: 0;
        padding-bottom: .25em;
        background: #89e8fa;
        text-align: center;
        font-size: 1em;
        font-weight: normal;
        font-variant: small-caps;
        line-height: 1em;
    }

    .card {
        flex-direction: row;
        padding: 4px;

        img {
            width: fit-content;
            height: fit-content;
        }

        .progress-container {
            flex: 1;
            align-items: stretch;
            margin: auto;

            & > div {
                flex-direction: row;
                justify-content: center;
            }
        }

        .completion {
            margin: .1em;
            font-size: 2.25em;
            letter-spacing: -.03em;
            opacity: .7;
            text-align: center;
        }
    }

    .description {
        margin: .8em 4px;
        flex: 1;
    }

    .efficiency {
        opacity: .6;
        margin: .6em 0;
        font-size: .9em;
        font-style: italic;
        text-align: center;
    }

    .action-participate {
        @include button-style;
        margin: 1px 4px;
    }

    &.ongoing {
        border-color: $green;

        .completion {
            opacity: 1;
            color: darken($green, 5%);
            text-shadow: 0 0 10px white;
            }
    }
}

.exit {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 120px;
    min-height: 22px;
    transform: translateY(100%);
    align-items: center;
    justify-content: center;
    padding: .2em;
    background: #232e6e;
    border-radius: 0 0 3px 3px;
    color: white;
    font-size: 1em;
    font-weight: 700;
    font-variant: small-caps;
    letter-spacing: 0.03em;
    text-decoration: none;
    text-align: center;
    text-shadow: 0 0 4px #15273c, 0 0 4px #15273c;

    &:hover, &:focus, &:active {
        background: $brightCyan;
    }
}

</style>
