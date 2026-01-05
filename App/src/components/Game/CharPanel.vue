<template>
    <div class="char-panel" :class="player.character.key">
        <div class="char-sheet">
            <div class="char-card">
                <div class="avatar">
                    <img
                        :src="characterPortrait"
                        alt="avatar"
                        @mousedown.stop="toggleItemSelection(null)"
                    >
                </div>

                <ul class="statuses">
                    <Statuses :statuses="player.statuses" type="player" />
                    <Statuses :statuses="player.diseases" type="disease" />
                </ul>

                <div class="health-points">
                    <div class="life">
                        <Tippy tag="ol">
                            <li>
                                <ul>
                                    <li v-for="n in player?.healthPoint?.max" :key="n" :class="isFull(n, player.healthPoint.quantity)" />
                                </ul>
                            </li>
                            <li>
                                <p><img :src="getImgUrl('ui_icons/player_variables/hp.png')" alt="hp">{{ player.healthPoint.quantity }}</p>
                            </li>
                            <template #content>
                                <h1 v-html="formatContent(player.healthPoint.name)" />
                                <p v-html="formatContent(player.healthPoint.description)" />
                            </template>
                        </Tippy>
                    </div>
                    <div class="morale">
                        <Tippy tag="ol">
                            <li>
                                <ul>
                                    <li v-for="n in player?.moralPoint?.max" :key="n" :class="isFull(n, player.moralPoint.quantity)" />
                                </ul>
                            </li>
                            <li>
                                <p><img :src="getImgUrl('ui_icons/player_variables/moral.png')" alt="mp">{{ player.moralPoint.quantity }}</p>
                            </li>
                            <template #content>
                                <h1 v-html="formatContent(player.moralPoint.name)" />
                                <p v-html="formatContent(player.moralPoint.description)" />
                            </template>
                        </Tippy>
                    </div>
                </div>
                <div class="inventory">
                    <inventory
                        :items="player.items"
                        :min-slot="3"
                        :selected-item="getTargetItem"
                        @select="toggleItemSelection"
                    />
                </div>
            </div>
            <div class="actions-card">
                <div v-if="!loading && selectedItem" class="item-info">
                    <p class="item-name">
                        {{ selectedItem.name }}
                    </p>
                    <div class="item-statuses">
                        <Statuses :statuses="selectedItem.statuses" type="item" />
                    </div>
                </div>
                <ActionTabs
                    v-if="actionTabs"
                    v-model:activeTab="activeTab"
                    :target-actions-mush="targetActionsMush"
                    :target-actions-admin="targetActionsAdmin"
                />
                <div v-if="!loading && target" class="interactions">
                    <div v-if="selectedItem">
                        <ActionButton
                            v-for="(action, key) in targetActions"
                            :key="key"
                            :action="action"
                            @click="executeWithDoubleTap(action, target)"
                        />
                    </div>
                    <div v-else>
                        <ActionButton
                            v-for="(action, key) in targetActions"
                            :key="key"
                            :action="action"
                            @click="executeWithDoubleTap(action)"
                        />
                    </div>
                </div>
            </div>
        </div>
        <div class="column">
            <div class="skills">
                <ul>
                    <Tippy
                        tag="li"
                        v-for="skill, index in skillsToDisplay"
                        :key="skill.key"
                        :class="skillSlotClass(index + 1)"
                    >
                        <img class="skill-image" :src="skillImage(skill)" :alt="skill.name">
                        <template #content>
                            <h1 v-html="formatText(skill.name)" />
                            <p v-html="formatText(skill.description)" />
                        </template>
                    </Tippy>
                </ul>
                <ul>
                    <Tippy
                        tag="li"
                        v-for="index in (numberOfSkillSlots)"
                        :key="index"
                        :class="skillSlotClass(index + skillsToDisplay.length)"
                    >
                        <button class="flashing" @click="openSkillSelectionPopUp">
                            <img :src="skillSlotImage(index + skillsToDisplay.length)" alt="Plus">
                        </button>
                        <template #content>
                            <h1 v-html="formatText($t('charPanel.availableSlot'))" />
                            <p v-html="formatText($t('charPanel.chooseNewSkill', { character: player.character.name }))" />
                        </template>
                    </Tippy>
                </ul>
                <Tippy
                    tag="li"
                    class="genome"
                    v-if="player.isMush"
                    @click="toggleMushSkillsDisplay">
                    <button>
                        <img :src="getImgUrl('mush_module.png')" alt="Mush Genome">
                    </button>
                    <template #content>
                        <h1 v-html="formatText($t('charPanel.mushGenome'))" />
                        <p v-html="formatText($t('charPanel.displayYourHumanSkills'))" v-if="displayMushSkills" />
                        <p v-html="formatText($t('charPanel.displayYourMushSkills'))" v-else />
                    </template>
                </Tippy>
            </div>

            <div class="actions-sheet">
                <img :src="getImgUrl('pam.png')" alt="pam">
                <Tippy tag="div">
                    <div class="action-points">
                        <div class="actions">
                            <ul>
                                <li v-for="n in player?.actionPoint?.max" :key="n" :class="isFull(n, player.actionPoint.quantity)" />
                            </ul>
                        </div>
                        <div class="movements">
                            <ul>
                                <li v-for="n in player?.movementPoint?.max" :key="n" :class="isFull(n, player.movementPoint.quantity)" />
                            </ul>
                        </div>
                    </div>
                    <template #content>
                        <h1 v-html="formatContent(player.actionPoint.name)" />
                        <p v-html="formatContent(player.actionPoint.description)" />
                    </template>
                </Tippy>
                <ul class="specials">
                    <Tippy
                        tag="li"
                        v-for="(point) in player.skillPoints"
                        :key="point.key"
                        class="skillPoint"
                    >
                        <img :src="skillPointImg(point)" :alt="point.key">x{{ point.charge?.quantity }}
                        <template #content>
                            <h1 v-html="formatContent(point.charge?.name)" />
                            <p v-html="formatContent(point.charge?.description)" />
                        </template>
                    </Tippy>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import Inventory from "@/components/Game/Inventory.vue";
