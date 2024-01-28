<template>
    <GamePopUp :title="formatText(exploration.title)" class="expedition" v-if="exploration">
        <span v-html="formatText(exploration.planet)" />
        <span v-html="formatText(exploration.explorators)" />
        <span v-html="formatText(exploration.estimatedDuration)" />
    </GamePopUp>
</template>

<script lang="ts">
import GamePopUp from "@/components/Utils/GamePopUp.vue";
import { formatText } from "@/utils/formatText";
import { defineComponent } from "vue";
import { DaedalusExploration } from "@/entities/DaedalusExploration";

export default defineComponent ({
    name: "ExpeditionPopUp",
    components: { GamePopUp },
    props: {
        exploration: {
            type: DaedalusExploration,
            required: true
        },
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
</style>
