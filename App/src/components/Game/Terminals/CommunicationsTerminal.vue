<template>
    <div class="terminal-container" v-if="terminal">
        <button v-if="terminal.infos.seeCommunications" @click="toggleTradeView">{{ tradeViewEnabled ? terminal.infos.seeCommunications : terminal.infos.seeTrades }}</button>
        <div class="upper-container" v-if="!tradeViewEnabled">
            <section class="left-section">
                <div class="sol-link container">
                    <h3 class="title">
                        <img :src="getImgUrl('spot2.svg')" alt="spot"/>
                        {{ terminal.sectionTitles?.contact }}
                    </h3>
                    <div class="link-infos">
                        <div class="link-infos-img">
                            <img
                                :src="getImgUrl(`sensor0${sensorFramesCount}.png`)"
                                class="sensor-frame"
                                alt="connection status"
                            />
                        </div>
                        <div class="link-infos-content">
                            <p class="link-strength text">
                                {{ terminal.infos.linkStrength }}
                            </p>
                            <p v-if="!establishLinkWithSolAction" class="link-established text">
                                {{ terminal.infos.linkEstablished }}
                            </p>
                        </div>
                    </div>
                    <ActionButton
                        v-if="establishLinkWithSolAction"
                        :key="establishLinkWithSolAction.name || ''"
                        class="terminal-button"
                        :action="establishLinkWithSolAction"
                        @click="executeTargetAction(terminal, establishLinkWithSolAction)"
                    />
                </div>
                <div class="neron container">
                    <h3 class="title">
                        <img :src="getImgUrl('spot2.svg')" alt="spot"/>
                        {{ terminal.sectionTitles?.neronVersion }}
                    </h3>
                    <div class="neron-infos">
                        <div class="neron-infos-img">
                            <img :src="getImgUrl('neron.png')" alt="neron"/>
                        </div>
                        <div class="neron-infos-content">
                            <p class="neron-version text">
                                {{ terminal.infos.neronUpdateStatus }}
                            </p>
                            <div class="neron-progress-bar-container">
                                <span class="neron-version-progress-bar" :style="{ width: `${neronMinorVersion}%` }" />
                                <span class="progress-text">{{ neron }}</span>
                            </div>
                        </div>
                    </div>
                    <ActionButton
                        v-if="upgradeNeron"
                        :key="upgradeNeron.name || ''"
                        class="terminal-button"
                        :action="upgradeNeron"
                        @click="executeTargetAction(terminal, upgradeNeron)"
                    />
                </div>
            </section>
            <section class="right-section" v-if="!tradeViewEnabled">
                <div class="xyloph container">
                    <h3 class="title">
                        <img :src="getImgUrl('spot2.svg')" alt="spot"/>
                        {{ terminal.sectionTitles?.xylophDb }}
                    </h3>
                    <div class="xyloph-grid">
                        <Tippy v-for="(xylophEntry, index) in terminal.xylophEntries" :key="index" tag="span">
                            <img :src="getXylophEntryImage(xylophEntry)" alt="Xyloph entry" class="xyloph-entry" />
                            <template #content>
                                <h1 class="xyloph-entry-name">{{ xylophEntry.name }}</h1>
                                <p class="xyloph-entry-description" v-html="formatText(xylophEntry.description)" />
                            </template>
                        </Tippy>
                    </div>
                    <ActionButton
                        :key="contactXylophAction.name || ''"
                        class="terminal-button"
                        :action="contactXylophAction"
                        @click="executeTargetAction(terminal, contactXylophAction)"
                        v-if="contactXylophAction.name"
                    />
                </div>
            </section>
        </div>
        <div class="rebel-bases container" v-if="!tradeViewEnabled">
            <h3 class="title">
                <img :src="getImgUrl('spot2.svg')" alt="spot"/>
                {{ terminal.sectionTitles?.rebelBasesNetwork }}
            </h3>
            <div class="rebel-bases-grid">
                <Tippy
                    tag="div"
                    v-for="(base, index) in terminal.rebelBases"
                    :key="index"
                    class="rebel-base-item"
                    :class="{
                        'not-contacted' : base.name === '???',
                        'contacting' : base.isContacting,
                        'contacted' : base.name !== '???',
                        'selected' : base.key === selectedRebelBase,
                        'disabled' : base.isContacting
                    }"
                    @click="selectRebelBase(base)"
                >
                    <p class="base-name">{{ base.name }}</p>
                    <p :class="{ 'base-signal' : base.isContacting, 'base-signal-lost' : base.isLost }" v-if="base.isContacting || base.isLost">{{ base.signal }}</p>
                    <img :src="getImgUrl(`rebel_bases/${base.key}.png`)" :alt="base.key" class="base-image" />
                    <template #content>
                        <h1 class="base-hover-name" v-html="formatText(base.hoverName)" />
                        <p class="base-description" v-html="formatText(base.description)" />
                    </template>
                </Tippy>
            </div>
            <ActionButton
                :key="decodeRebelSignalAction.name || ''"
                class="terminal-button"
                :action="decodeRebelSignalAction"
                @click="executeTargetAction(terminal, decodeRebelSignalAction, { rebel_base: selectedRebelBase })"
            />
        </div>
        <section class="trade-view" v-if="tradeViewEnabled">
            <div class="trade-messages">
                <p class="cannot-trade-under-attack" v-if="terminal.infos.cannotTradeUnderAttack">
                    {{ terminal.infos.cannotTradeUnderAttack }}
                </p>

                <div
                    v-for="trade in terminal.trades"
                    :key="trade.id"
                    class="trade-message"
                    v-else
                >
                    <div class="transport-message">
                        <p v-html="formatText(trade.description)" />
                        <div class="trade-image" v-if="trade.options && trade.options.length > 0">
                            <img :src="getImgUrl(`hunters/${trade.image}.png`)" :alt="trade.image" />
                        </div>
                    </div>

                    <div class="trade-options-container">
                        <Tippy
                            v-for="option in trade.options"
                            :key="option.id"
                            class="trade-response"
                            :class="{
                                'selected': selectedTradeOption && selectedTradeOption.option.id === option.id,
                                'disabled': option.tradeConditionsAreNotMet
                            }"
                            @click="selectTradeOption(trade, option)"
                        >
                            <p class="trade-option-name" v-html="formatText(option.name)" />
                            <p class="trade-details" v-html="formatText(option.description)" />
                            <template #content v-if="option.tradeConditionsAreNotMet">
                                <h1 v-html="formatText(option.name)" />
                                <p class="trade-not-available" v-html="formatText(option.tradeConditionsAreNotMet)"/>
                            </template>
                        </Tippy>
                        <Tippy
                            class="trade-response"
                            :class="{ 'selected': refuseTradeSelected.tradeId === trade.id }"
                            @click="selectRefuseTrade(trade.id)"
                        >
                            <p class="trade-option-name" v-html="formatText(terminal.infos.never)" />
                        </Tippy>
                    </div>

                    <ActionButton
                        v-if="selectedTradeOption && selectedTradeOption.trade.id === trade.id"
                        :key="acceptTradeAction.name || ''"
                        class="terminal-button"
                        :action="acceptTradeAction"
                        @click="executeTargetAction(terminal, acceptTradeAction, { tradeOptionId: selectedTradeOption.option.id })"
                    />
                    <ActionButton
                        v-if="refuseTradeSelected.tradeId === trade.id"
                        :key="refuseTradeAction.name || ''"
                        class="terminal-button"
                        :action="refuseTradeAction"
                        @click="executeTargetAction(terminal, refuseTradeAction, { tradeId: trade.id })"
                    />
                </div>
            </div>
        </section>
    </div>
