<template>
    <div class="astro-terminal">
        <h1><img src="@/assets/images/spot2.svg"> Astro Terminal</h1>
        <div class="tips">
            <input id="tips" type="checkbox" name="tips">
            <label for="tips">
                Afin de trouver une planète à explorer, il faut d'abord <strong>réaliser un scan</strong> pour en trouver jusqu'à un maximum de 2. Les résultats des planètes trouvées sont personnels et exclusifs à chaque personne.
                <br><br>
                Une fois une planète trouvée et choisie, vous devez communiquer à l'équipage l'<strong>orientation et le fuel</strong> nécessaire pour s'y rendre. Ceci permettra au <strong>Commandant</strong> de déplacer le Daedalus vers la planète choisie.
                <br><br>
                Lorsque le Daedalus est arrivé et en orbite, il est impossible de scanner pour rechercher d'autres planètes. Par contre, vous pouvez <strong>sonder la planète</strong> en orbite pour obtenir des GéoDonnées supplementaires. Ces données serviront au corps expéditionnaire, qui pourra préparer à l'avance l'exploration.
                <br><br>
                Pour sortir de l'orbite, le Commandant en charge doit exécuter l'action depuis le Terminal de Commandement.
            </label>
        </div>
        <div class="planet-container">
            <section class="planet">
                <h3>Goulidon</h3>
                <div class="card">
                    <img src="@/assets/images/astro/planet_0_small.png">
                    <p><span>Direction</span> East</p>
                    <p><span>Fuel required</span> 3</p>
                </div>
                <div class="analysis">
                    <ul>
                        <li><img src="@/assets/images/astro/cold.png"></li>
                        <li><img src="@/assets/images/astro/ocean.png"></li>
                        <li><img src="@/assets/images/astro/forest.png"></li>
                        <li><img src="@/assets/images/astro/cold.png"></li>
                        <li><img src="@/assets/images/astro/ocean.png"></li>
                        <li><img src="@/assets/images/astro/forest.png"></li>
                        <li><img src="@/assets/images/astro/cold.png"></li>
                        <li><img src="@/assets/images/astro/ocean.png"></li>
                        <li><img src="@/assets/images/astro/forest.png"></li>
                        <li><img src="@/assets/images/astro/cold.png"></li>
                        <li><img src="@/assets/images/astro/cold.png"></li>
                        <li><img src="@/assets/images/astro/ocean.png"></li>
                        <li><img src="@/assets/images/astro/forest.png"></li>
                        <li><img src="@/assets/images/astro/cold.png"></li>
                        <li><img src="@/assets/images/astro/ocean.png"></li>
                        <li><img src="@/assets/images/astro/forest.png"></li>
                        <li><img src="@/assets/images/astro/cold.png"></li>
                        <li><img src="@/assets/images/astro/ocean.png"></li>
                        <li><img src="@/assets/images/astro/forest.png"></li>
                        <li><img src="@/assets/images/astro/cold.png"></li>
                        <li><img src="@/assets/images/astro/cold.png"></li>
                        <li><img src="@/assets/images/astro/ocean.png"></li>
                    </ul>
                    <div class="actions">
                        <button>
                            <span class="cost">2<img src="@/assets/images/pa.png" alt="ap"></span>
                            <span>Analysis</span>
                        </button>
                        <button class="delete">
                            <img src="@/assets/images/bin.png">
                        </button>
                    </div>
                </div>
            </section>

            <section class="planet undiscovered">
                <h3>???</h3>
                <div class="card">
                    <img src="@/assets/images/astro/planet_0_small.png">
                </div>
                <div class="analysis">
                    <p>Scanning impossible...</p>
                </div>
            </section>
            

        </div>
        <button class="exit">
            Quitter
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent ({
    name: "AstroTerminal",
    props: {
    }
});
</script>

<style  lang="scss" scoped>

.astro-terminal {
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

.planet-container {
    // flex-direction: row;
    align-items: stretch;
    padding-bottom: .3em;
    min-height: 276px;
    overflow: auto;

    scroll-snap-type: y mandatory; // scroll will snap to projects

    --scrollbarBG: white;
    --thumbBG: rgba(0, 116, 223, 1);
    --border-radius: 6px;

    scrollbar-width: thin;
    scrollbar-color: var(--thumbBG) var(--scrollbarBG);

    &::-webkit-scrollbar {
        width: 8px;
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

.planet {
    position: relative;
    flex-direction: row;
    align-items: center;
    margin-bottom: .8em;
    background: $lightCyan url("~@/assets/images/astro/astro_bg.png") no-repeat right bottom;
    border-top: 2px solid #89e8fa;
    border-left: 2px solid $green;

    scroll-snap-align: start; // to control scroll snapping

    h3 {
        position: absolute;
        top: 0;
        left: 0;
        margin: 0;
        padding: .25em 2.5em .25em .5em;
        background: #89e8fa;
        text-align: center;
        font-size: 1em;
        font-weight: normal;
        font-variant: small-caps;
        line-height: 1em;

        @include corner-bezel(0, 0, 1.5em, 0);
    }

    .card {
        margin: 0.4em 1em;
        width: 40%;
        align-items: center;

        img {
            width: fit-content;
            height: fit-content;
            margin: 0.8em 0;
        }

        p {
            font-size: 0.92em;
            letter-spacing: 0.03em;
            font-variant: small-caps;
            margin: 0.2em 0;
        }

        span {
            color: #357fdd;
            margin-right: 0.6em;;
        }
    }

    .analysis {
        padding: 1em 2em;

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
