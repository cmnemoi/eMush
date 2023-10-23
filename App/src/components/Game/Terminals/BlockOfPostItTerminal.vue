<template>
    <div class="block-of-post-it-terminal-container">
        <form class="chat-input">
            <textarea
                v-model="text"
                class="text-input"
                @keydown.enter.exact.prevent="breakLine"
                @keydown.enter.ctrl.exact.prevent="breakLine"
                @keydown.enter.shift.exact.prevent="breakLine"
            />
        </form>
        <div class="write-button" >
            <div class="actions">
                <ActionButton
                    :key="writeAction.key"
                    :action="writeAction"
                    @click="executeTargetAction(terminalTarget, writeAction)"
                />
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Terminal } from "@/entities/Terminal";
import { defineComponent } from "vue";
import { ActionEnum } from "@/enums/action.enum";
import { Action } from "@/entities/Action";
import { mapActions } from "vuex";
import ActionButton from "@/components/Utils/ActionButton.vue";

export default defineComponent ({
    name: "BlockOfPostItTerminal",
    components : {
        ActionButton
    },
    props: {
        terminal: {
            type: Terminal,
            required: true
        }
    },
    computed: {
        writeAction(): Action | null {
            return this.terminal.getActionByKey(ActionEnum.WRITE);
        },
        terminalTarget() : Terminal {
            return this.terminal;
        }
    },
    methods: {
        ...mapActions({
            'executeAction': 'action/executeAction',
        }),
        breakLine (): void {
            this.text += "\n//\n";
        },
        async executeTargetAction(target: Terminal, action: Action): Promise<void> {
            if(action.canExecute) {
                const params = { "content": this.text };
                this.text = '';
                await this.executeAction({ target, action, params });
            }
        }
    },
    data() {
        return {
            ActionEnum,
            text: ""
        };
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
        min-height: 200px;
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

</style>
