<template>
    <div class="tips">
        <input id="tips" type="checkbox" name="tips" />
        <label for="tips">
            Afin de trouver une planète à explorer, il faut d'abord <strong>réaliser un scan</strong> pour en trouver jusqu'à un maximum de 2. Les résultats des planètes trouvées sont personnels et exclusifs à chaque personne.
            <br><br>
            Une fois une planète trouvée et choisie, vous devez communiquer à l'équipage l'<strong>orientation et le fuel</strong> nécessaire pour s'y rendre. Ceci permettra au <strong>Commandant</strong> de déplacer le Daedalus vers la planète choisie.
            <br><br>
            Lorsque le Daedalus est arrivé et en orbite, il est impossible de scanner pour rechercher d'autres planètes. Par contre, vous pouvez <strong>sonder la planète</strong> en orbite pour obtenir des GéoDonnées supplementaires. Ces données serviront au corps expéditionnaire, qui pourra préparer à l'avance l'exploration.
            <br><br>
            Pour sortir de l'orbite, le Commandant en charge doit exécuter l'action depuis le Terminal de Commandement.
        </label>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent ({
    name: "TerminalTips"
});
</script>

<style  lang="scss" scoped>

.tips { @extend %retracted-tips; }

</style>