import ActionTabs from "@/components/Game/Ship/ActionTabs.vue";
import ActionButton from "@/components/Utils/ActionButton.vue";
import Statuses from "@/components/Utils/Statuses.vue";
import { Action } from "@/entities/Action";
import { Door } from "@/entities/Door";
import { Equipment } from "@/entities/Equipment";
import { Item } from "@/entities/Item";
import { Player, Skill } from "@/entities/Player";
import { SkillPoint } from "@/entities/SkillPoint";
import { ActionEnum } from "@/enums/action.enum";
import { characterEnum } from '@/enums/character';
import { SkillIconRecord } from "@/enums/skill.enum";
import { skillPointEnum } from "@/enums/skill.point.enum";
import { formatText } from "@/utils/formatText";
import { getImgUrl } from "@/utils/getImgUrl";
import { useDoubleTap } from "@/utils/useDoubleTap";
import { computed, onBeforeMount, ref } from "vue";
import { useStore } from "vuex";

type ActionType = 'human' | 'mush' | 'admin'

const props = defineProps<{
    player: Player
}>();

const store = useStore();
const activeTab = ref<ActionType>('human');

// Double tap handlers map
const doubleTapHandlers = new Map<string, () => void>();

// Vuex getters
const loading = computed(() => store.getters['player/isLoading']);
const selectedItem = computed(() => store.getters['player/selectedItem']);
const displayMushSkills = computed(() => store.getters['player/displayMushSkills']);
const actionTabs = computed(() => store.getters['settings/actionTabs']);

// Computed properties
const characterPortrait = computed((): string => {
    return characterEnum[props.player.character.key].portrait ?? '';
});

const getTargetItem = computed((): Item | null => {
    return selectedItem.value;
});

const numberOfSkillSlots = computed((): number => {
    return displayMushSkills.value ? Math.max(props.player.character.mushSkillSlots - props.player.mushSkills.length, 0) : Math.max(props.player.character.humanSkillSlots - props.player.humanSkills.length, 0);
});

