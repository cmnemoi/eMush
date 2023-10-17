<template>
    <div class="panel">
        <TerminalTips />
        <section class="planet">
            <h3>Goulidon</h3>
            <span class="estimate">Retour estimé dans: 40 min.</span>
            <div class="card">
                <img class="planet-img" src="@/assets/images/astro/planet_unknown.png">
                <ul class="crew">
                    <li>
                        <img src="@/assets/images/char/body/finola.png" alt="Finola">
                        <p><img src="@/assets/images/lp.png"> 12</p>
                    </li>
                    <li>
                        <img src="@/assets/images/char/body/terrence.png" alt="Terrence">
                        <p><img src="@/assets/images/lp.png"> 4</p>
                    </li>
                    <li>
                        <img src="@/assets/images/char/body/chun.png" alt="Chun">
                        <p><img src="@/assets/images/dead.png"></p>
                    </li>
                </ul>
            </div>
            <span class="info-trigger" @click="show = !show"><img src="@/assets/images/down.png" :class="{ revert: show }"> informations collectées...</span>
            <ul class="analysis" v-if="show">
                <li><img src="@/assets/images/astro/cold.png"></li>
                <li class="unexplored"><img src="@/assets/images/astro/ocean.png"></li>
                <li><img src="@/assets/images/astro/forest.png"></li>
                <li class="unexplored"><img src="@/assets/images/astro/cold.png"></li>
                <li><img src="@/assets/images/astro/ocean.png"></li>
                <li><img src="@/assets/images/astro/unknown.png"></li>
                <li class="unexplored"><img src="@/assets/images/astro/unknown.png"></li>
                <li><img src="@/assets/images/astro/unknown.png"></li>
                <li class="unexplored"><img src="@/assets/images/astro/forest.png"></li>
                <li class="unexplored"><img src="@/assets/images/astro/cold.png"></li>
                <li class="unexplored"><img src="@/assets/images/astro/cold.png"></li>
            </ul>
        </section>
        <section class="logs">
            <div class="event">
                <span class="estimate"><img src="@/assets/images/casio.png"> 09m03s</span>
                <img src="@/assets/images/astro/forest.png">
                <div>
                    <h3>Ruminant - Provision</h3>
                    <p class="flavor">Vous rencontrez une myriade de petits rongeurs. Dans la panique générale vous parvenez à attraper l'un d'entre eux.</p>
                    <p class="details">Vous gagnez 3 Steacks Aliens.</p>
                </div>
                <p class="details">+1 car l'expédition dispose de la compétence : Survie</p>
            </div>
            <div class="event">
                <img src="@/assets/images/astro/forest.png">
                <div>
                    <h3>Ruminant - Provision</h3>
                    <p class="flavor">Vous rencontrez une myriade de petits rongeurs. Dans la panique générale vous parvenez à attraper l'un d'entre eux.</p>
                    <p class="details">Vous gagnez 3 Steacks Aliens.</p>
                </div>
                <p class="details">+1 car l'expédition dispose de la compétence : Survie</p>
            </div>
        </section>
    </div>
</template>

<script lang="ts">
import TerminalTips from "@/components/Game/Terminals/TerminalTips.vue";
import { defineComponent } from "vue";

export default defineComponent ({
    name: "ExpeditionPanel",
    components: {
        TerminalTips
    },
    props: {
    },
    data() {
        return {
            show: false,
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

</style>