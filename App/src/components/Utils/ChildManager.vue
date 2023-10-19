<template>
    <div class="children-add">
        <label>{{$t('admin.childManager.idToAdd')}}</label><input v-model="selectId"><button class="action-button" @click="$emit('addId', selectId)">{{$t('admin.buttons.add')}}</button>
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

export default {
    name: "ChildManager",
    components: {
        Pannel
    },
    props: {
        child: Object,
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
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 0.4em;
    padding: 10px;
}
.children-container {
    padding: 10px;
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
}
.header-container {
    flex-direction: row;
    justify-content: space-between;
}
.body-container { flex-direction: column; }

button, .action-button { min-width: 140px; }

</style>
