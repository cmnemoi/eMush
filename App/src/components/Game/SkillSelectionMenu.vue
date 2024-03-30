<template>
    <GamePopUp title="Choix de Compétence">
        <div class="skill-selection">
            <button>
                <img :src="getAssetUrl('skills/human/cook.png')">
            </button>
            <button>
                <img :src="getAssetUrl('skills/human/opportunist.png')">
            </button>
            <button>
                <img :src="getAssetUrl('skills/mush/mycellium_spirit.png')">
            </button>
            <button>
                <img :src="getAssetUrl('skills/mush/trapper.png')">
            </button>
        </div>
    </GamePopUp>
</template>

<script lang="ts">
import GamePopUp from "@/components/Utils/GamePopUp.vue";
import { defineComponent } from "vue";
import { getAssetUrl } from "@/utils/getAssetUrl";

export default defineComponent ({
    name: "SkillSelectionMenu",
    components: { GamePopUp },
    methods: {
        getAssetUrl
    }
});
</script>

<style lang="scss" scoped>

.skill-selection {
    flex-flow: row wrap;
    padding-top: .6em;
    padding-bottom: .6em;

    button {

        margin: 0 .1em;
        padding: .4em;
        border-radius: 3px;
        transition: all .15s;

        p {
            color: white;
            margin: auto .2em auto .4em;
        }

        &:hover, &:focus, &:active { background-color: #17448E; }
    }

}

</style>
