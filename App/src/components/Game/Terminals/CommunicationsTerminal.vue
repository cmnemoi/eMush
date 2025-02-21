<template>
    <div class="terminal-container" v-if="terminal">
        <section class="left-section">
            <div class="sol-link container">
                <h3 class="title">
                    <img :src="getImgUrl('spot2.svg')" alt="spot"/>
                    {{ terminal.sectionTitles?.contact }}
                </h3>
                <div class="link-infos">
                    <div class="link-infos-img">
                        <img
                            v-for="n in sensorFramesCount"
                            :key="n"
                            :src="getImgUrl(`sensor0${n}.png`)"
                            :class="['sensor-frame', `frame-${n}`]"
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
    </div>
</template>

<script lang="ts">
import { Terminal } from "@/entities/Terminal";
import { defineComponent } from "vue";
import { formatText } from "@/utils/formatText";
import { getImgUrl } from "@/utils/getImgUrl";
import { ActionEnum } from "@/enums/action.enum";
import { Action } from "@/entities/Action";
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
        neron(): string {
            return this.terminal.sectionTitles?.neronVersion?.split('.')[0].split(' ')[0] || '';
        },
        neronMinorVersion(): integer {
            return Number(this.terminal.sectionTitles?.neronVersion?.split('.')[1]);
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
        async executeTargetAction(target: Terminal, action: Action): Promise<void> {
            if (action.canExecute) {
                await this.executeAction({ target, action });
            }
        },
        formatText,
        getImgUrl
    }
});
</script>

<style lang="scss" scoped>
/* Layout */
.left-section {
    display: flex;
    flex-direction: column;
}

/* Common styles */
.text {
    color: #75C7E5;
    text-transform: uppercase;
}

/* Container styles */
.container {
    background-color: #A5EEFB;
    margin: 5px;
    padding: 5px;
    border-radius: 5px;

    .title {
        text-transform: uppercase;
        margin: 0;
        padding: 5px;
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
</style>
