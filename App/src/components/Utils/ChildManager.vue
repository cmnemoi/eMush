<template>
    <div class="children-add">
        <label>{{$t('admin.childManager.idToAdd')}}</label><input v-model="selectId"><button class="action-button" @click="$emit('addId', selectId)">{{$t('admin.buttons.add')}}</button>
    </div>
    <div class="children-container">
        <Pannel>
            <template #header>
                <div class="header-container">
                    <slot name="header" v-bind="child"/>
                </div>
            </template>
            <template #body>
                <div class="body-container">
                    <slot name="body" v-bind="child"/>
                </div>
            </template>
            <template #footer>
                <div class="footer-container">
                    <slot name="footer" v-bind="child"/>
                </div>
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
.body-container {
    flex-direction: column;
}
</style>
