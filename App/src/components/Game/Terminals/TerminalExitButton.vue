<template>
    <button class="exit">
        Quitter
    </button>
</template>

<script lang="ts">
import ActionButton from "@/components/Utils/ActionButton.vue";
import { Terminal } from "@/entities/Terminal";
import { defineComponent } from "vue";

export default defineComponent ({
    name: "TerminalExitButton",
    // components: { ActionButton },
    props : {
        terminal : {
            type: Terminal,
            required: true
        }
    }
});
</script>

<style  lang="scss" scoped>

.exit {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 120px;
    min-height: 22px;
    transform: translateY(100%);
    align-items: center;
    justify-content: center;
    padding: .2em;
    background: #232e6e;
    border-radius: 0 0 3px 3px;
    color: white;
    font-size: 1em;
    font-weight: 700;
    font-variant: small-caps;
    letter-spacing: 0.03em;
    text-decoration: none;
    text-align: center;
    text-shadow: 0 0 4px #15273c, 0 0 4px #15273c;

    &:hover, &:focus, &:active {
        background: $brightCyan;
    }
}

</style>
