<template>
    <div class="crewmate-container" :class="getSelectedPlayer.character.key">
        <div class="mate">
            <div class="card">
                <div class="avatar">
                    <img :src="portrait" alt="crewmate">
                </div>
                <div>
                    <p class="name">
                        {{ getSelectedPlayer.character.name }}
                        <GoToCharacterBiographyButton :character="getSelectedPlayer?.character"/>
                    </p>
                    <div class="crewmate-attributes">
                        <div class="titles">
                            <Tippy
                                tag="ul"
                                v-for="(key) in getSelectedPlayer.titles"
                                :key="key"
                                class="title">
                                <TitleImage :title="key" />
                                <template #content>
                                    <h1 v-html="formatText(key.name)" />
                                    <p v-html="formatText(key.description)" />
                                </template>
                            </Tippy>
                        </div>
                        <div class="statuses">
                            <Statuses :statuses="getSelectedPlayer?.getPublicStatuses()" type="player" />
                        </div>
                    </div>
                </div>
            </div>
            <p class="presentation">
                {{ getSelectedPlayer.character.description  }}
            </p>
            <div class="skills">
                <Tippy
                    tag="div"
                    v-for="(skill) in target.skills"
                    :key="skill.key"
                    class="skill">
                    <img class="skill-image" :src="skillImage(skill)" :alt="skill.name">
                    <template #content>
                        <h1 v-html="formatText(skill.name)" />
                        <p v-html="formatText(skill.description)" />
                    </template>
                </Tippy>
            </div>
        </div>
        <div class="action-part">
            <ActionTabs
                v-if="actionTabs"
                v-model:activeTab="activeTab"
                :target-actions-mush="targetActionsMush"
                :target-actions-admin="targetActionsAdmin"
            />
            <div class="interactions">
                <ActionButton
                    v-for="(action, key) in getActions"
                    :key="key"
                    :action="action"
                    @click="executeWithDoubleTap(action)"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import ActionTabs from "@/components/Game/Ship/ActionTabs.vue";
import ActionButton from "@/components/Utils/ActionButton.vue";
import Statuses from "@/components/Utils/Statuses.vue";
import TitleImage from "@/components/Utils/TitleImage.vue";
import { Action } from "@/entities/Action";
import { Player, Skill } from "@/entities/Player";
import { ActionEnum } from "@/enums/action.enum";
import { characterEnum } from '@/enums/character';
import { SkillIconRecord } from "@/enums/skill.enum";
import { formatText } from "@/utils/formatText";
import { useDoubleTap } from "@/utils/useDoubleTap";
import { computed, ref } from "vue";
import { useStore } from "vuex";
import GoToCharacterBiographyButton from "@/components/Game/GoToCharacterBiographyButton.vue";

type ActionType = 'human' | 'mush' | 'admin'

const props = defineProps<{
    target: Player
}>();

defineEmits<{
    executeAction: []
}>();

const store = useStore();
const activeTab = ref<ActionType>('human');
const doubleTapHandlers = new Map<string, () => void>();

const selectedTarget = computed(() => store.getters['room/selectedTarget']);
const actionTabs = computed(() => store.getters['settings/actionTabs']);
const player = computed(() => store.getters['player/player']);

const getSelectedPlayer = computed((): Player | null => {
    if (selectedTarget.value instanceof Player) {
        return selectedTarget.value;
    }
    return null;
});

const getAllActions = computed((): Action[] => {
    if (!(selectedTarget.value instanceof Player)) {
        return [];
    }

    let actions = selectedTarget.value.actions.filter(action => action.isNotMissionAction());

    // Setup commander order action to 0 action points if available
    if (selectedTarget.value instanceof Player && props.target.hasActionByKey(ActionEnum.COMMANDER_ORDER)) {
        const commanderOrderAction = selectedTarget.value.getActionByKey(ActionEnum.COMMANDER_ORDER);
        if (commanderOrderAction) {
            actions = actions.filter(action => action.key !== ActionEnum.COMMANDER_ORDER);
            const newOrderAction = (new Action()).decode(commanderOrderAction.jsonEncode());
            newOrderAction.actionPointCost = 0;
            actions.push(newOrderAction);
        }
    }

    return actions;
});

