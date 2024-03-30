<template>
    <div class="children-add">
        <Input
            :label="$t('admin.childCollectionManager.idToAdd')"
            :id="id"
            v-model="selectId"
            :type="mapIndexesType"
        ></Input>
        <button class="action-button" @click="$emit('addId', selectId)">{{$t('admin.buttons.add')}}</button>
    </div>
    <div class="children-container">
        <Pannel v-if="children == ''" class="empty">
            <template #header>
                <span><em>No item to display</em></span>
            </template>
        </Pannel>
        <Pannel v-for="child in children" :key="child.id">
            <template #header>
                <slot name="header" v-bind="child"/>
                <button class="icon" @click="$emit('remove', child)"><img :src="getAssetUrl('bin.png')" :alt="$t('admin.buttons.delete')" :title="$t('admin.buttons.delete')"></button>
            </template>
            <template #body>
                <slot name="body" v-bind="child"/>
            </template>
            <template #footer>
                <slot name="footer" v-bind="child"/>
            </template>
        </Pannel>
    </div>
</template>

<script>
import Pannel from "@/components/Utils/Pannel";
import Input from "@/components/Utils/Input.vue";
import { getAssetUrl } from "@/utils/getAssetUrl";

export default {
    name: "ChildCollectionManager",
    components: {
        Pannel,
        Input
    },
    props: {
        children: Array,
        mapIndexesType: String,
        id: {
            type: String,
            required: true
        }
    },
    methods: {
        getAssetUrl
    },
    emits: ['addId', 'remove'],
    data: function () {
        return {
            selectId: null
        };
    }
};
</script>

<style lang="scss" scoped>

.children-add, .children-container {
    flex-direction: row;
    margin: 0.6em 0;
}

.children-add {
    align-items: center;
    gap: 0.4em;
}
.children-container {
    flex-wrap: wrap;
    gap: 1.2em;
}

.header-container {
    flex-direction: row;
    justify-content: space-between;
}

.body-container { flex-direction: column; }

button:not(.icon), .action-button { min-width: 140px; }

.icon {
    padding: 0.2em;
    z-index: 2;
    background-color: transparentize(white, 0.8);
    border-radius: 0 3px 3px 0;

    &:hover, &:focus, &:active { background-color: $red; }

    img { margin: auto; }
}

.empty {
    flex: 1;
    text-align: center;
    border: 0;
    opacity: 0.7;

    span { cursor: default !important; }
}

</style>
