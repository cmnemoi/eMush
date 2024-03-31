<template>
    <form class="chat-input">
        <textarea
            v-model="text"
            ref="input"
            :placeholder="$t('game.communications.myMessageHere')"
            @keydown.enter.exact.prevent="sendNewMessage"
            @keydown.enter.ctrl.exact.prevent="breakLine"
            @keydown.enter.shift.exact.prevent="breakLine"
            @keyup="updateTypedMessage(text); resize()"
            @keyup.enter.exact.prevent="clearTypedMessage"
        />
        <button
            class="submit-button"
            :disabled="typedMessage.length <= 0"
            @click="sendNewMessage"
            @click.stop="clearTypedMessage">
            <img src="@/assets/images/comms/submit.gif" alt="submit">
        </button>
    </form>
</template>

<script lang="ts">
import { mapActions, mapGetters } from "vuex";
import { Channel } from "@/entities/Channel";
import { Message } from "@/entities/Message";
import { defineComponent } from "vue";

export default defineComponent ({
    name: "MessageInput",
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
        };
    },
    computed: {
        ...mapGetters('communication', [
            'typedMessage'
        ])
    },
    methods: {
        sendNewMessage(): void {
            if (this.text.length > 0) {
                this.text = this.text.replace(/\n/g, "//"); // Replace line breaks with "//" so they are actually interpreted as line breaks
                this.sendMessage({ text: this.text, parent: this.parent, channel: this.channel });
                this.text = "";
            }
        },
        breakLine (): void {
            this.text += "\n";
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
        }
    },
    mounted() {
        this.text = this.typedMessage;
    },
    watch: {
        typedMessage() {
            this.text = this.typedMessage;
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
        opacity: 0.85;
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