const targetActionsHuman = computed((): Action[] => {
    return getAllActions.value.filter(action => action.isMushAction == false && action.isAdminAction == false);
});

const targetActionsMush = computed((): Action[] => {
    return getAllActions.value.filter(action => action.isMushAction == true && action.isAdminAction == false);
});

const targetActionsAdmin = computed((): Action[] => {
    return getAllActions.value.filter(action => action.isAdminAction == true);
});

const getActions = computed((): Action[] => {
    if (!actionTabs.value) return getAllActions.value;

    const actionMap: { [key: string]: Action[] } = {
        'human': targetActionsHuman.value,
        'mush': targetActionsMush.value,
        'admin': targetActionsAdmin.value
    };

    return actionMap[activeTab.value];
});

const portrait = computed((): string => {
    return characterEnum[props.target.character.key].portrait ?? '';
});

const openLearnSkillPopUp = () => store.dispatch('popup/openLearnSkillPopUp');
const openCommanderOrderPanel = () => store.dispatch('player/openCommanderOrderPanel');

const executeTargetAction = async (action: Action) => {
    if (action.canExecute === false) {
        return;
    }

    if (action.key === ActionEnum.LEARN) {
        openLearnSkillPopUp();
        return;
    }
    if (action.key === ActionEnum.COMMANDER_ORDER) {
        openCommanderOrderPanel();
        return;
    }

    if (selectedTarget.value === player.value) {
        await store.dispatch('action/executeAction', { target: null, action });
    } else {
        await store.dispatch('action/executeAction', { target: selectedTarget.value, action });
    }
};

const executeWithDoubleTap = async (action: Action): Promise<void> => {
    if (!store.getters['settings/doubleTap']) {
        await executeTargetAction(action);
        return;
    }

    const actionKey = action.key;
    if (!actionKey) return;

    if (!doubleTapHandlers.has(actionKey)) {
        const { handleTap } = useDoubleTap(async () => {
            await executeTargetAction(action);
        });
        doubleTapHandlers.set(actionKey, handleTap);
    }

    const handler = doubleTapHandlers.get(actionKey);
    if (handler) {
        handler();
    }
};

const skillImage = (skill: Skill): string => {
    return SkillIconRecord[skill.key].icon ?? '';
};
</script>

<style lang="scss" scoped>
.crewmate-container {
    position: absolute;
    z-index: 5;
    bottom: 0;
    width: 100%;
    flex-direction: row;
    padding: 3px;
    background-color: #222a6b;
}

.mate {
    flex: 1;
    max-width: 50%;
    border-right: 1px dotted #4a5d8f;
    padding: 1px;
    padding-right: 4px;
}

.card {
    flex-flow: row wrap;

    .avatar {
        align-items: center;
        justify-content: center;
        width: 110px;
        height: 70px;
        overflow: hidden;
        border: 1px solid #161951;

        img {
            position: relative;
            width: 210px;
            height: auto;
        }
    }

    .crewmate-attributes {
        flex-direction: row;
        align-items: center;
        gap: 2px;
        padding-left: 4px;

        .titles,
        .statuses {
            flex-direction:row;
            align-items: center;
            font-size: 0.9em;
            flex-wrap: wrap;
            gap: 2px;
        }

        .titles {
            margin-bottom: 2px;
        }
    }

    .name {
        font-weight: 700;
        text-transform: uppercase;
        padding-left: 4px;
        margin: 0;
    }
}

.presentation {
    margin: 0;
    padding: 2px 0;
    font-size: 0.9em;
    font-style: italic;
}

.skills {
    flex-direction: row;
    flex-wrap: wrap;
}

.interactions {
    flex: 1;
    padding: 1px;
    padding-left: 4px;
}

@each $crewmate, $face-position-x, $face-position-y in $face-position { // adjust the image position in the crewmate avatar div
    $translate-x : (50% - $face-position-x);
    $translate-y : (50% - $face-position-y);
    .#{$crewmate} .avatar img {
        transform: translate($translate-x, $translate-y);
    }
}

.action-part {
    display: flex;
    flex : 1;
}

</style>
