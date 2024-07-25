<template>
    <GamePopUp
        title="Choix de Compétence"
        :is-open=popUp.isOpen
        @exit="close"
        v-if="player"
    >
        <div class="skill-selection">
            <Tippy
                tag="button"
                v-for="skill in player.character.selectableSkills"
                :key="skill.key"
                @click="chooseSkill({player, skill})"
            >
                <img :src="skillImage(skill)">
                <template #content>
                    <h1 v-html="formatText(skill.name)" />
                    <p v-html="formatText(skill.description)" />
                </template>
            </Tippy>
        </div>
    </GamePopUp>
</template>

<script lang="ts">
import { Player } from "@/entities/Player";
import GamePopUp from "@/components/Utils/GamePopUp.vue";
import { defineComponent } from "vue";
import { formatText } from "@/utils/formatText";
import { mapActions, mapGetters } from "vuex";
import { SkillIconRecord } from "@/enums/skill.enum";

type SelectableSkill = {
    key: string;
    name: string;
    description: string;
};

export default defineComponent ({
    name: "SkillSelectionMenu",
    components: { GamePopUp },
    props: {
        player: Player
    },
    computed: {
        ...mapGetters({
            popUp: 'popup/skillSelectionPopUp'
        })
    },
    methods: {
        ...mapActions({
            chooseSkill: 'player/chooseSkill',
            close: 'popup/closeSkillSelectionPopUp'
        }),
        formatText,
        skillImage(skill: SelectableSkill): string {
            return SkillIconRecord[skill.key].icon ?? '';
        }
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
