<template>
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
                    <li><img src="@/assets/images/astro/unknown.png"></li>
                    <li><img src="@/assets/images/astro/unknown.png"></li>
                    <li><img src="@/assets/images/astro/unknown.png"></li>
                    <li><img src="@/assets/images/astro/forest.png"></li>
                    <li><img src="@/assets/images/astro/cold.png"></li>
                    <li><img src="@/assets/images/astro/cold.png"></li>
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

        <section class="planet unknown">
            <h3>???</h3>
            <div class="card">
                <img src="@/assets/images/astro/planet_unknown.png">
            </div>
            <div class="analysis">
                <!-- <p>Scanning impossible...</p> -->
                <p>80% <img src="@/assets/images/astro/thermosensors.png"></p>
                <div class="actions">
                    <button>
                        <span class="cost">2<img src="@/assets/images/pa.png" alt="ap"></span>
                        <span>Scan (80%)</span>
                    </button>
                </div>
            </div>
        </section>
    </div>
</template>

<script lang="ts">
import { Terminal } from "@/entities/Terminal";
import { defineComponent } from "vue";

export default defineComponent ({
    name: "AstroTerminal",
    props: {
        terminal: {
            type: Terminal,
            required: true
        }
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