<template>
    <div class="trading-terminal">
        <h1><img :src="getAssetUrl('spot2.svg')"> Radio ondes courtes</h1>
        <div class="tips">
            <input id="tips-comm" type="checkbox" name="tips-comm">
            <label for="tips-comm">
                Toute communication commence par <strong>établir une liaison</strong>.
                <br><br>
                Cette action simple peut être réalisée par n'importe quel membre de l'équipage. La liaison apporte des points de moral à toute l'équipe et débloque des nouvelles actions pour le Responsable Comm.
                <br><br>
                Si le contact échoue, au moins ça <strong>améliorera la qualité du signal</strong> et augmentera les chances de contact lors du prochain essai.
            </label>
        </div>
        <p><strong>Des marchands extra-terrestres sont en orbite autour du Daedalus, NERON vous traduit le message transmis.</strong></p>
        <div class="tips">
            <input id="tips-trading" type="checkbox" name="tips">
            <label for="tips-trading">
                Les échanges se font avec des objets en bon état accessibles dans les <strong>Stockages</strong>.
                <br><br>
                Les membres d'équipage échangés sont ceux présent dans ces pièces, si vous êtes humain, seuls les membres "Inactifs" sont échangeables.
                <br><br>
                Si vous validez un échange sans remplir les conditions, le marchand déguerpira.
                <br><br>
                Si vous ouvrez le feu sur un son vaisseau, le marchand déguerpira.
            </label>
        </div>
        <div class="tips trading-objects">
            <input id="trading-objects" type="checkbox" name="tips">
            <label for="trading-objects">
                <strong>Echangeable</strong>.
                <br><br>
                bla bli bla.
            </label>
        </div>

        <hr>

        <section class="trade">
            <div class="proposal">
                <img :src="getAssetUrl('trading.png')">
                <p>Aloha, vos organismes sont vraiment passionnant surtout la métanisation de vos déchets organiques, incroyable... Nous vous échangeons un de vos amis contre des réserves d'oxygène conséquentes !</p>
            </div>
            <button class="choice">
                <h3>Vendu !</h3>
                <p>Donner 1 équipier au hasard... Vous rapportera : 10xO2</p>
            </button>
            <button class="choice">
                <h3>Jamais !</h3>
            </button>
        </section>
        <section class="trade">
            <div class="proposal">
                <img :src="getAssetUrl('trading.png')">
                <p>Aloha, vos organismes sont vraiment passionnant surtout la métanisation de vos déchets organiques, incroyable... Nous vous échangeons un de vos amis contre des réserves d'oxygène conséquentes !</p>
            </div>
            <button class="choice">
                <h3>Vendu !</h3>
                <p>Donner 1 équipier au hasard... Vous rapportera : 10xO2</p>
            </button>
            <button class="choice">
                <h3>Jamais !</h3>
            </button>
        </section>
        <button class="exit">
            Quitter
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { getAssetUrl } from "@/utils/getAssetUrl";

export default defineComponent ({
    name: "TradingTerminal",
    props: {
    },
    methods: {
        getAssetUrl
    }
});
</script>

<style  lang="scss" scoped>

.trading-terminal {
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

    hr {
        width: 100%;
        border: transparent;
        border-bottom: 2px dotted $deepBlue;
    }
}

.tips { @extend %retracted-tips; }

.trading-objects {
    background-image: url("/src/assets/images/sensor_bg.svg");
    background-repeat: no-repeat;
    background-position: right bottom;
}

.trade {
    align-items: stretch;
    padding-bottom: 1.4em;

    .proposal, .choice {
        flex-direction: column;
        margin: .2em 0;
        padding: .4em;
        border-radius: 4px;
        font-weight: bold;
    }

    .proposal {
        display: block;
        background: $lightCyan url("/src/assets/images/sensor_bg.svg") no-repeat right bottom;

        img {
            width: 85px;
            float: right;
        }

        p { margin: 0; }
    }

    .choice {
        color: $deepBlue;
        background: darken(adjust-hue($lightCyan, 3), 8);

        transition: background .2s;

        h3 {
            margin: 0;
            font-size: 1.2em;
        }
        p {
            margin: .2em .6em;
            font-size: .8em;
        }

        &:hover, &:focus, &:focus-within {
            background: darken(adjust-hue($lightCyan, 5), 13);
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
