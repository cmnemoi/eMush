<template>
    <h3>{{ label }}</h3>
    <pre> {{ map }} </pre>
    <div class="flex-row">
        <label for="select">{{ $t("admin.mapManager.indexToDelete") }}</label>
        <select v-model="indexToDelete" id="select">
            <option v-for="[index,] in map" :value="index" v-bind:key="index">
                {{ index }}
            </option>
        </select>
        <button class="action-button" @click="$emit('removeIndex', indexToDelete)">{{ $t("admin.buttons.delete") }}</button>
    </div>
    <div class="flex-row">
        <Input
            :label="$t('admin.mapManager.mapIndexToAdd')"
            id="mapIndex"
            v-model="mapIndexToAdd"
            :type="mapIndexesType"
        ></Input>
        <Input
            :label="$t('admin.mapManager.mapValueToAdd')"
            id="mapValue"
            v-model="mapValueToAdd"
            :type="mapValuesType"
        ></Input>
        <button class="action-button" @click="$emit('addTuple', [mapIndexToAdd, mapValueToAdd])">
            {{ $t("admin.buttons.add") }}
        </button>
    </div>
</template>

<script lang="ts">
import Input from "@/components/Utils/Input.vue";

export default {
    name: "MapManager",
    components: {
        Input
    },
    props: {
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

.action-button {
    margin: 0 10px;
}
#select {
    margin-left: 10px;
}


</style>