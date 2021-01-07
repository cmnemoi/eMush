<template>
<div class="chatbox-container" id="private-discussion-tab">
  <div class="chat-input">
    <textarea placeholder="Type your message here!" v-model="text" @keyup.enter="sendNewMessage"></textarea>
    <a class="submit" href="#"><img src="@/assets/images/comms/submit.gif" alt="submit"></a>
  </div>
  <div class="chatbox">
    <div class="actions">
      <a href="#"><img src="@/assets/images/comms/refresh.gif">Rafr.</a>
      <a href="#"><img src="@/assets/images/comms/invite.png">Inviter</a>
      <a href="#"><img src="@/assets/images/comms/alert.png">Plainte</a>
      <a href="#"><img src="@/assets/images/comms/close.png">Quitter</a>
    </div>
    <ul class="participants">
      <li><img src="@/assets/images/char/body/ian.png"></li>
      <li><img src="@/assets/images/char/body/jin_su.png"></li>
      <li><img src="@/assets/images/char/body/paola.png"></li>
      <li><img src="@/assets/images/char/body/chun.png"></li>
    </ul>
    <section class="unit">
      <div class="message new">
        <div class="char-portrait">
          <img src="@/assets/images/char/body/ian.png">
        </div>
        <p><span class="author">Ian :</span><strong><em>Piloting</em></strong></p>
        <span class="timestamp">~1d</span>
      </div>
      <div class="message new">
        <div class="char-portrait">
          <img src="@/assets/images/char/body/jin_su.png">
        </div>
        <p><span class="author">Jin Su :</span>So far eight hunters shot total (3 + 5), no scrap collected yet.</p>
        <span class="timestamp">~3d</span>
      </div>
      <div class="message">
        <div class="char-portrait">
          <img src="@/assets/images/char/body/ian.png">
        </div>
        <p><span class="author">Ian :</span>Excellent sir, I can see why they have you training the new pilots :P</p>
        <span class="timestamp">~3d</span>
      </div>
      <div class="message">
        <div class="char-portrait">
          <img src="@/assets/images/char/body/jin_su.png">
        </div>
        <p><span class="author">Jin Su :</span>Kind of you to say so, yet I sadly can't agree. In fact I find our hull's exellency wanting. It shall be restored once we collected scrap and built the oscilloscope.></p>
        <span class="timestamp">~5d</span>
      </div>
      <div class="message">
        <div class="char-portrait">
          <img src="@/assets/images/char/body/jin_su.png">
        </div>
        <p><span class="author">Jin Su :</span>Wololo !</p>
        <span class="timestamp">~6d</span>
      </div>
    </section>
    <section class="log">
      <p class="text-log"><strong>Jin Su</strong> a rejoint la discussion.</p>
      <span class="timestamp">environ 2 heures</span>
    </section>
  </div>
</div>
</template>

<script>


export default {
  name: "PrivateTab",
  props: {
  }
}
</script>

<style lang="scss" scoped>

/* --- PROVISIONAL UNTIL LINE 203 --- */

.message {
  position: relative;
  align-items: flex-start;
  flex-direction: row;

  .char-portrait {
    align-items: flex-start;
    justify-content: flex-start;
    min-width: 36px;
    padding: 2px;
  }

  p:not(.timestamp) {
    position:relative;
    flex: 1;
    margin: 3px 0;
    padding: 4px 6px;
    border-radius: 3px;
    background: white;
    word-break: break-word;

    .author {
      color: #2081e2;
      font-weight: 700;
      font-variant: small-caps;
      padding-right: .25em;
    }

    em {color: #cf1830;}
  }

  &.new p {
    border-left: 2px solid #EA9104;

    &::after {
      content:"";
      position: absolute;
      top: 7px;
      left: -6px;
      height: 11px;
      width: 11px;
      background: transparent url('~@/assets/images/comms/thinklinked.png') center no-repeat;
    }
  }

  p { min-height: 52px; }

  p::before { //Bubble triangle*/
    $size: 8px;
    content:"";
    position: absolute;
    top: 4px;
    left: -$size;
    width: 0px;
    height: 0px;
    border-top: $size solid transparent;
    border-bottom: $size solid transparent;
    border-right: $size solid white;
  }

  &.new p {
    &::before { border-right-color: #EA9104 }
    &::after { top: 22px; }
  }
}

/* ----- */

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

/* ----- */

.log {
  position: relative;
  padding: 4px 5px;
  margin: 1px 0;
  border-bottom: 1px solid rgb(170, 212, 229);

  p {
    margin: 0;
    font-size: .95em;
    /deep/ img { vertical-align: middle; }
  }
}

/* --- END OF PROVISIONAL --- */


#private-discussion-tab {
  .unit {
    padding: 5px 0;
  }

  .actions {
    flex-direction: row;
    justify-content: flex-end;
    align-items: stretch;

    a {
      @include button-style(.83em, 400, initial);
      height: 100%;
      margin-left: 3px;
    }
  }

  .participants {
    flex-direction: row;
    margin: 8px 0;

    li { width: 28px; }
  }
}

#private-discussion-tab .unit > .message:nth-of-type(odd) {
  flex-direction: row-reverse;

  .char-portrait { align-items: flex-end; }

  .timestamp { right: 41px; }

  p::before {
    left: initial;
    right: -8px;
    transform: rotate(180deg);
  }
  
  &.new p::before { border-right-color: white; }
}

</style>