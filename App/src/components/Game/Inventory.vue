<template>
    <div class="inventory" @wheel="ScrollIcons($event)">
        <div
            v-for="item in items"
            :key="`${item.id}-${item.description}`"
            tabindex="0"
            class="slot"
            :class="[isItemSelected(item) ? 'highlight' : '', isHidden(item) ? 'hidden' : '']"
            @mousedown.stop="$emit('select', item)"
        >
            <Tippy tag="div">
                <img :src="itemImage(item)" :alt="item.name"/>
                <div v-if="!hideStatusesOnItem" class="statuses">
                    <Statuses :statuses="item.statuses" type="item" on-item/>
                </div>
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
        </div>
        <div
            v-for="n in emptySlots"
            :key="n"
            class="slot empty"
            @mousedown.stop="$emit('select', null)"
        />
    </div>
</template>

<script lang="ts">
import { itemEnum } from "@/enums/item";
import { Item } from "@/entities/Item";
import { formatText } from "@/utils/formatText";
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { Tippy } from "vue-tippy";
import Statuses from "@/components/Utils/Statuses.vue";
import { StatusItemNameEnum } from "@/enums/status.item.enum";
import { mapGetters } from "vuex";

export default defineComponent ({
    name: "Inventory",
    components: {
        Statuses,
        Tippy
    },
    props: {
        items: {
            type: Array<Item>,
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
        ...mapGetters('settings', ['hideStatusesOnItem']),
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
        },
        isHidden(item: Item): boolean {
            return item.statuses.some(status => status.key === StatusItemNameEnum.HIDDEN) && !this.hideStatusesOnItem;
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
        top: -1px;
        left: -1px;
        pointer-events: none;
        z-index: 5;
        width: 54px;
        height: 54px;
        border: 2px solid rgb(153, 255, 153);
        border-radius: 3px;
    }

    .hidden {
        position: relative;
    }
    .hidden::before {
        content: "";
        position: absolute;
        top: -1px;
        left: -1px;
        pointer-events: none;
        z-index: 5;
        width: 55px;
        height: 55px;
        border-radius: 3px;
        box-shadow: inset 0 0 15px 3px rgba(0, 0, 0, 0.95);
    }
}

.effect_list {
    display: flex;
    flex-direction: column;
}
</style>
