<template>
    <pre>{{ array }}</pre>
    <div class="flex-row">
        <label id="label">{{ $t('admin.stringArrayManager.elementToAdd') }}</label>
        <input v-model="element" />
        <button class="action-button" @click="$emit('addElement', element)">{{$t('admin.buttons.add')}}</button>
        <button class="action-button" @click="$emit('removeElement', element)">{{$t('admin.buttons.delete')}}</button>
    </div>
</template>

<script lang="ts">

export default {
    name: "StringArrayManager",
    props: {
        array: Array<string>
    },
    emits: ['addElement', 'removeElement'],
    data: function () {
        return {
            element: null
        };
    }
};
</script>

<style lang="scss" scoped>

#label {
    margin-right: 10px;
}


</style>