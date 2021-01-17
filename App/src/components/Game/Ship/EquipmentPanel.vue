<template>
    <ActionPanel
        class="equipment-panel"
        :actions="target.actions"
        @clickOnAction="executeTargetAction"
    />
</template>

<script>
import { mapActions } from "vuex";
import ActionPanel from "@/components/Game/Ship/ActionPanel";
import ActionService from "@/services/action.service";
import { Equipment } from "@/entities/Equipment";

export default {
    components: {
        ActionPanel
    },
    props: {
        target: Equipment
    },
    methods: {
        ...mapActions('player', [
            'reloadPlayer',
            'setLoading'
        ]),
        async executeTargetAction(action) {
            this.setLoading();
            await ActionService.executeTargetAction(this.target, action);
            await this.reloadPlayer();
        }
    }
};
</script>

<style lang="scss" scoped>

.equipment-panel {
    z-index: 5;
    position: absolute;
    bottom: 0;
    width: calc(100% - 16px);
}

</style>