const skillsToDisplay = computed((): Array<Skill> => {
    return displayMushSkills.value ? props.player.mushSkills : props.player.humanSkills;
});

const target = computed((): Item | Player | null => {
    return selectedItem.value || props.player;
});

const rawTargetActions = computed((): Action[] => {
    let actions = target.value?.actions.filter(action => action.isNotMissionAction()) || [];

    // Setup commander order action cost to 0 if available
    if (target.value instanceof Player && target.value.hasActionByKey(ActionEnum.COMMANDER_ORDER)) {
        const commanderOrderAction = target.value.getActionByKey(ActionEnum.COMMANDER_ORDER);
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
    return rawTargetActions.value.filter(action => !action.isMushAction && !action.isAdminAction);
});

const targetActionsMush = computed((): Action[] => {
    return rawTargetActions.value.filter(action => action.isMushAction && !action.isAdminAction);
});

const targetActionsAdmin = computed((): Action[] => {
    return rawTargetActions.value.filter(action => action.isAdminAction && !action.isMushAction);
});

const targetActions = computed((): Action[] => {
    if (!actionTabs.value) return rawTargetActions.value || [];

    const actionMap: { [key: string]: Action[] } = {
        'human': targetActionsHuman.value,
        'mush': targetActionsMush.value,
        'admin': targetActionsAdmin.value
    };

    return actionMap[activeTab.value] || [];
});

// Methods
const formatContent = (value: string | null | undefined): string => {
    return !value ? '' : formatText(value.toString());
};

const isFull = (value: number, threshold: number): Record<string, boolean> => {
    return {
        "full": value <= threshold,
        'empty': value > threshold
    };
};

const skillSlotClass = (index: number): string => {
    switch (index) {
    case 1:
        return 'skill-slot-basic';
    case 2:
        return 'skill-slot-once';
    default:
        return 'skill-slot-gold';
    }
};

const skillSlotImage = (index: number): string => {
    switch (index) {
    case 1:
        return getImgUrl('skills/basicplus.png');
    case 2:
        return getImgUrl('skills/onceplus.png');
    default:
        return getImgUrl('skills/goldplus.png');
    }
};

const skillImage = (skill: Skill): string => {
    return SkillIconRecord[skill.key]?.icon ?? '';
};

const skillPointImg = (point: SkillPoint): string => {
    return point.key ? (skillPointEnum[point.key]?.icon ?? '') : '';
};

const toggleItemSelection = (item: Item | null): void => {
    activeTab.value = 'human';

    if (selectedItem.value === item) {
        store.dispatch('player/selectTarget', { target: null });
    } else {
        store.dispatch('player/selectTarget', { target: item });
    }
};

const executeTargetAction = async (target: Door | Item | Equipment | Player | null, action: Action): Promise<void> => {
    if (action.canExecute === false) {
        return;
    }
    if (action.key === ActionEnum.LEARN) {
        store.dispatch('popup/openLearnSkillPopUp');
        return;
    }
    if (action.key === ActionEnum.COMMANDER_ORDER) {
        store.dispatch('player/openCommanderOrderPanel');
        return;
    }
    if (action.key === ActionEnum.COM_MANAGER_ANNOUNCEMENT) {
        store.dispatch('player/openComManagerAnnouncementPanel');
        return;
    }

    await store.dispatch('action/executeAction', { target, action });
    if (selectedItem.value instanceof Item && !props.player.items.includes(selectedItem.value)) {
        store.dispatch('player/selectTarget', { target: null });
    }
};

const executeWithDoubleTap = async(action: Action, target: Door | Item | Equipment | Player | null = null): Promise<void> => {
    if (!store.getters['settings/doubleTap']) {
        await executeTargetAction(target, action);
        return;
    }

    const actionKey = action.key;
    if (!actionKey) return;

    const handlerKey = `${actionKey}_${target?.id}`;

    if (!doubleTapHandlers.has(handlerKey)) {
        const { handleTap } = useDoubleTap(async () => {
            await executeTargetAction(target, action);
        });
        doubleTapHandlers.set(handlerKey, handleTap);
    }

    const handler = doubleTapHandlers.get(handlerKey);
    if (handler) {
        handler();
    }
};


const openSkillSelectionPopUp = (): void => {
    store.dispatch('popup/openSkillSelectionPopUp');
};

const toggleMushSkillsDisplay = (): void => {
    store.dispatch('player/toggleMushSkillsDisplay');
};

// Lifecycle
onBeforeMount(() => {
    store.dispatch('player/initMushSkillsDisplay', { player: props.player });
});
</script>

<style lang="scss" scoped>
@use "sass:color";

.char-panel {
    flex-direction: row;

    @media screen and (max-width: $breakpoint-desktop-s) { width: 100%;}


    .char-sheet {
        flex-direction: column;
        max-width: 176px;
        min-height: 459px;
        padding: 5px;
        border-top-left-radius: 4px;
        background: rgba(54, 76, 148, 0.35);

        @media screen and (max-width: $breakpoint-desktop-s) {
            flex-direction: row;
            gap: 5px;
            width: 100%;
            max-width: initial;
            min-height: initial;
        }

        .char-card {
            justify-content: flex-start;
            @media screen and (max-width: $breakpoint-desktop-s) {
                width: 110px;
            }

            .avatar img {
                max-width: 166px;
            }

            .statuses {
                position: absolute;
                flex-flow: column wrap;
                align-items: flex-start;
                margin: 2px;
                max-height: 215px;
                gap: 3px;
            }
        }

        .health-points {
            flex-direction: row;
            flex-wrap: wrap;
            justify-content: space-evenly;
            row-gap: 0.6em;
            margin: -.75em 0 .25em;

            .life, .morale {
                flex-direction: row;
                align-items: center;
                filter: drop-shadow(0 0 5px $deepBlue);


                ol {
                    align-items: center;
                    flex-direction: column-reverse;

                    @media screen and (max-width: $breakpoint-desktop-s) {
                        flex-direction: row-reverse;
                    }

                    li:first-child {
                        z-index: 1;
                    }
                }

                p,
                ul {
                    display: flex;
                    flex-direction: row;
                    align-items: center;
                    border: 1px solid color.adjust($greyBlue, $lightness: 3.2%);
                    border-radius: 3px;
                    background: $greyBlue;
                    box-shadow: 0 0 4px 1px inset rgba(28, 29, 56, 1);
                }

                p {
                    margin: 0 0 -1px 0;
                    padding: .15em .4em .2em;
                    font-size: 0.8em;
                    letter-spacing: 0.03em;
                    border-bottom-width: 0;
                    text-shadow: 0 0 2px black, 0 0 2px black;

                    img {
                        width: 11px;
                        height: 13px;
                        margin-right: 1px;
                    }
                }

                ul {
                    padding: .1em .2em;
                    border-radius: 2px;

                    @media screen and (max-width: $breakpoint-desktop-s) {
                        display: none;
                    }

                    li {
                        width: 4px;
                        height: 5px;
                        background: rgba(138, 170, 44, 1);
                        box-shadow: 1px 1px 0 0 inset rgba(255, 255, 255, 0.7);

                        &:not(:last-child) {
                            margin-right: 1px;
                        }

                        &.empty {
                            background: rgba(37, 72, 137, 1);
                            box-shadow: 1px 1px 0 0 inset rgba(78, 154, 255, 0.7);
                        }
                    }
                }
            }
        }

        .inventory {
            overflow: visible;
            margin: 0 -1px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .actions-card {
            flex: 1;

            .item-info {
                display: flex;
                justify-content: center;
                flex-direction: row;
                gap: 4px;
                margin: 8px;

                .item-name {
                    margin: 0;
                    letter-spacing: 0.03em;
                    font-variant: small-caps;
                    text-align: center;
                }

                .item-statuses {
                    display: flex;
                    flex-direction: row;
                    flex-wrap: wrap;
                    justify-content: center;
                    align-items: center;
                    gap: 5px;
                }
            }

            .interactions {
                @media screen and (max-width: $breakpoint-desktop-s) { margin-top: 6px; }
            }
        }
    }

    .column {
        justify-content: space-between;

        .skills {
            ul {
                margin-top: -1px;
                display: flex;
                flex-direction: column;
                float: right;
            }

            li {
                display: flex;
                position: relative;
                align-items: center;

                /* justify-content: center; */
                width: 30px;
                height: 34px;
                padding-right: 3px;
                margin-bottom: 7px;
                background: transparent url('/src/assets/images/skills/skillblock.png') center left no-repeat;
                border-left: 1px solid #191a53;

                button {
                    @include button-style();

                    & {
                        width: 22px;
                        height: 22px;
                        padding: 0;
                    }

                    img {
                        top: 0;
                        padding: 0;
                    }
                }

                &.skill-slot {

                    &:before {
                        position: absolute;
                        z-index: 1;
                        top: 14px;
                        left: 13px;
                        width: 20px;
                        height: 23px;
                        padding-top: 7px;
                        font-family: $font-days-one;
                        font-size: .9em;
                        text-align: center;
                    }
                }

                &.skill-slot-basic {
                    @extend .skill-slot;
                    background: transparent url('/src/assets/images/skills/skillblock.png') center left no-repeat;
                }

                &.skill-slot-once {
                    @extend .skill-slot;
                    background: transparent url('/src/assets/images/skills/skillblock_once.png') center left no-repeat;
                }

                &.skill-slot-gold {
                    @extend .skill-slot;
                    background: transparent url('/src/assets/images/skills/skillblock_gold.png') center left no-repeat;
                }

                &.locked {
                    background: transparent url('/src/assets/images/skills/skillblock_gold.png') center left no-repeat;

                    &:before {
                        content: "";
                        position: absolute;
                        z-index: 1;
                        top: 14px;
                        left: 13px;
                        background: transparent url('/src/assets/images/skills/lock_gold.png') center no-repeat;
                        width: 20px;
                        height: 23px;
                        padding-top: 7px;
                        font-family: $font-days-one;
                        font-size: .9em;
                        text-align: center;
                    }
                }

                &:nth-child(2).locked:before { content:"2"; }
                &:nth-child(3).locked:before { content:"3"; }
                &:nth-child(4).locked:before { content:"4"; }
            }
        }

        .actions-sheet {
            align-items: center;
            justify-content: flex-start;
            width: 28px;
            padding: 5px 5px 5px 0;
            border-top-right-radius: 4px;
            background: rgba(54, 76, 148, 0.35);

            & > img { margin: 3px; }

            .action-points {
                flex-direction: row;

                & > div {
                    ul {
                        display: block;
                        flex-direction: column;
                        align-items: center;
                        border: 3px solid transparent;
                        border-image: url('/src/assets/images/actionpoints_bg.svg') 40% stretch;

                        li {
                            width: 5px;
                            height: 6px;
                            border-bottom: 1px solid black;
                            background: rgba(138, 170, 44, 1);
                            box-shadow: 0 -1px 0 0 inset rgba(0, 0, 0, 0.4);
                        }
                    }
                }

                .movements ul li {
                    background: rgb(0, 255, 228);
                    background: linear-gradient(135deg, rgba(255, 255, 255, 1) 5%, rgba(0, 255, 228, 1) 20%);

                    &.empty {
                        background: rgb(14, 62, 56);
                        background: linear-gradient(135deg, rgba(18, 85, 106, 1) 5%, rgba(14, 62, 56, 1) 20%);
                    }
                }

                .actions ul li {
                    background: rgb(255, 85, 153);
                    background: linear-gradient(135deg, rgba(255, 255, 255, 1) 5%, rgba(255, 85, 153, 1) 20%);

                    &.empty {
                        background: rgb(64, 0, 0);
                        background: linear-gradient(135deg, rgba(77, 17, 32, 1) 5%, rgba(64, 0, 0, 1) 20%);
                    }
                }
            }
            .specials {
                display: flex;
                flex-direction: column;

                li {
                    display: flex;
                    flex-direction: row;
                    align-items: baseline;
                    margin: 2px 0;
                    font-size: 0.75em;
                    font-weight: 700;

                    img { margin-right: -3px; }
                }
            }
        }
    }
}
</style>
