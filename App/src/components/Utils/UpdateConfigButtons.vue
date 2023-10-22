<template>
    <div class="flex-row actions">
        <button
            v-if="update"
            class="action-button"
            type="submit"
            @click="$emit('update')"
        >
            {{ $t('admin.buttons.updateConfig') }}
        </button>
        <button
            v-if="create"
            class="action-button"
            type="submit"
            @click="$emit('create')"
        >
            {{ $t('admin.buttons.createNewConfig') }}
        </button>
    </div>
</template>

<script lang="ts">

export default {
    name: "UpdateConfigButtons",
    emits: ['update', 'create'],
    props: {
        update: {
            type: Boolean,
            default: true
        },
        create: {
            type: Boolean,
            default: true
        }
    }
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
