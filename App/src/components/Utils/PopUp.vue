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
    background: transparentize(#09092d, 0.4);
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    z-index: 999;
    transition: all 0.3s;

    .modal-box {
        min-width: 400px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        padding: 2em;
        font-size: 1.1em;
        background-color: #191a4c;
        border-radius: 3px;
        border: 1px solid #3965fb;
        box-shadow:
            0 0 0 1px #191a4c,
            0 0 5px 1px rgba(57, 101, 251, 0.7),
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
    font-size: .85em;
    letter-spacing: 0.03em;
    text-decoration: none;
    font-variant: small-caps;
    transition: all 0.15s;

    &:hover, &:focus, &:active { color: white; }
}
</style>
