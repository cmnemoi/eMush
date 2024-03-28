<template>
    <TabContainer id="discussion-tab" :channel="channel" :new-message-allowed = "newMessagesAllowed">
        <section v-for="(message, id) in messages" :key="id" class="unit">
            <Message
                :message="message"
                :is-root="true"
                :is-replyable="true"
                @click="replyTo(message)" />
            <button class="toggle-children" @click="message.toggleChildren()">
                {{ message.hasChildrenToDisplay() ? ($t(message.isFirstChildHidden() ? 'game.communications.showMessageChildren' : 'game.communications.hideMessageChildren', { count: message.getHiddenChildrenCount() })) : '' }}
            </button>
            <Message
                v-for="(child, id) in message.children"
                :key="id"
                :message="child"
                :is-replyable="true"
                @click="replyTo(message)"
            />
            <MessageInput v-if="messageToReply === message && newMessagesAllowed" :channel="channel" :parent="message" />
        </section>
    </TabContainer>
</template>

<script lang="ts">
import { mapActions, mapGetters } from "vuex";
import { Channel } from "@/entities/Channel";
import MessageInput from "@/components/Game/Communications/Messages/MessageInput.vue";
import TabContainer from "@/components/Game/Communications/TabContainer.vue";
import { defineComponent } from "vue";
import { Message as MessageEntity } from "@/entities/Message";
import Message from "@/components/Game/Communications/Messages/Message.vue";

interface DiscussionTabState {
    messageToReply: MessageEntity | null
}

export default defineComponent ({
    name: "DiscussionTab",
    components: {
        Message,
        MessageInput,
        TabContainer
    },
    props: {
        channel: Channel
    },
    data: ():DiscussionTabState => {
        return {
            messageToReply: null
        };
    },
    computed: {
        ...mapGetters('communication', [
            'messages'
        ]),
        newMessagesAllowed(): boolean | undefined
        {
            return this.channel?.newMessageAllowed;
        }
    },
    methods: {
        replyTo: function (message: MessageEntity): void {
            if (this.messageToReply === message) {
                this.messageToReply = null;
            } else {
                this.messageToReply = message;
            }
        },
        ...mapActions('communication', [
            'loadMessages'
        ])
    }
});
</script>

<style lang="scss" scoped>

#discussion-tab {
    .unit {
        border-bottom: 1px solid rgb(170, 212, 229);

        .chat-input { margin: 5px 0 2px 50px !important; padding: 0 !important; }
    }

    .toggle-children {
        text-align: left;
        color: $deepGreen;
        font-size: .9em;
        cursor: pointer;
        text-decoration: underline;
    }
}

</style>
