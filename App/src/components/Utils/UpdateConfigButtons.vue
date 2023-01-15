<template>
    <div class="flex-row">
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