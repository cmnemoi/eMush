<template>
    <template v-if="isOpen">
        <div class="modal-toast" :class="type">
            <img v-if="type == 'warning'" src="@/assets/images/att.png" alt="warning" />
            <img v-else-if="type == 'error'" src="@/assets/images/neron_eye.gif" alt="error" />
            <img v-else-if="type == 'success'" src="@/assets/images/att.png" alt="warning" />
            <img v-else src="@/assets/images/info.png" alt="info" />
            <div class="content">
                <h1 v-if="title">
                    {{ title }}
                </h1>
                <button class="modal-close" @click="close">
                    Close
                </button>
                <slot />
            </div>
        </div>
        <div v-if="type == 'error'" class="modal-background">
        </div>
    </template>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent ({
    props: {
        isOpen: Boolean,
        title: String,
        type: String
    },
    emits: [
        "close"
    ],
    methods: {
        close() {
            this.$emit("close");
        }
    }
});
</script>

<style lang="scss" scoped>
.modal-background {
    position: fixed;
    background: transparentize(#09092d, 0.5);
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 998;
    transition: all 0.3s;
}

.modal-toast {
    position: fixed;
    z-index: 999;
    bottom: 24px;
    width: 95%;
    max-width: 520px;
    left: 50%;
    transform: translatex(-50%);
    flex-direction: row;
    align-items: center;
    column-gap: 1.2em;
    padding: 0.9em 1.4em;
    background-color: rgba(35, 37, 100, .9);
    box-shadow:
        inset 0 0 12px 3px #3965fb,
        inset 0 0 0 2px #3965fb,
        0 0 22px 8px transparentize(#09092d, 0.4);

        &::after { // angle blue triangles decoration
        content: "";
        position: absolute;
        z-index: -1;
        left: 0;
        right: 0;
        top: 0;
        bottom: 0;
        margin: 3px;
        border: 5px solid transparent;
        border-image: url('~@/assets/images/ToolTip-corners.gif') 50% round;
    };

    .content { display: block; }
}

h1 {
    font-size: 1.3em;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    text-align: left;
    margin: 0.3em 0;
}

.modal-close {
    cursor: pointer;
    position: absolute;
    text-align: center;
    right: 0;
    top: 0;
    padding: 12px;
    color: transparentize(white, 0.4);
    font-size: 1em;
    letter-spacing: 0.03em;
    text-decoration: none;
    font-variant: small-caps;
    transition: all 0.15s;

    &:hover, &:focus, &:active { color: white; }
}
</style>
