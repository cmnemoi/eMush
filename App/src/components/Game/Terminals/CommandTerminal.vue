<template>
    <div class="command-terminal-container">
        <section>
            <h3>Orienter le Daedalus</h3>
            <p class="daedalus-current-orientation">Le Daedalus pointe actuellement vers : <strong>Nord</strong>.</p>
            <div class="orientation-choice">
                <label class="orientation-choice-box-label">
                    <input class="orientation-choice-box"
                           type="radio"
                           name="orientation"
                           value="Nord"
                           v-model="chosenOrientation">
                    Nord
                </label>
                <label class="orientation-choice-box-label">
                    <input class="orientation-choice-box"
                           type="radio"
                           name="orientation"
                           value="Est"
                           v-model="chosenOrientation">
                    Est
                </label>
                <label class="orientation-choice-box-label">
                    <input class="orientation-choice-box"
                           type="radio"
                           name="orientation"
                           value="Sud"
                           v-model="chosenOrientation">
                    Sud
                </label>
                <label class="orientation-choice-box-label">
                    <input class="orientation-choice-box"
                           type="radio"
                           name="orientation"
                           value="Ouest"
                           v-model="chosenOrientation">
                    Ouest
                </label>
            </div>
            <div class="action">
                <button>
                    <span class="cost">1<img src="@/assets/images/pa.png" alt="ap"></span>
                    <span>Changer l'orientation : {{ chosenOrientation }}</span>
                </button>
            </div>
        </section>

        <section>
            <h3>Déplacer le Daedalus</h3>
            <div class="move-status">
                <!-- FOR WARNING ICON: <img src="@/assets/images/att.png" alt="warning"> -->
                <img src="@/assets/images/info.png" alt="info">
                <p>Il n'y a pas de fuel dans la Chambre de Combustion ! Le voyage infra-luminique est impossible.</p>
            </div>
            <div class="action">
                <button>
                    <span class="cost">1<img src="@/assets/images/pa.png" alt="ap"></span>
                    <span>Voyager</span>
                </button>
            </div>
        </section>

        <section>
            <h3>Informations générales</h3>
            <p>Nous voyageons actuellement dans l'<strong>Espace proche</strong>.</p>
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

section {
    @extend %terminal-section;
    flex-direction: column;
    padding: 1.5em .8em .8em;
    background-image: url("~@/assets/images/nav_bg.png");

    & > p, & > div {
        margin: 0.8em 0 0;
        width: 100%;
    }

    p { text-align: left; }
}

.orientation-choice {
    margin: 0.6em 0 0;
    width: 100%;
    flex-direction: row;
    justify-content: space-evenly;

    .orientation-choice-box {
        margin-right: .2em;
    }

    .orientation-choice-box-label {
        margin: 0 1em;
        cursor: pointer;

        & > * { cursor: pointer; }
    }

    p {
        padding-top: 0.8em;
        padding-left: 0.5em;
    }
}

.move-status {
    flex-direction: row;
    align-items: center;
    gap: 0.6em;

    img {
        width: fit-content;
        height: fit-content;
    }

    p {
        font-style: italic;
        margin: 0;
    }
}

.action {
    flex-direction: row;
    justify-content: space-evenly;
    margin-top: 0.6em;

    button {
        @include button-style;
        min-width: 10em;
    }
}

</style>