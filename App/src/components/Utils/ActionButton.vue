<template>
    <Tippy tag="div">
        <a
            :class="['action-button', cssClass, isDisabled].join(' ')"
            href="#">
            <span v-if="action.movementPointCost > 0 && !action.actionPointCost" class="cost">{{ action.movementPointCost }}<img src="@/assets/images/pm.png" alt="mp"></span>
            <span v-else-if="action.actionPointCost > 0 && !action.movementPointCost" class="cost">{{ action.actionPointCost }}<img src="@/assets/images/pa.png" alt="ap"></span>
            <span v-else-if="action.actionPointCost > 0 && action.movementPointCost > 0" class="cost">{{ action.actionPointCost }}<img src="@/assets/images/pa.png" alt="ap">{{ action.movementPointCost }}<img src="@/assets/images/pm.png" alt="mp"></span>
            <span v-if="action.canExecute">{{ action.name }}</span>
            <span v-else><s>{{ action.name }}</s></span>
            <span v-if="action.successRate < 100" class="success-rate"> ({{ action.successRate }}%)</span>
        </a>
        <template #content>
            <h1 v-html="formatContent(action.name)" />
            <p v-html="formatContent(action.description)" />
            <!--<p v-else v-html="formatContent(action_fail.description)"> </p>-->
        </template>
    </Tippy>
</template>

<script lang="ts">
import { defineComponent } from "vue";

export default defineComponent ({
    props: {
        action: Object,
        cssClass: String
    },
    computed:
        {
            isDisabled(): string
            {
                return !this.action?.canExecute ? "disabled" : "";
            }
        }
});
</script>

<style lang="scss" scoped>
.action-button {
    @include button-style();
    display: block;
    margin: 0.2rem;
}
.wide {
    min-width: 10em;
}
</style>
