<template>
    <ul class="inventory">
        <li
            v-for="(item) in items"
            :key="item.id"
            tabindex="0"
            class="slot"
            @click="$emit('select', item)"
        >
            <Tooltip>
                <template #tooltip-trigger>
                    <img :src="itemImage(item)" :alt="item.name">
                    <span class="qty">{{ item.number }}</span>
                </template>
                <template #tooltip-content>
                    <h1>{{ item.name }}</h1>
                    <p>{{ item.description }}</p>
                </template>
            </Tooltip>
        </li>
        <li v-for="n in emptySlots" :key="n" class="slot empty" />
    </ul>
</template>

<script lang="ts">
import { itemEnum } from "@/enums/item";
import Tooltip from "@/components/Utils/ToolTip.vue";
import { Item } from "@/entities/Item";
import { defineComponent } from "vue";

export default defineComponent ({
    name: "Inventory",
    components: { Tooltip },
    props: {
        items: {
            type: Array,
            required: true
        },
        minSlot: {
            type: Number,
            required: true
        }
    },
    emits: [
        'select'
    ],
    computed: {
        emptySlots: function (): number {
            const emptySlots = (this.minSlot - this.items.length);
            return emptySlots < 0 ? 0 : emptySlots;
        }
    },
    methods: {
        itemImage: function(item: Item): string {
            return itemEnum[item.key] ? itemEnum[item.key].image : require('@/assets/images/items/todo.jpg');
        }
    }
});
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
