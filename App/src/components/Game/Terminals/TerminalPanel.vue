<template>
    <div class="terminal" v-if="player.terminal">
        <h1><img :src="getImgUrl('spot2.svg')">{{ player.terminal.name }}</h1>
        <div>
            <TerminalTips :content="player.terminal.tips" />
            <AstroTerminal v-if="player.isFocusedOnTerminal(TerminalEnum.ASTRO_TERMINAL)" :terminal="player.terminal" />
            <CommandTerminal v-else-if="player.isFocusedOnTerminal(TerminalEnum.COMMAND_TERMINAL)" :terminal="player.terminal" />
            <BlockOfPostItTerminal v-else-if="player.isFocusedOnTerminal(TerminalEnum.BLOCK_OF_POST_IT)" :terminal="player.terminal" />
            <BiosTerminal v-else-if="player.isFocusedOnTerminal(TerminalEnum.BIOS_TERMINAL)" :terminal="player.terminal" />
            <ProjectsTerminal v-else-if="player.isFocusedOnProjectsTerminal() || player.isFocusedOnPilgredTerminal()" :terminal="player.terminal" />
        </div>
        <TerminalExitButton :terminal="player.terminal" />
    </div>
</template>

<script lang="ts">
import TerminalTips from "@/components/Game/Terminals/TerminalTips.vue";
import TerminalExitButton from "@/components/Game/Terminals/TerminalExitButton.vue";
import AstroTerminal from "@/components/Game/Terminals/AstroTerminal.vue";
import CommandTerminal from "@/components/Game/Terminals/CommandTerminal.vue";
import BlockOfPostItTerminal from "@/components/Game/Terminals/BlockOfPostItTerminal.vue";
import BiosTerminal from "@/components/Game/Terminals/BiosTerminal.vue";
import ProjectsTerminal from "@/components/Game/Terminals/ProjectsTerminal.vue";
import { defineComponent } from "vue";
import { TerminalEnum } from "@/enums/terminal.enum";
import { Player } from "@/entities/Player";
import { getImgUrl } from "@/utils/getImgUrl";

export default defineComponent ({
    name: "TerminalPanel",
    components: {
        TerminalTips,
        TerminalExitButton,
        AstroTerminal,
        CommandTerminal,
        BlockOfPostItTerminal,
        BiosTerminal,
        ProjectsTerminal
    },
    props: {
        player: {
            type: Player,
            required: true
        }
    },
    methods: {
        getImgUrl
    },
    data() {
        return {
            TerminalEnum
        };
    }
});
</script>

<style  lang="scss" scoped>

.terminal {
    position: relative;
    flex-direction: column;
    width: 100%;
    max-width: 424px;
    height: 460px;
    margin-bottom: 2em;
    color: $deepBlue;
    background: $brightCyan;

    //didn't @include the bevels because of the Exit button being outside the frame
    clip-path: polygon(6.5px 0, calc(100% - 6.5px ) 0, 100% 6.5px, 100% calc(100% + 2em), 0 calc(100% + 2em), 0 6.5px);

    h1 {
        font-size: 1rem;
        letter-spacing: 0.03rem;
        text-transform: uppercase;
        margin: 0;
        padding: 5px 10px;

        img { vertical-align: middle; }
    }

    & > div {
        overflow: auto;
        padding: 0 8px 5px;

        align-items: stretch;
        padding-bottom: .3em;

        @extend %game-scrollbar;
    }

    &::v-deep(em) {
        color: $red;
    }

}

</style>
