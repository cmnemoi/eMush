<template>
    <div class="command-terminal-container">
        <section class="planet">
            <h3>Orienter le Daedalus</h3>
            <div class="analysis">
                <p class="daedalus-current-orientation">Le Daedalus pointe actuellement vers : <strong>Nord</strong>.</p>
                <div class="orientation-choice">
                    <label class="orientation-button-label">
                        <input class="orientation-button"
                               type="radio"
                               name="orientation"
                               value="Nord"
                               v-model="chosenOrientation">
                        Nord
                    </label>
                    <label class="orientation-button-label">
                        <input class="orientation-button"
                               type="radio"
                               name="orientation"
                               value="Est"
                               v-model="chosenOrientation">
                        Est
                    </label>
                    <label class="orientation-button-label">
                        <input class="orientation-button"
                               type="radio"
                               name="orientation"
                               value="Sud"
                               v-model="chosenOrientation">
                        Sud
                    </label>
                    <label class="orientation-button-label">
                        <input class="orientation-button"
                               type="radio"
                               name="orientation"
                               value="Ouest"
                               v-model="chosenOrientation">
                        Ouest
                    </label>
                </div>
                <div class="actions">
                    <button>
                        <span class="cost">1<img src="@/assets/images/pa.png" alt="ap"></span>
                        <span>Changer l'orientation : {{ chosenOrientation }}</span>
                    </button>
                </div>
            </div>
        </section>

        <section class="planet">
            <h3>Déplacer le Daedalus</h3>
            <div class="analysis">
                <em>Il n'y a pas de fuel dans la Chambre de Combustion ! Le voyage infra-luminique est impossible.</em>
                <div class="actions">
                    <button>
                        <span class="cost">1<img src="@/assets/images/pa.png" alt="ap"></span>
                        <span>Voyager</span>
                    </button>
                </div>
            </div>
        </section>

        <section class="planet">
            <div class="analysis">
                <h3>Informations générales</h3>
                <p>Nous voyageons actuellement dans l'<strong>Espace proche</strong>.</p>
            </div>
        </section>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent ({
    name: "CommandTerminal",
    data() {
        return {
            chosenOrientation: 'Nord'
        };
    },
});
</script>

<style  lang="scss" scoped>

.planet {
    @extend %terminal-section;
    background-image: url("~@/assets/images/nav_bg.png");

    .analysis {
        flex-direction: column;
        margin: .2em 0;
        padding: .9em;
        // .daedalus-current-orientation {
        //     margin-top: 1.5em;
        // }
        .orientation-choice {
            flex-direction: row;
            margin-bottom: .5em;
            .orientation-button {
                margin: 0 .2em;
            }

            .orientation-button-label {
                margin: 0 1em;
            }
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