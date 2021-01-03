<template>
  <div class="chatbox-container" id="discussion-tab">
    <MessageInput :channel="channel"></MessageInput>
    <div class="chatbox">
      <div class="unit" v-for="(message, id) in channel.messages" v-bind:key="id">
        <Message :message="message" :isRoot="true" @click="replyTo(message)"></Message>
        <Message v-for="(children, id) in message.child" :message="children" v-bind:key="id" @click="replyTo(message)"></Message>
        <MessageInput v-show="messageToReply === message" :channel="channel" :parent="message"></MessageInput>
      </div>
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
  position: relative;
  z-index: 2;
  height: 436px;
  margin-top: -1px;
  line-height: initial;
  background: rgba(194, 243, 252, 1);
  @include corner-bezel(0px, 6.5px, 0px);

  & .chatbox {
    overflow: auto;
    padding: 7px;
    color: #090a61;

    & .unit {
      display: block;
      align-items: flex-end;
      padding: 5px 0;
      border-bottom: 1px solid rgb(170, 212, 229);
    }
  }
}

/* SCROLLBAR STYLING */

.chatbox, {
  --scrollbarBG: white;
  --thumbBG: #090a61;

  scrollbar-width: thin;
  scrollbar-color: var(--thumbBG) var(--scrollbarBG);
  &::-webkit-scrollbar { width: 6px; }
  &::-webkit-scrollbar-track { background: var(--scrollbarBG); }
  &::-webkit-scrollbar-thumb { background-color: var(--thumbBG); }
}

</style>