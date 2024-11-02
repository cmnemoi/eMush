<template>
    <div class="content-section">
        <h3 class="eden-title">{{ terminal.sectionTitles?.toANewEdenTitle }}</h3>
        <p class="eden-description" v-html="formatText(terminal.sectionTitles?.toANewEdenDescription)" />
        <div class="action" v-if="computeEdenAction">
            <ActionButton
                :key="computeEdenAction.key"
                :action="computeEdenAction"
                @click="executeTargetAction(target, computeEdenAction)"
            />
        </div>
        <div class="compute-status" v-else-if="terminal.infos.edenComputed">
            <img :src="getImgUrl('att.png')" alt="warning" class="warning-icon">
            <p v-html="formatText(terminal.infos.edenComputed)" />
        </div>
        <p
            class="nothing-compute"
            v-html="formatText(terminal.infos.nothingToCompute)"
            v-else-if="terminal.infos.nothingToCompute"
        />
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
import { getImgUrl } from "@/utils/getImgUrl";


export default defineComponent ({
    name: "CalculatorTerminal",
    computed: {
        computeEdenAction(): Action | null {
            return this.terminal.getActionByKey(ActionEnum.COMPUTE_EDEN);
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
            'executeAction': 'action/executeAction'
        }),
        async executeTargetAction(target: Terminal, action: Action): Promise<void> {
            if (action.canExecute) {
                await this.executeAction({ target, action });
            }
        },
        getImgUrl,
        formatText
    },
    data() {
        return {
            ActionEnum
        };
    },
    components: { ActionButton }
});
</script>

<style  lang="scss" scoped>
.calculator-terminal-container {
    background-color: #1a1f4d;
    border-radius: 8px;
    padding: 0;
    display: flex;
    flex-direction: column;
    min-height: 400px;
    color: white;
}

.content-section {
    flex: 1;
    padding: 15px;
    background-color: rgba(173, 216, 230, 0.1);

    .eden-title {
        color: #000080;
        margin: 0 0 10px 0;
        font-size: 1.1em;
    }

    .eden-description {
        color: #000080;
        font-style: italic;
        margin-bottom: 15px;
    }
}

.compute-status {
    display: flex;
    flex-direction: row; // explicitly set row direction
    align-items: center;
    gap: 0.6em;
    background-color: rgba(255, 255, 255, 0.9);
    padding: 10px;
    border-radius: 4px;
    margin: 10px 0;

    .warning-icon {
        width: 20px;
        height: 20px;
        flex-shrink: 0; // prevent icon from shrinking
    }

    p {
        font-style: italic;
        margin: 0;
        color: #000;
        flex: 1; // allow text to take remaining space
    }
}

.nothing-compute {
    color: #000080;
    font-style: italic;
}
</style>
