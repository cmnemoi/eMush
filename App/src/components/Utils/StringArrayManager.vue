<template>
    <pre>{{ array }}</pre>
    <div class="flex-row index-addition">
        <Input
            :label="$t('admin.stringArrayManager.elementToAdd')"
            id="mapIndex"
            v-model="element"
            :type="mapIndexesType"
        ></Input>
        <button class="action-button" @click="$emit('addElement', element)">{{$t('admin.buttons.add')}}</button>
        <button class="action-button" @click="$emit('removeElement', element)">{{$t('admin.buttons.delete')}}</button>
    </div>
</template>

<script lang="ts">
import Input from "@/components/Utils/Input.vue";

export default {
    name: "StringArrayManager",
    components: {
        Input
    },
    props: {
        array: Array<string>,
        mapIndexesType: String,
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

pre {
    padding: 0.8em 1.4em;
    line-height: 1.5em;
    background-color: transparentize(black, 0.8);
    border: 1px solid transparentize(white, 0.8);
    border-radius: 5px;
}

.index-addition {
    align-items: center;
    gap: 0.4em;
}

button, .action-button { min-width: 140px; }

</style>