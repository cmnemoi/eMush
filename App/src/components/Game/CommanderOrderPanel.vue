<template>
    <div class="terminal">
        <h1><img :src="getImgUrl('spot2.svg')">{{ $t('game.commanderOrderPanel.title') }}</h1>
        <div class="commander-order-terminal-container">
            <TerminalTips :content="$t('game.commanderOrderPanel.tips')" :player="player"/>
            <form class="chat-input">
                <textarea
                    v-model="mission"
                    class="text-input"
                    ref="input"
                    @keydown.enter.exact.prevent="breakLine"
                    @keydown.enter.ctrl.exact.prevent="breakLine"
                    @keydown.enter.shift.exact.prevent="breakLine"
                />
            </form>
            <div class="radio-buttons-container">
                <label
                    v-for="contactablePlayer in contactablePlayers"
                    :key="contactablePlayer.id"
                >
                    <input
                        type="radio"
                        v-model="selectedSubordinate"
                        :value="contactablePlayer.key"
                        :checked="selectedSubordinate === contactablePlayer.key"
                    />
                    {{ contactablePlayer.name }}
                </label>
            </div>
            <div class="send-button" v-if="selectedSubordinate && mission">
                <div class="actions">
                    <ActionButton
                        :key="commanderOrderAction.key"
                        :action="commanderOrderAction"
                        @click="executeTargetAction(commanderOrderAction)"
                    />
                </div>
            </div>
        </div>
        <button class="exit" v-if="commanderOrderPanelOpen" @click="closeCommanderOrderPanel">
            {{ $t('util.exit') }}
        </button>
    </div>
</template>

<script lang="ts">
import TerminalTips from "@/components/Game/Terminals/TerminalTips.vue";
import ActionButton from "@/components/Utils/ActionButton.vue";
import { Action } from "@/entities/Action";
import { Player } from "@/entities/Player";
import { ActionEnum } from "@/enums/action.enum";
import { getImgUrl } from "@/utils/getImgUrl";
import { defineComponent } from "vue";
import { mapActions, mapGetters } from "vuex";

export default defineComponent ({
    name: "CommanderOrderPanel",
    components : {
        ActionButton,
        TerminalTips
    },
    props: {
        player: {
            type: Player,
            required: true
        }
    },
    computed: {
        ...mapGetters({
            'commanderOrderPanelOpen': 'player/commanderOrderPanelOpen',
            'contactablePlayers': 'communication/contactablePlayers'
        }),
        commanderOrderAction(): Action {
            return this.player.getActionByKeyOrThrow(ActionEnum.COMMANDER_ORDER);
        }
    },
    methods: {
        ...mapActions({
            'executeAction': 'action/executeAction',
            'closeCommanderOrderPanel': 'player/closeCommanderOrderPanel',
            'getAvailaibleSubordinatesForMission': 'communication/getAvailaibleSubordinatesForMission'
        }),
        breakLine(): void {
            // find current caret position
            const element = this.$refs.input;
            const caretPos = element.selectionStart;

            // insert \n at the caret position
            element.value = element.value.slice(0, caretPos) + "\n" + element.value.slice(caretPos);

            // move caret to the end of the inserted "//"
            element.selectionStart = element.selectionEnd = caretPos + 1;
        },
        getImgUrl,
        async executeTargetAction(action: Action): Promise<void> {
            if(action.canExecute) {
                this.mission = this.mission.replace(/\n/g, "//");
                const params = { "subordinate": this.selectedSubordinate, "mission": this.mission };
                this.selectedSubordinate = '';
                this.mission = '';
                await this.executeAction({ target: null, action, params });
                await this.closeCommanderOrderPanel();
            }
        }
    },
    data() {
        return {
            ActionEnum,
            selectedSubordinate: "",
            mission: ""
        };
    },
    beforeMount() {
        this.getAvailaibleSubordinatesForMission(this.player);
    }
});
</script>

<style  lang="scss" scoped>

.actions {
    flex-direction: row;
    justify-content: space-evenly;
}

.chat-input {
    display: flex;
    position: relative;

    .text-input {
        position: relative;
        flex: 1;
        resize: vertical;
        min-height: 180px;
        padding: 3px 5px;
        font-style: italic;
        opacity: 0.85;
        box-shadow: 0 1px 0 white;
        border: 1px solid #aad4e5;
        border-radius: 3px;
        margin: 6px;

        &:active,
        &:focus {
            font-style: initial;
            opacity: 1;
        }
    }
}

.terminal {
    position: relative;
    flex-direction: column;
    width: 100%;
    max-width: $game-canvas-width;
    height: 460px;
    margin-bottom: 2em;
    color: $deepBlue;
    background: $brightCyan;

    //didn't @include the bevels because of the Exit button being outside the frame
    clip-path: polygon(6.5px 0, calc(100% - 6.5px ) 0, 100% 6.5px, 100% calc(100% + 2em), 0 calc(100% + 2em), 0 6.5px);

    h1 {
        font-size: 1rem;
        letter-spacing: 0.03rem;
        text-transform: uppercase;
        margin: 0;
        padding: 5px 10px;

        img { vertical-align: middle; }
    }

    & > div {
        overflow: auto;
        padding: 0 8px 5px;

        align-items: stretch;
        padding-bottom: .3em;

        @extend %game-scrollbar;
    }

    :deep(em) {
        color: $red;
    }

    .radio-buttons-container {
        margin-top: 0.2em;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: space-evenly;

        label {
            margin-right: 0.5em;
            margin-bottom: 0.2em;
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
}
</style>
