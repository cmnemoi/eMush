<template>
    <TabContainer
        id="discussion-tab"
        :channel="channel"
        :new-message-allowed = "newMessagesAllowed"
        @hit-bottom="console.log(`Oh no!`) + loadMorePosts()"
    >
        <section v-for="(message, id) in messages.slice(0, loadedMessages)" :key="id" class="unit">
            <Message
                :message="message"
                :is-root="true"
                :is-replyable="true"
                @reply="replyTo(message)"
            />
            <button
                v-if="message.hasChildrenToDisplay()"
                class="toggle-children"
                @click="message.toggleChildren()"
            >
                {{ ($t(message.isFirstChildHidden() ? 'game.communications.showMessageChildren' : 'game.communications.hideMessageChildren', { count: message.getHiddenChildrenCount() })) }}
            </button>
            <Message
                v-for="(child, id) in message.children"
                :key="id"
                :message="child"
                :is-replyable="true"
                @reply="replyTo(message)"
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
import { messages } from "@/i18n";

interface DiscussionTabState {
    messageToReply: MessageEntity | null,
    loadedMessages: number
}

export default defineComponent ({
    name: "DiscussionTab",
    components: {
        Message,
        MessageInput,
        TabContainer
    },
    props: {
        channel: Channel,
        
    },
    data: ():DiscussionTabState => {
        return {
            messageToReply: null,
            loadedMessages: 4
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
        loadMorePosts(): Message | null
        {
            const hold = 4;
            if (this.loadedMessages < this.messages.length) {
                this.loadedMessages = this.loadedMessages + hold;
            }
            else { console.log("NOPE")}
            // return this.messages.slice(0, this.loadedMessages);
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
