<template>
    <div v-if="isOpen" id="login-modal" class="modal-background">
        <div class="modal-box">
            <button class="modal-close" @click="close">
                Close
            </button>
            <slot />
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent ({
    props: {
        isOpen: Boolean
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
    z-index: 999;
    transition: all 0.3s;

    .modal-box {
        min-width: 400px;
        max-width: 740px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        padding: 1.6em 2em;
        background-color: rgba(35, 37, 100, .9);
        box-shadow:
            inset 0 0 12px 3px #3965fb, inset 0 0 0 2px #3965fb;

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
    }

    &::v-deep(h1) {
        font-size: 1.5em;
        text-transform: uppercase;
        text-align: center;
        margin: 0;
    }
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
