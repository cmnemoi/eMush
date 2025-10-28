<template>
    <GamePopUp
        :title="$t('charPanel.skillChoiceTitle')"
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
                @click="chooseSkill(skill)"
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

<script setup lang="ts">
import GamePopUp from "@/components/Utils/GamePopUp.vue";
import { Player } from "@/entities/Player";
import { SkillIconRecord } from "@/enums/skill.enum";
import { formatText } from "@/utils/formatText";
import { useDoubleTap } from "@/utils/useDoubleTap";
import { computed, onBeforeMount } from "vue";
import { useStore } from "vuex";

type SelectableSkill = {
    key: string;
    name: string;
    description: string;
};

const props = defineProps<{player: Player}>();

const store = useStore();

const displayMushSkills = computed<boolean>(() => store.getters['player/displayMushSkills']);
const isDoubleTapEnabled = computed<boolean>(() => store.getters['settings/doubleTap']);
const popUp = computed(() => store.getters['popup/skillSelectionPopUp']);
const skillsToDisplay = computed<SelectableSkill[]>(() => {
    return displayMushSkills.value  ? props.player?.character.selectableMushSkills : props.player?.character.selectableHumanSkills;
});

const doubleTapHandlers = new Map<string, () => void>();

const chooseSkill = async (skill: SelectableSkill): Promise<void> => {
    if (!isDoubleTapEnabled.value) {
        await store.dispatch('player/chooseSkill', { player: props.player, skill });
        return;
    }

    const skillKey = skill.key;
    if (!skillKey) return;

    if (!doubleTapHandlers.has(skillKey)) {
        const { handleTap } = useDoubleTap(async () => {
            await store.dispatch('player/chooseSkill', { player: props.player, skill });
        });
        doubleTapHandlers.set(skillKey, handleTap);
    }

    const handler = doubleTapHandlers.get(skillKey);
    if (handler) {
        handler();
    }
};

const close = () => store.dispatch('popup/closeSkillSelectionPopUp');
const initMushSkillsDisplay = ({ player }: { player: Player }) => store.dispatch('player/initMushSkillsDisplay', { player });
const skillImage = (skill: SelectableSkill): string => SkillIconRecord[skill.key]?.icon ?? '';

onBeforeMount(() => initMushSkillsDisplay({ player: props.player }));
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
