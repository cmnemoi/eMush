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


.game-popup-exploration-wrapper {
    max-width: $breakpoint-desktop-l;
    width:100%;
    display: flex;
    align-items: flex-end;
    margin: auto;

    @media screen and (max-width: $breakpoint-desktop-m) {
        width: $breakpoint-desktop-s;
    }

    @media screen and (max-width: $breakpoint-desktop-s) {
        max-width: $game-canvas-width !important;
        width: 100%;
        align-items: center;
    }

    .expedition {
        width: 100%;
        max-width: calc($breakpoint-desktop-m / 3);
        margin: 5px 18px -5px 18px;

        @media screen and (max-width: $breakpoint-desktop-s) {
            max-width: $game-canvas-width !important;
            width: 100%;
            align-items: center;
        }

        h1.header::before {
            content: url("/src/assets/images/ui_icons/planet.png");
            padding-right: 0.25em;
        }

        span {
            line-height: 1.6em;

            :deep(strong) {
                font-weight: normal;
                font-variant: small-caps;
                letter-spacing: 0.02em;
                color: $cyan;
            }
        }
    }
}
</style>
