<template>
    <ActionPanel
        class="equipment-panel"
        :actions="target.actions"
        @clickOnAction="action => $emit('executeAction', action)"
    />
</template>

<script lang="ts">
import ActionPanel from "@/components/Game/Ship/ActionPanel.vue";
import { Equipment } from "@/entities/Equipment";
import { defineComponent } from "vue";

export default defineComponent ({
    components: {
        ActionPanel
    },
    props: {
        target: Equipment
    },
    emits: [
        'executeAction'
    ]
});
</script>

<style lang="scss" scoped>

.equipment-panel {
    z-index: 5;
    position: absolute;
    bottom: 0;
    width: calc(100% - 16px);
}

</style>
