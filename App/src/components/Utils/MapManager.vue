<template>
    <h3 v-if="label">{{ label }}</h3>
    <div class="flex-row index-deletion">
        <div class="select-default">
            <label for="select">{{ $t("admin.mapManager.indexToDelete") }}</label>
            <select v-model="indexToDelete" id="select">
                <option v-for="[index,] in map" :value="index" v-bind:key="index">
                    {{ index }}
                </option>
            </select>
        </div>
        <button class="action-button" @click="$emit('removeIndex', indexToDelete)">{{ $t("admin.buttons.delete") }}</button>
    </div>
    <div class="flex-row index-addition">
        <Input
            :label="$t('admin.mapManager.mapIndexToAdd')"
            :id="id + '_index'"
            v-model="mapIndexToAdd"
            :type="mapIndexesType"
        ></Input>
        <Input
            :label="$t('admin.mapManager.mapValueToAdd')"
            :id="id + '_value'"
            v-model="mapValueToAdd"
            :type="mapValuesType"
        ></Input>
        <button class="action-button" @click="$emit('addTuple', [mapIndexToAdd, mapValueToAdd])">
            {{ $t("admin.buttons.add") }}
        </button>
    </div>
    <pre> {{ map }} </pre>
</template>

<script lang="ts">
import Input from "@/components/Utils/Input.vue";

export default {
    name: "MapManager",
    components: {
        Input
    },
    props: {
        id: {
            type: String,
            required: true
        },
        label: String,
        map: Map,
        mapIndexesType: String,
        mapValuesType: String
    },
    emits: ['addTuple', 'removeIndex'],
    data: function () {
        return {
            indexToDelete: null,
            mapIndexToAdd: null,
            mapValueToAdd: null
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

.index-deletion, .index-addition {
    flex-wrap: wrap;
    align-items: center;
    gap: 1.2em;
}

button, .action-button { min-width: 140px; }

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

</style>