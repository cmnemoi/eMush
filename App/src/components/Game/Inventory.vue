<template>
    <ul class="inventory" @wheel="ScrollIcons($event)">
        <li
            v-for="(item) in items"
            :key="item.id"
            tabindex="0"
            class="slot"
            :class="isItemSelected(item) ? 'highlight' : ''"
            @mousedown.stop="$emit('select', item)"
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
        <li
            v-for="n in emptySlots"
            :key="n"
            class="slot empty"
            @mousedown.stop="$emit('select', null)"
        />
    </ul>
</template>

<script lang="ts">
import { itemEnum } from "@/enums/item";
import { Item } from "@/entities/Item";
import { formatText } from "@/utils/formatText";
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";

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
        },
        selectedItem: {
            type: Item,
            required: false,
            default: null
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
            return itemEnum[item.key] ? itemEnum[item.key].image : getImgUrl('items/todo.png');
        },
        formatDescription(value: string): string {
            if (! value) return '';
            return formatText(value.toString());
        },
        isItemSelected: function(item: Item): boolean {
            return this.selectedItem instanceof Item && this.selectedItem.id === item.id;
        },
        ScrollIcons(event: any) {
            event.preventDefault();
            event.currentTarget.scrollLeft += event.deltaY * 0.28;
        }
    }
});
</script>

<style lang="scss" scoped>
.inventory {
    display: flex;
    flex-direction: row;
    overflow: hidden;
    overflow-x: auto;

    @extend %game-scrollbar;
    --scrollbarBG: rgba(0, 0, 0, 0.4);
    scrollbar-width: thin;
    &::-webkit-scrollbar {
        height: 8px;
    }

    .slot {
        @include inventory-slot();
    }
    .highlight::before {
        content: "";
        position: absolute;
        pointer-events: none;
        z-index: 5;
        width: 54px;
        height: 54px;
        border: 1px solid rgb(153, 255, 153);
        box-shadow: 0 0 0 2px inset rgb(153, 255, 153), 0 0 8px 2px inset rgb(17, 56, 128);
    }
}

.effect_list {
    display: flex;
    flex-direction: column;
}
</style>
