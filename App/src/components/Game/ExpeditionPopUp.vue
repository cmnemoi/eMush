<template>
    <GamePopUp title="Exploration en cours" class="expedition">
        <span><strong>Planète: </strong>Piluter</span>
        <span><strong>Équipe: </strong>Hua, Jin Su, Janice</span>
        <span><strong>Retour estimé: </strong>70 minutes</span>
    </GamePopUp>
</template>

<script lang="ts">
import GamePopUp from "@/components/Utils/GamePopUp.vue";
import { defineComponent } from "vue";
export default defineComponent ({
    name: "ExpeditionPopUp",
    components: { GamePopUp },
});
</script>

<style lang="scss" scoped>
    span {
        line-height: 1.6em;
        
        strong {
            font-weight: normal;
            font-variant: small-caps;
            letter-spacing: 0.02em;
            color: $cyan;
        }
    }

</style>