<template>
<div class="chatbox-container" id="mush-tab">
  <div class="chat-input">
    <textarea placeholder="Type your message here!" v-model="text" @keyup.enter="sendNewMessage"></textarea>
    <a class="submit" href="#"><img src="@/assets/images/comms/submit.gif" alt="submit"></a>
  </div>
  <div class="chatbox">
    <div class="actions">
      <a href="#"><img src="@/assets/images/comms/refresh.gif">Rafr.</a>
      <a href="#"><img src="@/assets/images/comms/alert.png">Plainte</a>
    </div>
    <div class="unit">
      <div class="banner cycle-banner">
        <img class="expand" src="@/assets/images/comms/less.png">
        <span>Jour 5 Cycle 6</span>
      </div>
      <div class="message new">
        <div class="char-portrait">
          <img src="@/assets/images/char/body/ian.png">
        </div>
        <p><img src="@/assets/images/comms/talkie.png"> <span class="author">Ian :</span><strong><em>Piloting</em></strong></p>
        <span class="timestamp">~1d</span>
      </div>
    </div>
    <div class="unit">
      <div class="banner cycle-banner">
        <img class="expand" src="@/assets/images/comms/less.png">
        <span>Jour 5 Cycle 5</span>
      </div>
      <div class="message new">
        <div class="char-portrait">
          <img src="@/assets/images/char/body/jin_su.png">
        </div>
        <p><img src="@/assets/images/comms/talkie.png"> <span class="author">Jin Su :</span>So far eight hunters shot total (3 + 5), no scrap collected yet.</p>
        <span class="timestamp">~3d</span>
      </div>
      <div class="message">
        <div class="char-portrait">
          <img src="@/assets/images/char/body/ian.png">
        </div>
        <p><img src="@/assets/images/comms/talkie.png"> <span class="author">Ian :</span>Excellent sir, I can see why they have you training the new pilots :P</p>
        <span class="timestamp">~3d</span>
      </div>
      <div class="log">
        <p class="text-log"><img src="@/assets/images/triumph.png"> Bienvenue parmi le Mush <strong>Ian</strong>. Vous avez été récompensé avec <strong>120 points de Triomphe</strong>.</p>
        <span class="timestamp">~5d</span>
      </div>
    </div>
  </div>
</div>
</template>

<script>


export default {
  name: "MushTab",
  props: {
  }
}
</script>

<style lang="scss" scoped>

/* --- PROVISIONAL UNTIL LINE 185 --- */

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


#mush-tab {
  .unit {
    padding: 5px 0;
  }

  .chat-input .submit { //change the submit button color
    $color: #ff3867;
    $hover-color: #fa6480;
    background: $color;
    background: linear-gradient(0deg, 
      darken(adjust-hue($color, 13), 5.49) 2%, 
      $color 6%, 
      $color 46%, 
      lighten(adjust-hue($color, -6), 3.5) 54%, 
      lighten(adjust-hue($color, -6), 3.5) 94%, 
      lighten(desaturate($color, 25.00), 15.49) 96%
    );

    &:hover, &:focus {
      background: $hover-color;
      background: linear-gradient(0deg, 
        darken(adjust-hue($hover-color, 14), 3.92) 2%, 
        $hover-color 6%, 
        $hover-color 46%, 
        lighten(adjust-hue($hover-color, -4), 1) 54%, 
        lighten(adjust-hue($hover-color, -4), 1) 94%, 
        lighten(desaturate($hover-color, 18.10), 13.14) 96%
      );
    }
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

  .banner {
    margin-bottom: 6px;
    background: #e7bacc !important;
  }
}

#mush-tab .unit > .message:nth-of-type(odd) {
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