<template>
    <div class="action-panel">
        <ActionTabs
            v-if="actionTabs"
            :target-actions-mush="targetActionsMush"
            :target-actions-admin="targetActionsAdmin"
            v-model:activeTab="activeTab"
        />
        <div class="action-list">
            <ActionButton
                v-for="(actionWithTarget, key) in getActions"
                :key="key"
                :action="actionWithTarget.action"
                @click="executeWithDoubleTap(actionWithTarget)"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
import ActionButton from "@/components/Utils/ActionButton.vue";
import { Action } from "@/entities/Action";
import { Equipment } from "@/entities/Equipment";
import { Hunter } from "@/entities/Hunter";
import { Player } from "@/entities/Player";
import { useDoubleTap } from "@/utils/useDoubleTap";
import { computed, ref, watch } from "vue";
import { useStore } from "vuex";
import ActionTabs, { ActionType } from './ActionTabs.vue';

export interface ActionWithTarget {
    action: Action,
    target: Equipment | Player | Hunter
}

const store = useStore();

// Computed properties from store
const selectedTarget = computed(() => store.getters['room/selectedTarget']);
const getSpaceWeaponAndActions = computed(() => store.getters['room/getSpaceWeaponAndActions']);
const actionTabs = computed(() => store.getters['settings/actionTabs']);
const player = computed(() => store.getters['player/player']);

// Local state
const activeTab = ref<ActionType>('human');
const doubleTapHandlers = new Map<string, () => void>();

// Computed
const getActionsWithTargets = computed((): ActionWithTarget[] => {
    // if we are in spaceBattle the action given by the patrolShip should remain visible at any time
    const actionsWithTarget = getSpaceWeaponAndActions.value.slice();

    // we need to add the actions provided by the current target
    // the target is different for patrolShip actions and target actions
    if (selectedTarget.value !== null) {
        for (let i = 0; i < selectedTarget.value.actions.length; i++) {
            const actionWithTargetToAdd = { action: selectedTarget.value.actions[i], target: selectedTarget.value } as ActionWithTarget;
            if (!actionsWithTarget.some((actionWithTarget: ActionWithTarget) => actionWithTarget.action.id === actionWithTargetToAdd.action.id)) {
                actionsWithTarget.push(actionWithTargetToAdd);
            }
        }
    }

    // we need to sort back the actions by point cost and name (yes, this is ugly)
    sortActionsByPointCostAndName(actionsWithTarget);

    return actionsWithTarget;
});

const targetActionsHuman = computed((): ActionWithTarget[] => {
    return getActionsWithTargets.value.filter(action => action.action.isMushAction == false && action.action.isAdminAction == false);
});

const targetActionsMush = computed((): ActionWithTarget[] => {
    return getActionsWithTargets.value.filter(action => action.action.isMushAction == true && action.action.isAdminAction == false);
});

const targetActionsAdmin = computed((): ActionWithTarget[] => {
    return getActionsWithTargets.value.filter(action => action.action.isAdminAction == true);
});

const getActions = computed((): ActionWithTarget[] => {
    if (!actionTabs.value) return getActionsWithTargets.value;

    const actionMap: { [key: string]: ActionWithTarget[] } = {
        'human': targetActionsHuman.value,
        'mush': targetActionsMush.value,
        'admin': targetActionsAdmin.value
    };

    return actionMap[activeTab.value];
});

// Methods
const executeActionWithTarget = async (actionWithTarget: ActionWithTarget): Promise<void> => {
    if (actionWithTarget.action.canExecute) {
        if (actionWithTarget.target === player.value) {
            await store.dispatch('action/executeAction', { target: null, action: actionWithTarget.action });
        } else {
            await store.dispatch('action/executeAction', { target: actionWithTarget.target, action: actionWithTarget.action });
        }
    }
};

const executeWithDoubleTap = async (actionWithTarget: ActionWithTarget): Promise<void> => {
    if (!store.getters['settings/doubleTap']) {
        await executeActionWithTarget(actionWithTarget);
        return;
    }

    const actionKey = actionWithTarget.action.key;
    if (!actionKey) return;

    const handlerKey = `${actionKey}_${actionWithTarget.target.id}`;

    if (!doubleTapHandlers.has(handlerKey)) {
        const { handleTap } = useDoubleTap(async () => {
            await executeActionWithTarget(actionWithTarget);
        });
        doubleTapHandlers.set(handlerKey, handleTap);
    }

    const handler = doubleTapHandlers.get(handlerKey);
    if (handler) {
        handler();
    }
};

const sortActionsByPointCostAndName = (actionsWithTarget: ActionWithTarget[]): ActionWithTarget[] => {
    return actionsWithTarget.sort((action1: ActionWithTarget, action2: ActionWithTarget) => {
        const a = action1.action;
        const b = action2.action;

        if (a.actionPointCost === null || b.actionPointCost === null) {
            throw new Error('ActionConfig point cost is null');
        }
        if (a.name === null || b.name === null) {
            throw new Error('ActionConfig name is null');
        }

        if (a.actionPointCost === b.actionPointCost) {
            return a.name.localeCompare(b.name);
        }
        return a.actionPointCost - b.actionPointCost;
    });
};

// Watch for target changes and reset activeTab
watch(selectedTarget, () => activeTab.value = 'human');
</script>

<style lang="scss" scoped>

.action-panel {
    position: relative;
    background: #222a6b;
}

.action-list {
    position: relative;

    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: space-between;
    padding: 12px 8px;
    min-height: 105px;
    align-content: flex-start;
    align-items: flex-start;

    &::before {
        content: "";
        position: absolute;
        top: 0;
        left: calc(50% - 8px);
        width: 0;
        height: 0;
        border-top: 8px solid #09092d;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
    }

    & > div { //targets tippy-generated divs
        flex-basis: 50%;
        margin: 0;
        display: inline-block;
        padding: 0px 3px;
        border: none;
    }
}
</style>
