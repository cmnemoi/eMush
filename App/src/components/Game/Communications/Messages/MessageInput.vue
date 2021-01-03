<template>
<div>
  <textarea class="chat-input" placeholder="Type your message here!" v-model="text" @keyup.enter="sendNewMessage"></textarea>
</div>
</template>

<script>
import {Message} from "@/entities/Message";
import {mapActions} from "vuex";
import {Channel} from "@/entities/Channel";

export default {
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
  data: () => {
    return {
      text: null
    }
  },
  methods: {
    sendNewMessage: function () {
      if (this.text.length > 0) {
        this.sendMessage({text: this.text, parent: this.parent, channel: this.channel})
        this.text = null;
      }
    },
    ...mapActions('communication', [
      'sendMessage',
    ]),
  },
}
</script>

<style lang="scss" scoped>
textarea {

  position: relative;
  resize: vertical;
  min-height: 29px;
  margin: 7px 7px 4px 7px;
  padding: 3px 5px;
  font-style: italic;
  opacity: .85;

  box-shadow: 0px 1px 0px white;
  border: 1px solid #aad4e5;
  border-radius: 3px;

  &:active, &:focus {
    min-height: 48px;
    max-height: 80%;
    font-style: initial;
    opacity: 1;
  }
}

</style>