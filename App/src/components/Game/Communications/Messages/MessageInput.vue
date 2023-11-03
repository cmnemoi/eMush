<template>
    <form class="chat-input">
        <textarea
            v-model="text"
            class="text-input"
            placeholder="Mon message ici!"
            @keydown.enter.exact.prevent="sendNewMessage"
            @keydown.enter.ctrl.exact.prevent="breakLine"
            @keydown.enter.shift.exact.prevent="breakLine"
        />
        <a class="submit-button" @click="sendNewMessage">
            <img src="@/assets/images/comms/submit.gif" alt="submit">
        </a>
    </form>
</template>

<script lang="ts">
import { mapActions } from "vuex";
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
            text: ""
        };
    },
    methods: {
        sendNewMessage (): void {
            if (this.text.length > 0) {
                this.text = this.text.replace(/\n/g, "//");
                this.sendMessage({ text: this.text, parent: this.parent, channel: this.channel });
                this.text = "";
            }
        },
        breakLine (): void {
            this.text += "\n";
        },
        ...mapActions('communication', [
            'sendMessage'
        ])
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

    .text-input {
        position: relative;
        flex: 1;
        resize: vertical;
        min-height: 29px;
        padding: 3px 5px;
        font-style: italic;
        opacity: 0.85;
        box-shadow: 0 1px 0 white;
        border: 1px solid #aad4e5;
        border-radius: 3px;

        &:active,
        &:focus {
            min-height: 48px;

            /* max-height: 80%; */
            font-style: initial;
            opacity: 1;
        }
    }
}

</style>