</template>

<script lang="ts">
import { Terminal } from "@/entities/Terminal";
import { defineComponent } from "vue";
import { formatText } from "@/utils/formatText";
import { getImgUrl } from "@/utils/getImgUrl";
import { ActionEnum } from "@/enums/action.enum";
import { Action } from "@/entities/Action";
import { RebelBase } from "@/entities/RebelBase";
import { XylophEntry } from "@/entities/XylophEntry";
import { Trade, TradeOption } from "@/entities/Trade";
import ActionButton from "@/components/Utils/ActionButton.vue";
import { mapActions } from "vuex";
import { Tippy } from "vue-tippy";

interface SelectedTradeOption {
    trade: Trade;
    option: TradeOption;
}

export default defineComponent({
    name: "CommunicationsTerminal",
    components: {
        ActionButton
    },
    computed: {
        acceptTradeAction(): Action {
            return this.terminal.getActionByKeyOrThrow(ActionEnum.ACCEPT_TRADE);
        },
        refuseTradeAction(): Action {
            return this.terminal.getActionByKeyOrThrow(ActionEnum.REFUSE_TRADE);
        },
        establishLinkWithSolAction(): Action | null {
            return this.terminal.getActionByKey(ActionEnum.ESTABLISH_LINK_WITH_SOL);
        },
        upgradeNeron(): Action {
            return this.terminal.getActionByKeyOrThrow(ActionEnum.UPGRADE_NERON);
        },
        decodeRebelSignalAction(): Action {
            const action = new Action();
            action.load(this.terminal.getActionByKeyOrThrow(ActionEnum.DECODE_REBEL_SIGNAL));

            if (this.selectedRebelBase === '' && action.canExecute) {
                action.canExecute = false;
                action.description = this.terminal.infos.selectRebelBaseToDecode;
            }

            return action;
        },
        contactXylophAction(): Action {
            return this.terminal.getActionByKeyOrThrow(ActionEnum.CONTACT_XYLOPH);
        },
        neron(): string {
            return this.terminal.sectionTitles?.neronVersion?.split('.')[0].split(' ')[0] || '';
        },
        neronMinorVersion(): integer {
            return Number(this.terminal.sectionTitles?.neronVersion?.split('.')[1]);
        },
        selectedRebelBase(): string {
            return this.d_selectedRebelBase;
        },
        sensorFramesCount(): integer {
            const linkStrength = this.terminal.infos?.linkStrength;
            if (!linkStrength) {
                return 0;
            }

            const match = linkStrength.match(/\d+/);
            if (!match) {
                return 0;
            }

            const strengthValue = parseInt(match[0], 10);
            if (strengthValue <= 25) {
                return 1;
            } else if (strengthValue <= 50) {
                return 2;
            } else if (strengthValue <= 75) {
                return 3;
            } else {
                return 4;
            }
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
            'executeAction': 'action/executeAction'
        }),
        async executeTargetAction(target: Terminal, action: Action, params: object = {}): Promise<void> {
            if (action.canExecute) {
                await this.executeAction({ target, action, params });
            }
        },
        formatText,
        getImgUrl,
        getXylophEntryImage(xylophEntry: XylophEntry): string {
            return xylophEntry.isDecoded ? getImgUrl('bdd.png') : getImgUrl('bdd_off.png');
        },
        selectRebelBase(base: RebelBase) {
            if (!base.isContacting) {
                return;
            }

            this.d_selectedRebelBase = base.key;
            this.decodeRebelSignalAction = this.terminal.getActionByKeyOrThrow(ActionEnum.DECODE_REBEL_SIGNAL);
        },
        toggleTradeView() {
            this.tradeViewEnabled = !this.tradeViewEnabled;
            this.selectedTradeOption = null;
            this.refuseTradeSelected.tradeId = 0;
        },
        selectTradeOption(trade: Trade, option: TradeOption) {
            if (option.tradeConditionsAreNotMet) {
                return;
            }

            this.selectedTradeOption = { trade, option };
            // Reset refuse trade selection when a trade option is selected
            this.refuseTradeSelected.tradeId = 0;
        },
        selectRefuseTrade(tradeId: number) {
            // Reset selected trade option when refuse is selected
            this.selectedTradeOption = null;
            this.refuseTradeSelected.tradeId = tradeId;
        }
    },
    data() {
        return {
            d_selectedRebelBase: '',
            tradeViewEnabled: false,
            selectedTradeOption: null as SelectedTradeOption | null,
            refuseTradeSelected: {
                tradeId: 0
            }
        };
    }
});
</script>

