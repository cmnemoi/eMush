<template>
    <div class="terminal-container" v-if="terminal">
        <button @click="toggleTradeView">{{ tradeViewEnabled ? 'See communications' : 'See trades' }}</button>
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
                            <ActionButton
                                v-if="establishLinkWithSolAction"
                                :key="establishLinkWithSolAction.name || ''"
                                class="terminal-button"
                                :action="establishLinkWithSolAction"
                                @click="executeTargetAction(terminal, establishLinkWithSolAction)"
                            />
                            <p class="link-established text" v-else>
                                {{ terminal.infos.linkEstablished }}
                            </p>
                        </div>
                    </div>
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
                            <ActionButton
                                v-if="upgradeNeron"
                                :key="upgradeNeron.name || ''"
                                class="terminal-button"
                                :action="upgradeNeron"
                                @click="executeTargetAction(terminal, upgradeNeron)"
                            />
                        </div>
                    </div>
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
                    :class="{ 'not-contacted' : base.name === '???', 'contacting' : base.isContacting, 'contacted' : base.name !== '???', 'selected' : base.key === selectedRebelBase }"
                    @click="selectRebelBase(base)"
                >
                    <p class="base-name">{{ base.name }}</p>
                    <p :class="{ 'base-signal' : base.isContacting, 'base-signal-lost' : base.isLost }" v-if="base.isContacting || base.isLost">{{ base.signal }}</p>
                    <img :src="getImgUrl(`rebel_bases/${base.key}.png`)" :alt="base.key" class="base-image" />
                    <template #content>
                        <h1 class="base-hover-name">{{ base.hoverName }}</h1>
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
            <div class="trade container">
                <h3 class="title">
                    <img :src="getImgUrl('spot2.svg')" alt="spot"/>
                    {{ terminal.sectionTitles?.trade || 'Radio ondes courtes' }}
                </h3>
                <p class="cannot-trade-under-attack" v-if="terminal.infos.cannotTradeUnderAttack">
                    {{ terminal.infos.cannotTradeUnderAttack }}
                </p>
                <div class="trade-options">
                    <div class="trade-option" v-for="trade in terminal.trades" :key="trade.key">
                        <p class="trade-option-name">{{ trade.name }}</p>
                        <p class="trade-option-description">{{ trade.description }}</p>
                        <button v-for="option in trade.options" :key="option.key">
                            <p class="trade-option-name">{{ option.name }}</p>
                            <p class="trade-option-description">{{ option.description }}</p>
                        </button>
                    </div>
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
import ActionButton from "@/components/Utils/ActionButton.vue";
import { mapActions } from "vuex";

export default defineComponent({
    name: "CommunicationsTerminal",
    components: {
        ActionButton
    },
    computed: {
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
            return this.terminal.getActionByKey(ActionEnum.CONTACT_XYLOPH) || new Action();
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
        }
    },
    data() {
        return {
            d_selectedRebelBase: '',
            tradeViewEnabled: false
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
}

/* NERON specific styles */

.neron-infos {
    background-image: url("/src/assets/images/neron_bg.png");
    background-repeat: no-repeat;
    background-position: right bottom;
}

.neron-infos-content {
    .neron-progress-bar-container {
        width: 60%;
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
    row-gap: 15px;
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

.base-description, .xyloph-entry-description {
    text-transform: none;
}
</style>
