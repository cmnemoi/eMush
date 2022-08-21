<template>
    <TabContainer id="discussion-tab" :channel="channel" :new-message-allowed = "newMessagesAllowed">
        <section v-for="(message, id) in messages" :key="id" class="unit">
            <Message :message="message" :is-root="true" @click="replyTo(message)" />
            <Message
                v-for="(children, id) in message.child"
                :key="id"
                :message="children"
                @click="replyTo(message)"
            />
            <MessageInput v-show="messageToReply === message" :channel="channel" :parent="message" />
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
}

</style>