<style lang="scss" scoped>
/* Layout */
.upper-container {
    display: flex;
    flex-direction: row;
}

.left-section {
    display: flex;
    flex-direction: column;
}

.right-section {
    display: flex;
    flex-direction: column;
}

/* Common styles */
p {
    text-transform: uppercase;
}

.text {
    color: #75C7E5;
    text-align: center;
}

/* Container styles */
.container {
    background-color: #A5EEFB;
    margin: 2px;
    padding: 5px;
    border-radius: 5px;

    .title {
        text-transform: uppercase;
        margin: 0;
        padding: 5px;
    }
}

/* Specific container styles */
.xyloph {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.xyloph .xyloph-grid {
    flex: 1;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-template-rows: repeat(3, 1fr);
}

.xyloph-entry {
    &:hover {
        background-color: $green;
        border-radius: 5px;
        cursor: pointer;
    }
}

/* Info sections shared styles */
.link-infos,
.neron-infos {
    display: flex;
    flex-direction: row;
    margin: 0;
    padding: 0;

    &-img {
        margin: 0;
        padding: 0;
    }

    &-content {
        margin: 0;
        padding: 0;
        align-items: center;
        flex: 1;

        .terminal-button {
            margin: 5px;
            padding: 0;
            width: 80%;
        }

        .text {
            margin: 0;
            padding: 3px;
        }
    }
}

/* Sol link specific styles */
.link-infos {
    background-image: url("/src/assets/images/sensor_bg.svg");
    background-repeat: no-repeat;
    background-position: right bottom;
    margin-left: 10px;
}

/* NERON specific styles */

.neron-infos {
    background-image: url("/src/assets/images/neron_bg.png");
    background-repeat: no-repeat;
    background-position: right bottom;
}

.neron-infos-content {
    .neron-progress-bar-container {
        width: 90%;
        height: 25px;
        background-color: #2C569A;
        border: 2px solid #1D4176;
        border-radius: 5px;
        overflow: hidden;
        position: relative;

        .neron-version-progress-bar {
            height: 100%;
            background-color: #75DF00;
            position: relative;
        }

        .progress-text {
            position: absolute;
            left: 8px;
            top: 50%;
            transform: translateY(-50%);
            color: #2C6800;
            font-weight: bold;
            white-space: nowrap;
            pointer-events: none;
            clip-path: inset(0 calc(100% - var(--progress-width)) 0 0);
            --progress-width: v-bind('neronMinorVersion * 4 + "%"');
        }
    }
}

/* Rebel bases grid */
.rebel-bases-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    grid-template-rows: repeat(2, 1fr);
    gap: 5px;
}

