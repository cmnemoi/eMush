<template>
    <GamePopUp
        :title="$t('charPanel.apprenticeship')"
        :is-open=popUp.isOpen
        @exit="close"
        v-if="player"
    >
        <span v-html="formatText($t('charPanel.skillChoiceDescription'))" />
        <div class="skill-selection">
            <Tippy
                tag="button"
                v-for="skill in skillsToDisplay"
                :key="skill.key"
                @click="learnSkill(skill)"
            >
                <img :src="skillImage(skill)" :alt="skill.name" />
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
import { Action } from "@/entities/Action";
import { ActionEnum } from "@/enums/action.enum";

type SelectableSkill = {
    key: string,
    name: string,
    description: string
}

export default defineComponent ({
    name: "LearnSkillMenu",
    components: { GamePopUp },
    props: {
        player: {
            type: Player,
            required: true
        }
    },
    computed: {
        ...mapGetters({
            popUp: 'popup/learnSkillPopUp'
        }),
        learnAction(): Action {
            return this.player.getActionByKeyOrThrow(ActionEnum.LEARN);
        },
        skillsToDisplay(): SelectableSkill[] {
            const skillsInRoom: SelectableSkill[] = this.getSkillsInRoom();
            const uniqueSkillsInRoom = this.removeDuplicateSkills(skillsInRoom);
            const skillsToDisplay = this.removePlayerSkills(uniqueSkillsInRoom);

            return skillsToDisplay;
        }
    },
    methods: {
        ...mapActions({
            close: 'popup/closeLearnSkillPopUp',
            executeAction: 'action/executeAction'
        }),
        formatText,
        getSkillsInRoom(): SelectableSkill[] {
            const roomPlayers = this.player?.room?.players;
            let skillsInRoom: SelectableSkill[] = [];
            roomPlayers?.forEach((player: Player) => {
                skillsInRoom = skillsInRoom.concat(player.humanSkills);
            });

            return skillsInRoom;
        },
        removeDuplicateSkills(skillsInRoom: SelectableSkill[]): SelectableSkill[] {
            return skillsInRoom.filter((skill: SelectableSkill, index: number) => {
                return skillsInRoom.findIndex(skillInRoom => skillInRoom.key === skill.key) === index;
            });
        },
        removePlayerSkills(skillsInRoom: SelectableSkill[]): SelectableSkill[] {
            let skills = skillsInRoom;
            this.player?.humanSkills?.forEach((skill: SelectableSkill) => {
                skills = skills.filter((skillInRoom: SelectableSkill) => {
                    return skillInRoom.key !== skill.key;
                });
            });

            return skills;
        },
        skillImage(skill: SelectableSkill): string {
            return SkillIconRecord[skill.key].icon ?? '';
        },
        async learnSkill(skill: SelectableSkill): Promise<void> {
            const action = this.learnAction;
            if (!action.canExecute) {
                return;
            }

            await this.executeAction({ target: null, action, params: { skill: skill.key } });
            await this.close();
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
