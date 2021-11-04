<template>
    <Tooltip>
        <template #tooltip-trigger>
            <a class="action-button" href="#">
                <span v-if="action.movementPointCost > 0">{{ action.movementPointCost }}<img src="@/assets/images/pm.png" alt="mp"></span>
                <span v-else-if="action.actionPointCost > 0">{{ action.actionPointCost }}<img src="@/assets/images/pa.png" alt="ap"></span>
                <span v-if="action.canExecute">{{ action.name }}</span>
                <span v-else><s>{{ action.name }}</s></span>
                <span v-if="action.successRate < 100" class="success-rate"> ({{ action.successRate }}%)</span>
            </a>
        </template>
        <template #tooltip-content="{ formatContent }">
            <h1 v-html="formatContent(action.name)" />
            <p v-html="formatContent(action.description)" />
            <!--<p v-else v-html="formatContent(action_fail.description)"> </p>-->
        </template>
    </Tooltip>
</template>

<script lang="ts">
import Tooltip from "@/components/Utils/ToolTip.vue";
import { defineComponent } from "vue";

export default defineComponent ({
    components: { Tooltip },
    props: {
        action: Object
    }
});
</script>

<style lang="scss" scoped>
.action-button {
    @include button-style();
}

.success-rate {
    margin-left: 5px;
}
</style>
