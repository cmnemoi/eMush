<template>
    <div class="terminal-container" v-if="terminal">
        <div class="contact-section">
            <h3>
                <img :src="getImgUrl('spot2.svg')" alt="spot" />
                {{ terminal.sectionTitles.contact }}
            </h3>
            <div class="contact-status">
                <div class="sensor-icon">
                    <img
                        v-for="n in sensorFramesCount"
                        :key="n"
                        :src="getImgUrl(`sensor0${n}.png`)"
                        :class="['sensor-frame', `frame-${n}`]"
                        alt="connection status"
                    />
                </div>
                <div class="status-text">
                    <p>{{ terminal.infos?.linkStrength }}</p>
                    <ActionButton
                        v-if="establishLinkWithSolAction"
                        :key="establishLinkWithSolAction.name || ''"
                        :action="establishLinkWithSolAction"
                        @click="executeTargetAction(terminal, establishLinkWithSolAction)"
                    />
                    <p v-else>{{ terminal.infos?.linkEstablished }}</p>
                </div>
            </div>
        </div>
        <div class="contact-section">
            <h3>
                <img :src="getImgUrl('spot2.svg')" alt="spot" />
                {{ terminal.sectionTitles.neronVersion }}
            </h3>
            <div>
                <ActionButton
                    :key="upgradeNeron.name || ''"
                    :action="upgradeNeron"
                    @click="executeTargetAction(terminal, upgradeNeron)"
                />
            </div>
        </div>
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
        sensorFramesCount(): number {
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
@keyframes sensorAnimation {
    0%, 25% { opacity: 1; }
    50%, 100% { opacity: 0; }
}

.terminal-container {
    display: flex;
    flex-direction: column;
    height: 100%;
    width: 100%;
    background-color: #9FE8FC;
}

.contact-section {
    padding: 1em;

    h3 {
        display: flex;
        align-items: center;
        gap: 0.1em;
        font-weight: bold;
        margin: 0 0 1em;
        text-transform: uppercase;
    }

    background-image: url("/src/assets/images/sensor_bg.svg");
    background-repeat: no-repeat;
    background-position: right bottom;
}

.contact-status {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 1em;
}

.sensor-icon {
    position: relative;
    width: 60px;
    height: 60px;
    border-radius: 2px;
    flex-shrink: 0;

    img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        opacity: 0;

        &.frame-1 {
            opacity: 1;
            animation: none;
        }
    }

    .sensor-frame {
        &.frame-2 {
            animation: sensorAnimation 4s infinite;
            animation-delay: 0s;
        }
        &.frame-3 {
            animation: sensorAnimation 3s infinite;
            animation-delay: 1s;
        }
        &.frame-4 {
            animation: sensorAnimation 2s infinite;
            animation-delay: 0.5s;
        }
    }
}

.status-text {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;

    p {
        margin: 0;
        font-size: 0.9em;
        font-weight: normal;
        line-height: 1.5;
        text-align: center;
        text-transform: uppercase;
    }

    :deep(.action-button) {
        width: fit-content;
    }
}
</style>
