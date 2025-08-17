<template>
    <form class="chat-input">
        <div v-if="!showRichEditor" class="form-container">
            <textarea
                v-model="text"
                ref="input"
                class="messageInput-area"
                :placeholder="$t('game.communications.myMessageHere')"
                @keydown.enter.exact.prevent="sendNewMessage()"
                @keydown.enter.ctrl.exact.prevent="breakLine"
                @keydown.enter.shift.exact.prevent="breakLine"
                @keyup="resize()"
                @focusout ="updateTypedMessage(text)"
                @keyup.enter.exact.prevent="clearTypedMessage"
            />
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
        ...mapGetters('communication', [
            'typedMessage'
        ])
    },
    methods: {
        getImgUrl,
        sendNewMessage(messageToSend?: string): void {
            const textToSend = messageToSend !== undefined ? messageToSend : this.text;
            this.showRichEditor = false;

            if (textToSend.length > 0) {
                const formattedText = textToSend.replace(/\n/g, "//");

                this.sendMessage({
                    text: formattedText,
                    parent: this.parent,
                    channel: this.channel
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
            const element = this.$refs.input;
            if (!element) return;
            element.style.height = "auto";
            element.style.height = element.scrollHeight + 2 + "px";
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
    .messageInput-area {
        flex: 1; /* Le textarea prend tout l'espace disponible */
        resize: vertical;
        overflow-y: scroll;
        min-height: 58px;
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
