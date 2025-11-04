<template>
    <form class="chat-input">
        <div v-if="!showRichEditor" class="form-container">
            <div class="input-wrapper">
                <div ref="preview" class="input-preview" v-html="formatSyntax(text)"/>
                <textarea
                    v-model="text"
                    ref="input"
                    class="input-area"
                    :placeholder="$t('game.communications.myMessageHere')"
                    @keydown.enter.exact.prevent="sendNewMessage()"
                    @keydown.enter.ctrl.exact.prevent="breakLine"
                    @keydown.enter.shift.exact.prevent="breakLine"
                    @keyup="resize()"
                    @focusout ="updateTypedMessage(text)"
                    @keyup.enter.exact.prevent="clearTypedMessage"
                />
            </div>
            <div class="buttons-container">
                <Tippy tag="button" class="format-button" @click.prevent="openRichEditor">
                    <img :src="getImgUrl('comms/buttonFormat.png')" alt="format">
                    <template #content>
                        <h1 v-html="$t('game.communications.messageInputAdvanced')"/>
                        <p v-html="$t('game.communications.messageInputAdvancedDescription')"/>
                    </template>
                </Tippy>
                <button
                    class="submit-button"
                    :disabled="text <= 0"
                    @click="sendNewMessage()"
                    @click.stop="clearTypedMessage">
                    <img :src="getImgUrl('comms/submit.gif')" alt="submit">
                </button>
            </div>
        </div>

        <RichTextEditor
            :visible="showRichEditor"
            :initial-text="text"
            @cancel="closeRichEditor"
            @send="sendNewMessage"
        />

    </form>
</template>

<script lang="ts">
import { mapActions, mapGetters } from "vuex";
import { Channel } from "@/entities/Channel";
import { Message } from "@/entities/Message";
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import RichTextEditor from "./RichTextEditor/RichTextEditor.vue";
import { Tippy } from "vue-tippy";
import { formatSyntax } from "@/utils/formatText";

export default defineComponent ({
    name: "MessageInput",
    components: {
        RichTextEditor,
        Tippy
    },
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
            showRichEditor: false
        };
    },
    computed: {
        ...mapGetters({
            typedMessage: 'communication/typedMessage',
            player: 'player/player'
        })
    },
    methods: {
        formatSyntax,
        getImgUrl,
        sendNewMessage(messageToSend?: string): void {
            const textToSend = messageToSend !== undefined ? messageToSend : this.text;
            this.showRichEditor = false;

            if (textToSend.length > 0) {
                const formattedText = textToSend.replace(/\n/g, "//").replace(/^(\/neron )/ig, '/neron ');

                this.sendMessage({
                    text: formattedText,
                    parent: this.parent,
                    channel: this.channel,
                    player: this.player
                });
                this.text = "";
                this.closeRichEditor();
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
            const input = this.$refs.input;
            const preview = this.$refs.preview;
            if (!input) return;
            input.style.height = "auto";
            input.style.height = input.scrollHeight + 2 + "px";
            preview.style.height = "auto";
            preview.style.height = input.scrollHeight + 2 + "px";
        },
        openRichEditor(): void {
            this.showRichEditor = true;
        },
        closeRichEditor(): void {
            this.showRichEditor = false;
        }
    },
    mounted() {
        this.text = this.$refs.input.value = this.typedMessage;
        this.resize();
        this.$nextTick(() => {
            if (window.innerWidth < 768) {
                return;
            }

            if (this.$refs.input && this.$refs.input.offsetParent !== null) {
                this.$refs.input.focus();
            }
        });
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
.chat-input {
    display: flex;
    position: relative;
    flex-direction: row;
    padding: 7px 7px 4px 7px;
    overflow: hidden;
}
.form-container {
    display: flex;
    flex-direction: row;
    align-items: flex-end;
    gap: 5px;
    margin-top:2px;
    width: 100%;
}
.input-wrapper {
    position: relative;
    flex: 1;

    &:active .input-preview,
    &:focus-within .input-preview {
        font-style: initial;
        opacity: 1 !important;
    }
}
.input-preview {
    display: block;
    resize: none;
    overflow-y: scroll;
    width: 100%;
    min-height: 58px;
    max-height: 348px;
    background: #fff;
    padding: 3px 5px;
    font-family: "Fira Mono", Consolas, monospace;
    font-size: 0.85em;
    font-style: italic;
    line-height: 15px;
    word-break: break-word;
    white-space: pre-wrap;
    opacity: 0.75;
    box-shadow: 0 1px 0 white;
    border: 1px solid #aad4e5;
    border-radius: 3px;
    z-index: 50;
    @extend %game-scrollbar;

    :deep(em) {
        color: $red;
    }
    :deep(.neron) {
        color: #387ce3;
        font-family: "Fira Mono", Consolas, monospace;
    }
}
.input-area {
    position: absolute;
    resize: none;
    overflow-y: scroll;
    width: 100%;
    min-height: 58px;
    max-height: 348px;
    padding: 3px 5px;
    font-family: "Fira Mono", Consolas, monospace;
    font-size: 0.85em;
    color: transparent;
    caret-color: #000;
    line-height: 15px;
    word-break: break-word;
    border: none;
    box-shadow: none;
    z-index: 100;
    background: transparent;
    @extend %game-scrollbar;
}
.format-button, .submit-button {
    cursor: pointer;
    @include button-style();

    & {
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    &:first-child {
        margin-bottom: 4px; /* Espace entre les deux boutons */
    }

    img {
        width: 24px;
        max-height: 24px;
    }
}

</style>
