<template>
  <div class="chatbox-container" id="discussion-tab">
    <MessageInput :channel="channel"></MessageInput>
    <div class="chatbox">
      <div class="unit" v-for="(message, id) in channel.messages" v-bind:key="id">
        <Message :message="message" :isRoot="true" @click="replyTo(message)"></Message>
        <Message v-for="(children, id) in message.child" :message="children" v-bind:key="id" @click="replyTo(message)"></Message>
        <MessageInput v-show="messageToReply === message" :channel="channel" :parent="message"></MessageInput>
      </div>
      <!--    <div class="unit">-->
      <!--      <div class="main-message">-->
      <!--        <img src="@/assets/images/char/body/jin_su.png">-->
      <!--        <p><span class="author">Jin Su :</span>It would be nice to set up BIOS. Any IT Expert up to it?</p>-->
      <!--        <span class="timestamp">moments ago</span>-->
      <!--      </div>-->
      <!--      <div>-->
      <!--        <p><img src="@/assets/images/char/head/jin_su.png"><span class="author">Jin Su :</span><strong><em>Share BIOS parameters</em></strong><br />-->
      <!--          <strong>Foodstuff Destruction</strong> : No<br />-->
      <!--          <strong>CPU Priority</strong> : Astronavigation<br />-->
      <!--          <strong>Crew lock</strong> : Piloting<br />-->
      <!--          <strong>Vocoder Announcements</strong> : Unauthorized<br />-->
      <!--          <strong>Report Deaths</strong> : Yes<br />-->
      <!--          <strong>Inhibit DMZ-CorePeace</strong> : Yes<br />-->
      <!--          <em>Find us an O2 planet!</em>-->
      <!--        </p>-->
      <!--        <span class="timestamp">moments ago</span>-->

      <!--      </div>-->
      <!--    </div>-->

      <!--    <div class="unit">-->
      <!--      <div class="main-message neron new">-->
      <!--        <img src="@/assets/images/comms/neron_chat.png">-->
      <!--        <p><span class="author">NERON :</span><strong>Gravity Simulator</strong> engaged, nice. <em>Move Humans more fastly</em>. You not get dirt everywhere! [Hax!]</p>-->
      <!--        <span class="timestamp">25min</span>-->

      <!--      </div>-->
      <!--      <div class="new">-->
      <!--        <p><img src="@/assets/images/char/head/hua.png"><span class="author">Hua :</span>Dirty dismantling the shower.</p>-->
      <!--        <span class="timestamp">~1h</span>-->
      <!--      </div>-->
      <!--      <div class="new">-->
      <!--        <p><img src="@/assets/images/char/head/paola.png"><span class="author">Paola :</span>Thanks Hua, got my sofa ready on the bridge now :)</p>-->
      <!--        <span class="timestamp">~1h</span>-->
      <!--      </div>-->
      <!--    </div>-->

      <!--    <div class="unit">-->
      <!--      <div class="main-message new">-->
      <!--        <img src="@/assets/images/char/body/ian.png">-->
      <!--        <p><span class="author">Ian :</span><strong><em>Piloting</em></strong></p>-->
      <!--        <span class="timestamp">~5d</span>-->
      <!--      </div>-->
      <!--      <div>-->
      <!--        <p><img src="@/assets/images/char/head/jin_su.png"><span class="author">Jin Su :</span>So far eight hunters shot total (3 + 5), no scrap collected yet.</p>-->
      <!--        <span class="timestamp">~3d</span>-->
      <!--      </div>-->
      <!--      <div>-->
      <!--        <p><img src="@/assets/images/char/head/ian.png"><span class="author">Ian :</span>Excellent sir, I can see why they have you training the new pilots :P</p>-->
      <!--        <span class="timestamp">~3d</span>-->
      <!--      </div>-->
      <!--      <div>-->
      <!--        <p><img src="@/assets/images/char/head/jin_su.png"><span class="author">Jin Su :</span>Kind of you to say so, yet I sadly can't agree. In fact I find our hull's exellency wanting. It shall be restored once we collected scrap and built the oscilloscope.</p>-->
      <!--        <span class="timestamp">~1d</span>-->
      <!--      </div>-->
      <!--      <div>-->
      <!--        <p><img src="@/assets/images/char/head/jin_su.png"><span class="author">Jin Su :</span>Scrap collected, still a bit left.</p>-->
      <!--        <span class="timestamp">~1d</span>-->
      <!--      </div>-->
      <!--      <div>-->
      <!--        <p><img src="@/assets/images/char/head/paola.png"><span class="author">Paola :</span>This topic is discriminating, I vote for a new one simply labeled "hunters" or "attackers"</p>-->
      <!--        <span class="timestamp">~1d</span>-->
      <!--      </div>-->
      <!--    </div>-->
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