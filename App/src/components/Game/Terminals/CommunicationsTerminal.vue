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
                        v-for="n in 4"
                        :key="n"
                        :src="getImgUrl(`sensor0${n}.png`)"
                        :class="['sensor-frame', `frame-${n}`]"
                        alt="connection status"
                    />
                </div>
                <div class="status-text">
                    <p>{{ terminal.infos?.linkStrength?.toUpperCase() }}</p>
                    <ActionButton
                        v-if="establishLinkWithSolAction"
                        :key="establishLinkWithSolAction?.name"
                        :action="establishLinkWithSolAction"
                        @click="executeTargetAction(terminal, establishLinkWithSolAction)"
                    />
                    <p v-else>{{ terminal.infos?.linkEstablished?.toUpperCase() }}</p>
                </div>
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
    }

    .sensor-frame {
        animation: sensorAnimation 2s infinite;

        &.frame-1 { animation-delay: 0s; }
        &.frame-2 { animation-delay: 0.5s; }
        &.frame-3 { animation-delay: 1s; }
        &.frame-4 { animation-delay: 1.5s; }
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
    }

    :deep(.action-button) {
        width: fit-content;
    }
}
</style>
