<template>
    <div class="command-terminal-container" v-if="terminal">
        <section style="display:none;">
            <h3>{{ terminal.sectionTitles?.orientateDaedalus }}</h3>
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

        <section v-if="advanceDaedalusAction">
            <h3>{{ terminal.sectionTitles?.moveDaedalus }}</h3>
            <div class="move-status" v-if="terminal.infos.advanceDaedalusStatus">
                <img src="@/assets/images/att.png" alt="warning" v-if="terminal.infos.advanceDaedalusStatus.isWarning()">
                <img src="@/assets/images/info.png" alt="info" v-else> 
                <p v-html="formatText(terminal.infos.advanceDaedalusStatus.text)"></p>
            </div>
            <div class="action">
                <ActionButton
                    :cssClass="'wide'"
                    :key="advanceDaedalusAction.key"
                    :action="advanceDaedalusAction"
                    @click="executeTargetAction(target, advanceDaedalusAction)"
                />
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
            <h3>{{ terminal.sectionTitles?.generalInformations }}</h3>
            <p v-html="formatText(terminal.infos?.difficulty)"></p>
        </section>
    </div>
</template>

<script lang="ts">
import { Terminal } from "@/entities/Terminal";
import { defineComponent } from "vue";
import { formatText } from "@/utils/formatText";
import ActionButton from "@/components/Utils/ActionButton.vue";
import { Action } from "@/entities/Action";
import { ActionEnum } from "@/enums/action.enum";
import { mapActions } from "vuex";


export default defineComponent ({
    name: "CommandTerminal",
    computed: {
        advanceDaedalusAction(): Action {
            const action = this.terminal?.actions.find(action => action.key === ActionEnum.ADVANCE_DAEDALUS);
            if (!action) throw new Error(`No advance_daedalus action found for terminal ${this.terminal?.key}`);

            return action;
        },
        target(): Terminal {
            return this.terminal;
        }
    },
    props: {
        terminal: {
            type: Terminal,
            required: true
        }
    },
    methods: {
        ...mapActions({
            'executeAction': 'action/executeAction',
        }),
        async executeTargetAction(target: Terminal, action: Action): Promise<void> {
            if(action.canExecute) {
                await this.executeAction({ target, action });
            }
        },
        formatText(text: string | null): string {
            if (!text)
                return '';
            return formatText(text);
        }
    },
    data() {
        return {
            ActionEnum,
            chosenOrientation: 'Nord'
        };
    },
    components: { ActionButton }
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
}

</style>