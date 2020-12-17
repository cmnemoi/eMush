<template>
  <div v-if="isRoot" class="main-message" @click="$emit('click')">
    <img :src="characterPortrait">
    <p>
      <span class="author">{{ message.character.name }} :</span><span v-html="format(message.message)"></span></p>
    <span class="timestamp">{{ formatDate(message.date, {local: "fr-FR"}) }}</span>
  </div>
  <div v-if="!isRoot" class="child-message" @click="$emit('click')">
    <p>
      <img :src="characterPortrait">
      <span class="author">{{ message.character.name }} :</span><span v-html="format(message.message)"></span></p>
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
      value = value.replaceAll(/\*\*(\w*)\*\*/g, '<strong>$1&nbsp;</strong>');
      value = value.replaceAll(/:pa:/g, '<img src="'+require("@/assets/images/pa.png")+'" alt="pa">')
      return value.replaceAll(/:pm:/g, '<img src="'+require("@/assets/images/pm.png")+'" alt="pm">')
    }
  }
}
</script>return

<style lang="scss" scoped>
div {
  position: relative;
  display: block;
  clear: both;

  & p:not(.timestamp) {
    position:relative;
    margin: 3px 0;
    padding: 4px 6px;
    border-radius: 3px;
    background: white;

    & .author {
      color: #2081e2;
      font-weight: 700;
      font-variant: small-caps;
      padding-right: .25em;
    }

    & em {color: #cf1830;}
  }

  & .timestamp {
    position: absolute;
    z-index: 2;
    right: 5px;
    bottom: 5px;
    font-size: .85em;
    font-style: italic;
    opacity: .5;
    float: right;
  }

  &.new:not(.neron) p, &.new.neron {
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
    & img { margin: 2px; float: left; }

    & p { margin-left: 36px; min-height: 52px; }

    & p::before { /* bubble triangle*/
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
    & img { margin-right: 3px; }
    & p { margin-top: 12px; }

    p::before { /* bubble triangle*/
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
      top: calc( -12px - var(--border-radius) );
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
      top: 13px;
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

  &.neron {
    padding: 2px 4px;
    border-radius: 4px;
    background: #74CBF3;

    & p {
      min-height: 36px;
      padding: 0 0 0 8px;
      font-variant: small-caps;
      color: inherit;
      background: transparent;
      & .author{ color: inherit; }
      &::before { content: none; }
      &::after { top: 8px;}
    }

    &::after { top: 12px !important; }
  }

}
</style>