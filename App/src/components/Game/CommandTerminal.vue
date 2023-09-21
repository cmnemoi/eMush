<template>
    <div class="command-terminal">
        <h1><img src="@/assets/images/spot2.svg"> Terminal de commandement</h1>
        <div class="tips">
            <input id="tips-command" type="checkbox" name="tips-command">
            <label for="tips-command">
                En tant que <em>Commandant</em> vous êtes le seul à avoir accès à ce terminal.
                <br><br>
                Le déplacement du vaisseau est nécessaire pour <strong>aller vers une planète</strong> et peut être utile pour se débarrasser des hunters.
                <br><br>
                Pour aller vers une planète il faut <strong>connaître l'orientation et le nombre d'unités de fuel</strong>. Ces deux informations sont fournies depuis le <em>Terminal Astro</em>. Avec ces informations, quelqu'un doit d'abord remplir la chambre de combustion avec la quantité correcte de Fuel. Ensuite, orientez le Daedalus dans la bonne direction et lancez le déplacement !
                <br><br>
                Si le travail a été bien fait, le Daedalus se placera en orbite d'une planète qui pourra être explorée par l'équipage et ainsi obtenir des nouvelles ressources !
            </label>
        </div>

        <section class="section">
            <div class="sub-section">
                <p>Le Daedalus pointe actuellement vers <strong>{{ orientation }}</strong>.</p>
                <div class="orientation-choice">
                    <label class="orientation-button-label">
                        <input class="orientation-button"
                               type="radio"
                               name="orientation"
                               value="Nord"
                               v-model="orientation">
                        Nord
                    </label>
                    <label class="orientation-button-label">
                        <input class="orientation-button"
                               type="radio"
                               name="orientation"
                               value="Est"
                               v-model="orientation">
                        Est
                    </label>
                    <label class="orientation-button-label">
                        <input class="orientation-button"
                               type="radio"
                               name="orientation"
                               value="Sud"
                               v-model="orientation">
                        Sud
                    </label>
                    <label class="orientation-button-label">
                        <input class="orientation-button"
                               type="radio"
                               name="orientation"
                               value="Ouest"
                               v-model="orientation">
                        Ouest
                    </label>
                </div>
                <button class="action-button" v-text="'Changer l\'orientation: ' + orientation"></button>
            </div>
        </section>
        <section class="section">
            <div class="sub-section">
                <p>Il n'y a pas de fuel dans la <em>Chambre de Combustion</em> ! Le voyage infra-luminique est impossible.</p>
                <button class="action-button">Voyager</button>
            </div>
        </section>
        <section class="section">
            <div class="sub-section">
                <p>Nous voyageons maintenant dans l'<strong>Espace Infini</strong>.</p>
            </div>
        </section>
        <button class="exit">
            Quitter
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent ({
    name: "CommandTerminal",
    data() {
        return {
            orientation: 'Nord'
        };
    },
    props: {
    }
});
</script>

<style  lang="scss" scoped>

.command-terminal {
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

    em {
        color: $red;
    }
}

.tips { @extend %retracted-tips; }

.section {
    align-items: stretch;
    padding-bottom: 1.4em;

    .sub-section {
        flex-direction: column;
        margin: .2em 0;
        padding: .4em;
        border-radius: 4px;
    }

    .sub-section {
        display: block;
        background: $lightCyan url("~@/assets/images/nav_bg.png") no-repeat right bottom;

        img {
            width: 85px;
            float: right;
        }

        p { margin: 0; }

        .orientation-choice {
            flex-direction: row;
            margin: .2em 0;
            padding: .4em;

            .orientation-button {
                margin: 0 .2em;
            }

            .orientation-button-label {
                margin: 0 1.7em;
            }
        }

        .action-button {
            margin: .7em auto;
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
