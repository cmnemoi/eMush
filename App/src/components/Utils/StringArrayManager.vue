<template>
    <div class="flex-row index-addition">
        <label v-if="label" :for="id">{{ label }}</label>
        <select v-if="selection" :id="id" v-model="element">
            <option
                v-for="i in selection"
                :value="i"
                v-bind:key="i"
            >
                {{ i }}
            </option>
        </select>
        <Input
            v-else
            :label="label == null ? $t('admin.stringArrayManager.elementToAdd') : label"
            :id="id"
            v-model="element"
            :type="mapIndexesType"
        ></Input>
        <button class="action-button" @click="$emit('addElement', element)">{{$t('admin.buttons.add')}}</button>
        <button class="action-button" @click="$emit('removeElement', element)">{{$t('admin.buttons.delete')}}</button>
    </div>
    <pre>{{ array }}</pre>
</template>

<script lang="ts">
import Input from "@/components/Utils/Input.vue";

export default {
    name: "StringArrayManager",
    components: {
        Input
    },
    props: {
        id: {
            type: String,
            required: true
        },
        label: {
            type: String,
            required: false,
        },
        selection: {
            type: Array<string>,
            required: false
        },
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
    border-radius: 4px;
}

.index-addition {
    align-items: center;
    gap: 0.4em;
}

select {
    min-width: 5em;
    color: white;
    padding: 0.3em 0.6em;
    background: #222b6b;
    border: 1px solid transparentize(white, 0.8);
}

button, .action-button { min-width: 140px; }

</style>