<template>
    <form class="chat-input">
        <div v-if="!showRichEditor" class="form-container">
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
            />
            <div class="character-count" :class="{ 'getting-close': textLength > maxLength/2, 'over-limit': textLength > maxLength}">
                {{ textLength }} / {{ maxLength}}
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
                    :disabled="text === undefined || textLength == 0 || textLength > maxLength"
                    @click="sendNewMessage()">
                    <img :src="getImgUrl('comms/submit.gif')" alt="submit">
                </button>
            </div>
        </div>

        <RichTextEditor
            :visible="showRichEditor"
            :initial-text="text"
            :max-length="maxLength"
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
            showRichEditor: false,
            maxLength: 4096
        };
    },
    computed: {
        ...mapGetters({
            typedMessage: 'communication/typedMessage',
            player: 'player/player'
        }),
        textLength(): number {
            return this.text ? this.text.length + this.text.split('\n').length - 1 : 0;
        }
    },
    methods: {
        getImgUrl,
        sendNewMessage(messageToSend?: string): void {
            const textToSend = messageToSend !== undefined ? messageToSend : this.text;
            if (textToSend.length > this.maxLength) {
                return;
            }
            this.showRichEditor = false;

            if (textToSend.length > 0) {
                const formattedText = textToSend.replace(/\n/g, "//");

                this.sendMessage({
                    text: formattedText,
                    parent: this.parent,
                    channel: this.channel,
                    player: this.player
                });
                this.text = "";
                this.clearTypedMessage();
                this.closeRichEditor();
            }
        },
        breakLine (): void {
            // find current caret position
            const element = this.$refs.input;
            const caretPos = element.selectionStart;

            // insert \n at the caret position
            const newValue = element.value.slice(0, caretPos) + "\n" + element.value.slice(caretPos);

            // update both element.value and Vue model to keep them in sync
            element.value = newValue;
            this.text = newValue;

            // move caret after the inserted line break
            this.$nextTick(() => {
                element.selectionStart = element.selectionEnd = caretPos + 1;
            });
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
.input-area {
    flex: 1;
    resize: vertical;
    overflow-y: scroll;
    min-height: 58px;
    max-height: 348px;
    padding: 3px 5px;
    font-style: italic;
    opacity: 0.75;
    border: 1px solid #aad4e5;
    border-radius: 3px;
    @extend %game-scrollbar;

    &:active,
    &:focus {
        font-style: initial;
        opacity: 1;
    }
}
.character-count {
    position: absolute;
    bottom: 3px;
    right: 38px;
    font-size: 11px;
    font-style: italic;
    font-weight: bold;
    color: transparent; // Only display when over the limit
    opacity: 0.65;
    padding: 2px 5px;
    border-radius: 3px;
    pointer-events: none;

    &.getting-close {
        color: rgba(128, 128, 128, 0.75);
    }

    &.over-limit {
        color: #d32f2f;
    }
}
.input-area:focus ~ .character-count{
    opacity: 1;
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
        margin-bottom: 8px; /* Espace entre les deux boutons */
    }

    img {
        width: 24px;
        max-height: 24px;
    }
}

</style>
