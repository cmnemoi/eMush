<template>
    <ul class="inventory">
        <li
            v-for="(item) in items"
            :key="item.id"
            tabindex="0"
            class="slot"
            @click="$emit('select', item)"
        >
            <Tippy tag="div">
                <img :src="itemImage(item)" :alt="item.name">
                <span class="qty">{{ item.number }}</span>
                <template #content>
                    <h1>{{ item.name }}</h1>
                    <p v-html="formatDescription(item.description)" />
                    <span v-if="item.effectTitle">
                        {{item.effectTitle}}
                        <ul class="effect_list">
                            <li v-for="(effect, key) in item.effects" :key="key" v-html="formatContent(effect)"></li>
                        </ul>
                    </span>
                </template>
            </Tippy>
        </li>
        <li v-for="n in emptySlots" :key="n" class="slot empty" />
    </ul>
</template>

<script lang="ts">
import { itemEnum } from "@/enums/item";
import { Item } from "@/entities/Item";
import { formatText } from "@/utils/formatText";
import { defineComponent } from "vue";

export default defineComponent ({
    name: "Inventory",
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
        },
        formatDescription(value: string): string {
            if (! value) return '';
            return formatText(value.toString());
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

.effect_list {
    display: flex;
    flex-direction: column;
}
</style>
