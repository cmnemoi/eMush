<template>
    <div class="command-terminal-container" v-if="player.terminal">
        <section>
            <h3>Orienter le Daedalus</h3>
            <p class="daedalus-current-orientation" v-html="formatContent(player.terminal.currentDaedalusOrientation)"></p>
            <div class="orientation-choice">
                <label class="orientation-choice-box-label" v-for="(availableOrientation, key) in player.terminal.availableDaedalusOrientations" :key="key">
                    <input class="orientation-choice-box"
                           type="radio"
                           name="orientation"
                           :value="availableOrientation"
                           v-model="chosenOrientation"
                           @click="reloadStorePlayer(player)">
                    {{ availableOrientation.name }}
                </label>
            </div>
            <div class="action">
                <ActionButton :action="orientateAction" 
                              :params="{'chosenOrientation': chosenOrientation.name}" 
                              @click="executeTargetAction(orientateAction, {'orientation': chosenOrientation.key})"/> 
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

        <!-- Pilgred section
        <section>
            <h3>Pilgred</h3>
            <div class="action">
                <button>
                    <span class="cost">1<img src="@/assets/images/pa.png" alt="ap"></span>
                    <span>Retourner sur Sol</span>
                </button>
            </div>
        </section> -->

        <section>
            <h3>Informations générales</h3>
            <p>Nous voyageons actuellement dans l'<strong>Espace proche</strong>.</p>
        </section>
    </div>
</template>

<script lang="ts">
import ActionButton from "@/components/Utils/ActionButton.vue";
import { Action } from "@/entities/Action";
import { Terminal } from "@/entities/Terminal";
import { defineComponent } from "vue";
import { mapActions } from "vuex";
import { formatText } from "@/utils/formatText";
import { Player } from "@/entities/Player";


export default defineComponent ({
    name: "CommandTerminal",
    components: { ActionButton },
    props: {
        player : {
            type: Player,
            required: true
        }
    },
    computed: {
        orientateAction() : Action {
            const action = this.player.terminal?.actions.find(action => action.key === 'change_daedalus_orientation');
            if (action === undefined) {
                throw new Error('Action not found');
            }
            return action;
        }
    },
    methods: {
        ...mapActions({
            'executeAction': 'action/executeAction',
            'reloadPlayer': 'player/reloadPlayer'
        }),
        async executeTargetAction(action: Action, params: any) {
            if (!action.canExecute){
                return;
            }
            await this.executeAction({ target: this.player.terminal, action, params });
        },
        async reloadStorePlayer(player: Player) {
            await this.reloadPlayer(player);
        },
        formatContent(text: string|null) {
            if (!text) return '';
            return formatText(text);
        }
    },
    data() {
        return {
            chosenOrientation: {
                key: 'north',
                name: 'Nord'
            }
        };
    },
});
</script>

<style  lang="scss" scoped>

section {
    @extend %terminal-section;
    flex-direction: column;
    padding: 1.5em .8em .8em;
    background-image: url("~@/assets/images/nav_bg.svg");

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