/* Xyloph grid */
.xyloph-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    grid-template-rows: repeat(3, 1fr);
    column-gap: 10px;
    margin-top: 5px;
    margin-bottom: 5px;
}

.rebel-base-item {
    background-color: #81E0FD;
    text-align: center;
    opacity: 0.5;
    position: relative;

    &.contacting, &.contacted {
        opacity: 1;
    }

    &.contacting {
        &:hover,
        &.selected {
            background-color: $green;
            cursor: pointer;
        }
    }

    .base-signal {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 2;
        margin: 0;
        font-size: 2rem;
        font-weight: bold;
        text-shadow: 1px 1px 5px white;
    }

    .base-signal-lost {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 2;
        margin: 0;
        text-shadow: 1px 1px 2px white;
    }

    .base-image {
        position: relative;
        z-index: 1;
        max-width: 100%;
    }
}

.base-description, .xyloph-entry-description, .trade-not-available {
    text-transform: none;
}

/* Trade view styles */
.trade-view {
    display: flex;
    flex-direction: column;
    height: 100%;
    background-color: #D8F7FF;
    border-radius: 5px;
    overflow: hidden;
    color: #003366;
}

.shortwave-radio {
    background-color: #A5EEFB;
    padding: 10px;
    border-radius: 5px 5px 0 0;

    .title {
        text-transform: uppercase;
        font-weight: bold;
        margin: 0 0 10px 0;
        color: #003366;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .info-banner {
        background-color: #81E0FD;
        padding: 8px 15px;
        margin: 5px 0;
        border-radius: 3px;
        display: flex;
        justify-content: space-between;
        align-items: center;

        p {
            margin: 0;
            font-style: italic;
            color: #003366;
            text-transform: none;
        }

        .read-more {
            color: #003366;
            font-size: 0.9em;
            cursor: pointer;
        }
    }

    .trade-header {
        margin: 10px 0;
        color: #003366;
        font-weight: bold;
        text-transform: none;
    }
}

.trade-messages {
    flex: 1;
    overflow-y: auto;
    padding: 10px;

    .cannot-trade-under-attack {
        color: #FF0000;
        font-weight: bold;
        text-align: center;
        margin: 20px 0;
    }

    .trade-message {
        margin-bottom: 20px;
        border-bottom: 1px dotted #81E0FD;
        padding-bottom: 15px;

        &:last-child {
            border-bottom: none;
        }

        .transport-message {
            background-color: #A5EEFB;
            padding: 10px;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;

            p {
                margin: 0;
                color: #003366;
                text-transform: none;
                flex: 1;
            }

            .trade-image {
                width: 50px;
                height: 50px;
                margin-left: 10px;

                img {
                    width: 100%;
                    height: 100%;
                    object-fit: contain;
                }
            }
        }

        .trade-options-container {
            margin-top: 5px;
        }

        .trade-response {
            background-color: #81E0FD;
            padding: 10px;
            border-radius: 5px;
            margin-top: 5px;
            cursor: pointer;

            &:hover {
                background-color: #75DF00;
            }

            &.selected {
                background-color: #75DF00;
                border: 2px solid #003366;
            }

            &.disabled {
                background-color: #81E0FD;
                opacity: 0.5;
                cursor: not-allowed;
            }

            &.diplomat {
                background-color: #B8E8FF;

                &:hover, &.selected {
                    background-color: #75DF00;
                }
            }

            .trade-option-name {
                margin: 0 0 5px 0;
                color: #003366;
                font-weight: bold;
                text-transform: none;

                .diplomat-tag {
                    background-color: #003366;
                    color: white;
                    padding: 2px 5px;
                    border-radius: 3px;
                    font-size: 0.8em;
                    margin-right: 5px;
                }
            }

            .trade-details {
                margin: 0;
                color: #003366;
                text-transform: none;
                font-size: 0.9em;
            }
        }

        .terminal-button {
            margin: auto;
        }
    }
}
</style>
