<template>
    <div class="action-panel">
        <ActionButton
            v-for="(action, key) in getActions"
            :key="key"
            :action="action"
            @mousedown="executeTargetAction(action)"
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

interface AlertsState {
    loading: boolean,
    selectedTarget: Equipment | Player | Hunter
}

export default defineComponent ({
    components: {
        ActionButton
    },
    computed: {
        ...mapGetters('room', [
            'selectedTarget'
        ]),
        getActions(): Action[]
        {
            if (this.selectedTarget === null) { return [];}
            return this.selectedTarget.actions;
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
        async executeTargetAction(action: Action) {
            if (action.canExecute){
                if (this.selectedTarget === this.player) {
                    await this.executeAction({ target: null, action });
                } else {
                    await this.executeAction({ target: this.selectedTarget, action });
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
