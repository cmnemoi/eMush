<template>
    <div class="flex-row actions">
        <button class="action-button" type="submit" @click="$emit('update')">
            {{ $t('admin.buttons.updateConfig') }}
        </button>
        <button class="action-button" type="submit" @click="$emit('create')">
            {{ $t('admin.buttons.createNewConfig') }}
        </button>
    </div>
</template>

<script lang="ts">

export default {
    name: "UpdateConfigButtons",
    emits: ['update', 'create']
};

</script>

<style lang="scss" scoped>

.actions {
    justify-content: center;
    flex-flow: wrap;
    margin-top: 4em;

    button {
        padding: .4em .8em;
    }
}

</style>
