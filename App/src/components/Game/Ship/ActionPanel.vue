<template>
    <div class="action-panel">
        <ActionButton
            v-for="(actionWithTarget, key) in getActionsWithTargets"
            :key="key"
            :action="actionWithTarget.action"
            @mousedown="executeActionWithTarget(actionWithTarget)"
        />
    </div>
</template>

<script lang="ts">
import ActionButton from "@/components/Utils/ActionButton.vue";
import { defineComponent } from "vue";
import { mapActions, mapGetters } from "vuex";
import { Player } from "@/entities/Player";
import { Action } from "@/entities/Action";
import { Equipment } from "@/entities/Equipment";
import { Hunter } from "@/entities/Hunter";

interface ActionWithTarget {
    action: Action,
    target: Equipment | Player | Hunter
}

export default defineComponent ({
    components: {
        ActionButton
    },
    computed: {
        ...mapGetters('room', [
            'selectedTarget',
            'getSpaceWeaponAndActions'
        ]),
        getActionsWithTargets(): ActionWithTarget[]
        {
            // if we are in spaceBattle the action given by the patrolShip should remain visible at any time
            const actionsWithTarget = this.getSpaceWeaponAndActions.slice();

            // we need to add the actions provided by the current target
            // the target is different for patrolShip actions and target actions
            if (this.selectedTarget !== null) {
                for (let i = 0; i < this.selectedTarget.actions.length; i++) {
                    const actionWithTargetToAdd = { action: this.selectedTarget.actions[i], target: this.selectedTarget } as ActionWithTarget;
                    if (!actionsWithTarget.some((actionWithTarget: ActionWithTarget) => actionWithTarget.action.id === actionWithTargetToAdd.action.id)) {
                        actionsWithTarget.push(actionWithTargetToAdd);
                    }
                }
            }

            return actionsWithTarget;
        },
        ...mapGetters('player', [
            'player'
        ])
    },
    props: {
        actions: Array
    },
    methods: {
        ...mapActions({
            'executeAction': 'action/executeAction'
        }),
        async executeActionWithTarget(actionWithTarget: ActionWithTarget): Promise<void> {
            if (actionWithTarget.action.canExecute){
                if (actionWithTarget.target === this.player) {
                    await this.executeAction({ target: null, action: actionWithTarget.action });
                } else {
                    await this.executeAction({ target: actionWithTarget.target, action: actionWithTarget.action });
                }
            }
        }
    }
});
</script>

<style lang="scss" scoped>

.action-panel {
    position: relative;
    background: #222a6b;
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
