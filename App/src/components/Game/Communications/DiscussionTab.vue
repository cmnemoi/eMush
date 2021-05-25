<template>
    <TabContainer id="discussion-tab" :channel="channel" new-message-allowed>
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

<script>
import { mapActions, mapGetters } from "vuex";
import { Channel } from "@/entities/Channel";
import Message from "@/components/Game/Communications/Messages/Message";
import MessageInput from "@/components/Game/Communications/Messages/MessageInput";
import TabContainer from "@/components/Game/Communications/TabContainer";

export default {
    name: "DiscussionTab",
    components: {
        Message,
        MessageInput,
        TabContainer
    },
    props: {
        channel: Channel
    },
    data: () => {
        return {
            messageToReply: null
        };
    },
    computed: {
        ...mapGetters('communication', [
            'messages'
        ])
    },
    methods: {
        replyTo: function (message) {
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
};
</script>

<style lang="scss" scoped>

#discussion-tab {
    .unit {
        border-bottom: 1px solid rgb(170, 212, 229);

        .chat-input { margin: 5px 0 2px 50px !important; padding: 0 !important; }
    }
}

</style>
