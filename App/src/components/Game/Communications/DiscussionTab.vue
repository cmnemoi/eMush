<template>
  <div class="chatbox-container" id="discussion-tab">
    <MessageInput :channel="channel"></MessageInput>
    <div class="chatbox">
      <section class="unit" v-for="(message, id) in channel.messages" v-bind:key="id">
        <Message :message="message" :isRoot="true" @click="replyTo(message)"></Message>
        <Message v-for="(children, id) in message.child" :message="children" v-bind:key="id" @click="replyTo(message)"></Message>
        <MessageInput v-show="messageToReply === message" :channel="channel" :parent="message"></MessageInput>
      </section>
    </div>
  </div>
</template>

<script>
import {mapActions, mapGetters} from "vuex";
import {Channel} from "@/entities/Channel";
import Message from "@/components/Game/Communications/Messages/Message";
import MessageInput from "@/components/Game/Communications/Messages/MessageInput";

export default {
  name: "DiscussionTab",
  components: {MessageInput, Message},
  props: {
    channel: Channel
  },
  data: () => {
    return {
      messageToReply: null
    }
  },
  computed: {
    ...mapGetters('communication', [
      'getCurrentChannel',
      'getMessages',
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
      'loadMessages',
    ]),
  },
  beforeMount() {
    this.loadMessages({channel:this.channel});
  }
}
</script>

<style lang="scss" scoped>

#discussion-tab {
  .unit {
    border-bottom: 1px solid rgb(170, 212, 229);

    .chat-input { margin: 5px 0 2px 50px !important; padding: 0 !important; }
  }
}

</style>