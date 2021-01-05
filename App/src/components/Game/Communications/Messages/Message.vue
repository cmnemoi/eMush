<template>
  <div v-if="isRoot" class="message main-message" @click="$emit('click')">
    <div class="char-portrait">
      <img :src="characterPortrait">
    </div>
    <p>
      <span class="author">{{ message.character.name }} :</span><span v-html="format(message.message)"></span></p>
    <div class="actions">
      <a href="#"><img src="@/assets/images/comms/reply.png">Répondre</a>
      <a href="#"><img src="@/assets/images/comms/fav.png">Favori</a>
      <a href="#"><img src="@/assets/images/comms/alert.png">Plainte</a>
    </div>
    <span class="timestamp">{{ formatDate(message.date, {local: "fr-FR"}) }}</span>
  </div>
  <div v-if="!isRoot" class="message child-message" @click="$emit('click')">
    <p>
      <img :src="characterPortrait">
      <span class="author">{{ message.character.name }} :</span><span v-html="format(message.message)"></span></p>
    <div class="actions">
      <a href="#"><img src="@/assets/images/comms/reply.png">Répondre</a>
      <a href="#"><img src="@/assets/images/comms/alert.png">Plainte</a>
    </div>
    <span class="timestamp">{{ formatDate(message.date, {local: "fr-FR"}) }}</span>
  </div>
</template>

<script>
import formatDistanceToNow from 'date-fns/formatDistanceToNow'
import { fr } from 'date-fns/locale'
import {Message} from "@/entities/Message";
import {characterEnum} from "@/enums/character";

export default {
  name: "Message",
  emits: {
    // No validation
    click: null,
  },
  props: {
    message: Message,
    isRoot: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    characterPortrait: function() {
      const images = characterEnum[this.message.character.key];
      return this.isRoot ? images.body : images.head;
    },
  },
  methods: {
    formatDate: (date) => {
      return formatDistanceToNow(date, {locale : fr});
    },
    format: function (value) {
      if (!value) return ''
      value = value.toString()
      value = value.replaceAll(/\*\*(.*)\*\*/g, '<strong>$1</strong>');
      value = value.replaceAll(/\*(.*)\*/g, '<em>$1</em>');
      value = value.replaceAll(/:pa:/g, '<img src="'+require("@/assets/images/pa.png")+'" alt="pa">')
      return value.replaceAll(/:pm:/g, '<img src="'+require("@/assets/images/pm.png")+'" alt="pm">')
    }
  }
}
</script>return

<style lang="scss" scoped>

.message {
  position: relative;
  align-items: flex-start;
  flex-direction: row;

  .char-portrait {
    align-items: flex-start;
    justify-content: flex-start;
    min-width: 36px;
    margin-top: 4px;
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

    /deep/ em {color: #cf1830;} //Makes italic text red
  }

  &.new p { //New messages styling
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

  &.main-message {

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

  &.child-message {
    margin-left: 50px;
    img { margin-right: 3px; }
    p { margin-top: 10px; }

    p::before { //Bubble triangle
      $size: 8px;
      content:"";
      position: absolute;
      top: -$size;
      left: 4px ;
      width: 0px;
      height: 0px;
      border-left: $size solid transparent;
      border-right: $size solid transparent;
      border-bottom: $size solid white;
    }

    /* MESSAGES LINKTREE */

    &::before {
      --border-radius: 5px;
      content: "";
      position: absolute;
      top: calc( 0px - var(--border-radius) );
      left: -36px;
      width: calc( 28px + var(--border-radius) );
      height: calc( 26px + var(--border-radius) );
      border-left: 1px solid #aad4e5;
      border-bottom: 1px solid #aad4e5;
      border-radius: var(--border-radius);

      clip-path: polygon(
              0 var(--border-radius),
              calc(100% - var(--border-radius)) var(--border-radius),
              calc(100% - var(--border-radius)) 100%,
              0 100%
      );
    }

    &:not(:last-child)::after {
      --border-radius: 5px;
      content: "";
      position: absolute;
      top: 25px;
      left: -36px;
      width: calc( 28px + var(--border-radius) );
      bottom: calc( -4px - var(--border-radius) );
      border-left: 1px solid #aad4e5;
      border-top: 1px solid #aad4e5;
      border-radius: var(--border-radius);

      clip-path: polygon(
              0 0,
              calc(100% - var(--border-radius)) 0,
              calc(100% - var(--border-radius)) calc(100% - var(--border-radius)),
              0 calc(100% - var(--border-radius))
      );
    }
  }

  &.neron { //Neron messages styling

    .char-portrait {
      position: absolute;
      top: 0;
      left: 0;
      z-index: 2;
      margin: 4px 6px;
    }

    p {
      background: #74CBF3;
      font-variant: small-caps;
      
      .author { color: inherit; }
    }

    &.main-message {
      p { padding-left: 46px;
        &::before { content: none; } //removes the bubble triangle
      }
    }

    &.child-message p::before { border-color: #74CBF3; }
  }

  .actions { //buttons styling
    display: none;
    position: absolute;
    right: 3px;
    top: 5px;
    height: 14px;
  }
}

.message:hover, .message:focus, .message:focus-within, .message:active {
  .actions { display: flex; }
}

</style>