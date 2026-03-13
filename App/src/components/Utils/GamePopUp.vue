<template>
    <div class="modal-box" v-if="isOpen">
        <h1 class="header">
            {{ title }}
        </h1>
        <slot />
        <button v-if="closable" class="modal-close" @click="$emit('exit', $event)">
            <img :src="getImgUrl('comms/close.png')" alt="close">
        </button>
    </div>
</template>

<script setup lang="ts">
import { getImgUrl } from "@/utils/getImgUrl";

withDefaults(defineProps<{
    title?: string;
    isOpen?: boolean;
    closable?: boolean;
}>(), {
    closable: true
});

defineEmits<{
    exit: [event: Event];
}>();
</script>

<style  lang="scss" scoped>
@use "sass:color";
.modal-box {
    position: relative;
    margin-bottom: 1.2em;
    padding: 0 .8em .5em;
    background-color: #191a4c;
    border-radius: 3px;
    border: 1px solid #3965fb;
    box-shadow:
        0 0 0 1px #191a4c,
        0 8px 8px -5px color.adjust(black, $alpha: -0.15),
        0 0 5px 1px rgba(57, 101, 251, 0.7)
    ;

    h1.header {
        display: flex;
        flex-direction: row;
        align-self: center;
        align-items: center;
        margin: 0 2em;
        padding: .1em 2em .2em;
        min-height: 22px;
        color: #f6d70a;
        font-size: 0.9em;
        letter-spacing: .03em;
        font-weight: 400;
        font-variant: small-caps;
        text-shadow: 0 0 4px black, 0 0 4px black;
        background: #2d2f89;

        img { padding-right: 4px; }

        @include corner-bezel(0, 0, 22px, 22px );
    }

    p {
        margin: 0.6em 0;
        font-size: 0.85em;
        line-height: 1.5em;
    }

    .modal-close {
        cursor: pointer;
        position: absolute;
        right: 0;
        top: 0;
        margin: .3em;
        padding: .2em;
        border-radius: 3px;
        transition: all 0.15s;

        &:hover, &:focus, &:active { background-color: #17448E; }
    }

    &.expedition {
        h1.header::before {
            content:  url("/src/assets/images/ui_icons/planet.png");
            padding-right: 0.25em;
        }
    }
}
</style>
