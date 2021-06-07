<template>
    <a class="action-button" href="#">
        <Tooltip :title="action.name" :content="action.description">
            <template v-slot:tooltip-trigger>
                <span v-if="action.movementPointCost > 0">{{ action.movementPointCost }}<img src="@/assets/images/pm.png" alt="mp"></span>
                <span v-else-if="action.actionPointCost > 0">{{ action.actionPointCost }}<img src="@/assets/images/pa.png" alt="ap"></span>
                <span v-if="action.canExecute">{{ action.name }}</span>
                <span v-else><s>{{ action.name }}</s></span>
                <span v-if="action.successRate < 100" class="success-rate"> ({{ action.successRate }}%)</span>
            </template>
        </Tooltip>
    </a>
</template>

<script>
import Tooltip from "@/components/Utils/ToolTip";

export default {
    props: {
        action: Object
    },
    components: {Tooltip}
};
</script>

<style lang="scss" scoped>
.action-button {
    @include button-style();
}

.success-rate {
    margin-left: 5px;
}
</style>
