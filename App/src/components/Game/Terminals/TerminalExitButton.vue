<template>
    <button class="exit" v-if="terminal" @click="executeTargetAction(target, exitTerminalAction)">
        {{ exitTerminalAction.name }}            
    </button>
</template>

<script lang="ts">
import { Action } from "@/entities/Action";
import { Terminal } from "@/entities/Terminal";
import { defineComponent } from "vue";
import { mapActions } from "vuex";
import { ActionEnum } from "@/enums/action.enum";

export default defineComponent ({
    name: "TerminalExitButton",
    computed: {
        exitTerminalAction(): Action {
            const action = this.terminal?.actions.find(action => action.key === ActionEnum.EXIT_TERMINAL);
            if (!action) throw new Error(`No exit_terminal action found for terminal ${this.terminal?.key}`);

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
    },
    data() {
        return {
            ActionEnum
        };
    },
});
</script>

<style  lang="scss" scoped>

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

</style>
