<template>
    <Tippy tag="div">
        <a 
            :class="isDisabled"
            class="action-button"
            href="#">
            <span v-if="action.movementPointCost > 0 && !action.actionPointCost" class="cost">{{ action.movementPointCost }}<img src="@/assets/images/pm.png" alt="mp"></span>
            <span v-else-if="action.actionPointCost > 0 && !action.movementPointCost" class="cost">{{ action.actionPointCost }}<img src="@/assets/images/pa.png" alt="ap"></span>
            <span v-else-if="action.actionPointCost > 0 && action.movementPointCost > 0" class="cost">{{ action.actionPointCost }}<img src="@/assets/images/pa.png" alt="ap">{{ action.movementPointCost }}<img src="@/assets/images/pm.png" alt="mp"></span>
            <span v-if="action.canExecute">{{ addParamsToActionName(action.name, [params?.chosenOrientation]) }}</span>
            <span v-else><s>{{ addParamsToActionName(action.name, [params?.chosenOrientation]) }}</s></span>
            <span v-if="action.successRate < 100" class="success-rate"> ({{ action.successRate }}%)</span>
        </a>
        <template #content>
            <h1 v-html="formatContent(addParamsToActionName(action.name, [params?.chosenOrientation]))" /><br />
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
        params: {
            type: Object,
            required: false
        }
    },
    computed:
        {
            isDisabled(): string
            {
                return !this.action?.canExecute ? "disabled" : "";
            },
        },
    methods: {
        addParamsToActionName(name: string, params: string[]): string {
            return [name, ...params].join(' ');
        },
    }
    
});
</script>

<style lang="scss" scoped>
.action-button {
    @include button-style();
    display: block;
    margin: 0.2rem;
}
</style>
