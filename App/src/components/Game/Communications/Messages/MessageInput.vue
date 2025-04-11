<template>
    <form class="chat-input">
        <div class="action-buttons">
            <ActionButtons
                :actions="['shareHealth']"
                @share-health="handleShareHealth"
            />
            <ActionButtons
                v-if="!player.isFocused()"
                :actions="['shareInventory']"
                @share-inventory="handleShareInventory"
            />
            <ActionButtons
                v-if="player.isFocusedOnTerminal(TerminalEnum.RESEARCH_LAB_TERMINAL)"
                :actions="['shareResearch']"
                @share-research="handleShareResearch"
            />
        </div>
        <textarea
            v-model="text"
            ref="input"
            :placeholder="$t('game.communications.myMessageHere')"
            @keydown.enter.exact.prevent="sendNewMessage"
            @keydown.enter.ctrl.exact.prevent="breakLine"
            @keydown.enter.shift.exact.prevent="breakLine"
            @keyup="resize()"
            @focusout ="updateTypedMessage(text)"
            @keyup.enter.exact.prevent="clearTypedMessage"
        />
        <button
            class="submit-button"
            :disabled="text <= 0"
            @click="sendNewMessage"
            @click.stop="clearTypedMessage">
            <img :src="getImgUrl('comms/submit.gif')" alt="submit">
        </button>
    </form>
</template>

<script lang="ts">
import { mapActions, mapGetters } from "vuex";
import { Channel } from "@/entities/Channel";
import { Message } from "@/entities/Message";
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import ActionButtons from "@/components/Game/Communications/ActionButtons.vue";
import { TerminalEnum } from "@/enums/terminal.enum";
import { Project } from "@/entities/Project";

export default defineComponent ({
    name: "MessageInput",
    components: { ActionButtons },
    props: {
        channel: {
            type: Channel,
            required: true
        },
        parent: {
            type: Message,
            required: false
        }
    },
    data(): any {
        return {
            text: this.typedMessage,
            TerminalEnum
        };
    },
    computed: {
        ...mapGetters({
            'typedMessage': 'communication/typedMessage',
            'player': 'player/player'
        })
    },
    methods: {
        getImgUrl,
        sendNewMessage(): void {
            if (this.text.length > 0) {
                this.text = this.text.replace(/\n/g, "//"); // Replace line breaks with "//" so they are actually interpreted as line breaks
                this.sendMessage({ text: this.text, parent: this.parent, channel: this.channel });
                this.text = "";
            }
        },
        breakLine (): void {
            // find current caret position
            const element = this.$refs.input;
            const caretPos = element.selectionStart;

            // insert \n at the caret position
            element.value = element.value.slice(0, caretPos) + "\n" + element.value.slice(caretPos);

            // move caret to the end of the inserted "//"
            element.selectionStart = element.selectionEnd = caretPos + 1;
        },
        ...mapActions('communication', [
            'sendMessage',
            'updateTypedMessage'
        ]),
        clearTypedMessage(): void {
            this.updateTypedMessage('');
            this.text = '';
        },
        resize() {
            const element = this.$refs.input;
            element.style.height = "auto";
            element.style.height = element.scrollHeight + 2 + "px";
        },
        handleShareHealth() {
            const health = `${this.player.healthPoint.quantity}:hp: / ${this.player.moralPoint.quantity}:pmo:`;
            if (this.typedMessage.length > 0) {
                this.updateTypedMessage(`${this.typedMessage}\n${health}`);
            } else {
                this.updateTypedMessage(health);
            }
        },
        handleShareInventory() {
            const inventory = this.player.room?.inventory(this.$t);
            if (this.typedMessage.length > 0) {
                this.updateTypedMessage(`${this.typedMessage}\n${inventory}`);
            } else {
                this.updateTypedMessage(inventory);
            }
        },
        handleShareResearch() {
            const research = this.player.terminal?.projects.map((project: Project) => project.toString()).join('\n');
            if (this.typedMessage.length > 0) {
                this.updateTypedMessage(`${this.typedMessage}\n${research}`);
            } else {
                this.updateTypedMessage(research);
            }
        }
    },
    mounted() {
        this.text = this.$refs.input.value = this.typedMessage;
        this.resize();
    },
    watch: {
        typedMessage() {
            this.text = this.$refs.input.value = this.typedMessage;
            this.resize();
        }
    }
});
</script>

<style lang="scss" scoped>

.action-buttons {
    display: flex;
    flex-direction: column;
    height: 100%;
    max-height: 25px;
    margin-right: 8px;
}

.chat-input {
    display: flex;
    position: relative;
    flex-direction: row;
    padding: 7px 7px 4px 7px;

    .submit-button {
        cursor: pointer;

        @include button-style();

        width: 24px;
        margin-left: 4px;
    }

    textarea {
        position: relative;
        flex: 1;
        resize: vertical;
        overflow-y: scroll;
        min-height: 48px;
        max-height: 348px;
        padding: 3px 5px;
        font-style: italic;
        opacity: 0.75;
        box-shadow: 0 1px 0 white;
        border: 1px solid #aad4e5;
        border-radius: 3px;
        @extend %game-scrollbar;

        &:active,
        &:focus {
            font-style: initial;
            opacity: 1;
        }
    }
}

</style>
