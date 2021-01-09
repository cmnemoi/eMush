<template>
    <a class="action-button" href="#">
        <span v-if="action.movementPointCost > 0">{{ action.movementPointCost }}<img src="@/assets/images/pm.png" alt="mp"></span>
        <span v-else-if="action.actionPointCost > 0">{{ action.actionPointCost }}<img src="@/assets/images/pa.png" alt="ap"></span>
        {{ action.name }}
    </a>
</template>

<script>
export default {
    props: {
        action: Object
    }
};
</script>

<style lang="scss" scoped>
.action-button {
    @include button-style();
}
</style>
