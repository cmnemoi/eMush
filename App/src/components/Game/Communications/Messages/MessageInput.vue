<template>
<div class="chat-input">
  <textarea placeholder="Type your message here!" v-model="text" @keyup.enter="sendNewMessage"></textarea>
  <a class="submit" href="#"><img src="@/assets/images/comms/submit.gif" alt="submit"></a>
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

.chat-input {
  position: relative;
  flex-direction: row;
  padding: 7px 7px 4px 7px;

  a {
    @include button-style();
    width: 24px;
    margin-left: 4px;
  }

  textarea {
    position: relative;
    flex: 1;
    resize: vertical;
    min-height: 29px;
    padding: 3px 5px;
    font-style: italic;
    opacity: .85;

    box-shadow: 0px 1px 0px white;
    border: 1px solid #aad4e5;
    border-radius: 3px;

    &:active, &:focus {
      min-height: 48px;
      /*max-height: 80%;*/
      font-style: initial;
      opacity: 1;
    }
  }
}

</style>