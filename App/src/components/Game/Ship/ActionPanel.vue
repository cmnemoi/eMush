<template>
    <div class="action-panel">
        <ActionButton
            v-for="(action, key) in actions"
            :key="key"
            class="action-button"
            :action="action"
            @click="$emit('clickOnAction', action)"
        />
    </div>
</template>

<script>
import ActionButton from "@/components/Utils/ActionButton";

export default {
    components: {
        ActionButton
    },
    props: {
        actions: Array
    },
    emits: [
        "clickOnAction"
    ]
};
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

    .action-button {
        flex-basis: 48%;
        margin: 0;
    }
}

</style>
