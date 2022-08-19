<template>
    <input v-model="selectId"><button @click="selectNewChild">Add</button>
    <div>
        <ul class="flex-column">
            <li v-for="child in children" :key="child.id">{{ child.id }} - {{ child.name }} <span @click="removeChild(child)">x delete</span></li>
        </ul>
    </div>
</template>

<script>
import GameConfigService from "@/services/game_config.service";
import { removeItem } from "@/utils/misc";

export default {
    name: "ChildRelation",
    props: {
        children: Array
    },
    emits: ['update:children'],
    data: function () {
        return {
            selectId: 2
        };
    },
    methods: {
        selectNewChild() {
            GameConfigService.loadModifierCondition(this.selectId).then((res) => {
                const newValue = this.children;
                newValue.push(res);
                this.$emit('update:children', newValue);
            });
        },
        removeChild(child) {
            const newValue = removeItem(this.children, child);
            this.$emit('update:children', newValue);
        }
    },
    emits: ['update:children']
};
</script>

<style scoped>
.flex-column {
    flex-direction: column;
}
</style>