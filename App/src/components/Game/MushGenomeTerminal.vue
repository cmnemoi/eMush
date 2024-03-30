<template>
    <div class="mush-genome-terminal">
        <h1><img :src="getAssetUrl('spot2.svg')"> Génome</h1>
        <div class="tips">
            <input id="tips-comm" type="checkbox" name="tips-comm">
            <label for="tips-comm">
                Ce module permet de modifier votre propre génome pour améliorer vos performances Mush. Maîtriser certaines de ces actions peut s'avérer crutial pour la mission.
            </label>
        </div>
        <div>
            <p><em>Choisir mes compétences humaines</em></p>
            <ul class="skills">
                <li><img :src="getAssetUrl('skills/human/politician.png')" alt="politician"> Politician</li>
                <li><img :src="getAssetUrl('skills/human/crazy_eye.png')" alt="Crazy Eye"> Crazy Eye</li>
                <li><img :src="getAssetUrl('skills/human/mankind_only_hope.png')" alt="Mankind's only hope"> Mankind's only hope</li>
                <li><button>Choisir une compétence</button></li>
            </ul>
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
    name: "MushGenomeTerminal",
    props: {
    },
    methods: {
        getAssetUrl
    }
});
</script>

<style  lang="scss" scoped>

.mush-genome-terminal {
    position: relative;
    flex-direction: column;
    width: 424px;
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

.skills {
    flex-wrap: wrap;

    li {
        display: flex;
        align-items: center;
        margin: 0 .8em .8em 0;
        padding: .1em .4em;
        background: #a6eefb;
        border: 1px solid #84e100;
        border-radius: 3px;

        button { @include button-style(); }
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
