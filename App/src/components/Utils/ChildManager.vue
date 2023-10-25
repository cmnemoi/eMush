<template>
    <div class="children-add">
        <Input
            :label="$t('admin.childManager.idToAdd')"
            id="idToAdd"
            v-model="selectId"
            :type="mapIndexesType"
        ></Input>
        <button class="action-button" @click="$emit('addId', selectId)">{{$t('admin.buttons.add')}}</button>
    </div>
    <div class="children-container">
        <Pannel>
            <template #header>
                <slot name="header" v-bind="child"/>
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

export default {
    name: "ChildManager",
    components: {
        Pannel,
        Input
    },
    props: {
        child: Object,
        mapIndexesType: String
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
.children-add {
    flex-direction: row;
    align-items: center;
    gap: 0.4em;
    padding: 0.6em 0;
}
.children-container {
    flex-direction: row;
    flex-wrap: wrap;
    padding: 0.6em 0;
}
.header-container {
    flex-direction: row;
    justify-content: space-between;
}
.body-container { flex-direction: column; }

button, .action-button { min-width: 140px; }

</style>
