<template>
    <ul class="inventory">
        <li
            v-for="(item) in items"
            :key="item.id"
            class="slot"
            @click="$emit('select', item)"
        >
            <img :src="itemImage(item)" :alt="item.name">
            <span class="qty">{{ item.number }}</span>
        </li>
        <li v-for="n in emptySlots" :key="n" class="slot empty" />
    </ul>
</template>

<script>
import { itemEnum } from "@/enums/item";

export default {
    name: "Inventory",
    props: {
        items: Array,
        minSlot: Number
    },
    emits: [
        'select'
    ],
    computed: {
        emptySlots: function () {
            const emptySlots = (this.minSlot - this.items.length);
            return emptySlots < 0 ? 0 : emptySlots;
        }
    },
    methods: {
        itemImage: function(item) {
            return itemEnum[item.key] ? itemEnum[item.key].image : require('@/assets/images/items/todo.jpg');
        }
    }
};
</script>

<style lang="scss" scoped>
.inventory {
    display: flex;
    flex-direction: row;

    .slot {
        @include inventory-slot();
    }
}
</style>
