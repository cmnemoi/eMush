<template>
    <div class="game-popup-exploration-wrapper">
        <GamePopUp
            :title="formatText(exploration.title)"
            class="expedition"
            v-if="exploration"
            :is-open="exploration !== null"
            :closable="false"
        >
            <span v-html="formatText(exploration.planet)" />
            <span v-html="formatText(exploration.explorators)" />
            <span v-html="formatText(exploration.estimatedDuration)" />
        </GamePopUp>
    </div>
</template>

<script lang="ts">
import GamePopUp from "@/components/Utils/GamePopUp.vue";
import { DaedalusExploration } from "@/entities/DaedalusExploration";
import { formatText } from "@/utils/formatText";
import { defineComponent } from "vue";

export default defineComponent ({
    name: "ExpeditionPopUp",
    components: { GamePopUp },
    props: {
        exploration: {
            type: DaedalusExploration,
            required: true
        }
    },
    methods: {
        formatText
    }
});

</script>

<style lang="scss" scoped>
    span {
        line-height: 1.6em;

        :deep(strong) {
            font-weight: normal;
            font-variant: small-caps;
            letter-spacing: 0.02em;
            color: $cyan;
        }
    }

    .expedition {
        width: 100%;
        max-width: calc($breakpoint-desktop-m / 3);
        margin: 4px 0px;
    }

    .game-popup-exploration-wrapper h1.header::before {
        content: url("/src/assets/images/ui_icons/planet.png");
        padding-right: 0.25em;
    }

    .game-popup-exploration-wrapper {
        width: 100%;
        display: flex;
        align-items: flex-end;

        @media screen and (max-width: $breakpoint-desktop-m) and (orientation: portrait) {
            align-items: center;
        }
    }

</style>
