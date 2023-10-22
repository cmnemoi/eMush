<template>
    <div class="flex-row index-addition">
        <div v-if="selection" class="select-default">
            <label v-if="label" :for="id">{{ label }}</label>
            <select :id="id" v-model="element">
                <option
                    v-for="i in selection"
                    :value="i"
                    v-bind:key="i"
                >
                    {{ i }}
                </option>
            </select>
        </div>
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

.select-default {
    width: 31%;
    min-width: 200px;
    align-self: flex-end;

    label {
        padding: 0 0.8em;
        transform: translateY(0.45em);
        word-break: break-word;
    }

    select {
        min-width: 5em;
        padding: 0.3em 0.6em;
        font-size: 1.3em;
        color: white;
        background: #222b6b;
        border: 1px solid transparentize(white, 0.8);
        border-radius: 1px;

        &:focus {
            outline: none;
            box-shadow: 0 0 0 3px transparentize(white, 0.85);
        }
    }
}

button, .action-button { min-width: 140px; }

</style>