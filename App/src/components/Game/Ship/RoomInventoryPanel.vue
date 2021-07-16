<template>
    <div class="inventory-container">
        <Inventory
            class="inventory"
            :items="items"
            :min-slot="7"
            @select="selectItem"
        />
        <div class="item-details">
            <div class="name-container">
                <p v-if="selectedItem" class="item-name">
                    {{ selectedItem.name }}
                    <Statuses :statuses="selectedItem.statuses" type="item" />
                </p>
            </div>
            <ActionPanel
                :actions="selectedItem?.actions || []"
                @clickOnAction="executeItemAction"
            />
        </div>
    </div>
</template>

<script lang="ts">
import Inventory from "@/components/Game/Inventory.vue";
import ActionPanel from "@/components/Game/Ship/ActionPanel.vue";
import Statuses from "@/components/Utils/Statuses.vue";
import { mapActions } from "vuex";
import { defineComponent } from "vue";
import { Action } from "@/entities/Action";
import { Item } from "@/entities/Item";

interface RoomInventoryPanelState {
    selectedItem: null | Item
}

export default defineComponent ({
    name: "RoomInventoryPanel",
    components: {
        ActionPanel,
        Inventory,
        Statuses
    },
    props: {
        items: Array
    },
    data: () : RoomInventoryPanelState => {
        return {
            selectedItem: null
        };
    },
    methods: {
        selectItem: function(item: Item) {
            this.selectedItem = item;
        },
        async executeItemAction(action: Action) {
            this.executeAction({ target: this.selectedItem, action });
        },
        ...mapActions('action', [
            'executeAction'
        ])
    }
});
</script>

<style lang="scss" scoped>

.inventory-container {
    z-index: 5;
    position: absolute;
    bottom: 0;
    width: 100%;
    padding-right: 16px; // for textual scrollbar

    .name-container {
        background: #09092d;
        height: 32px;

        .item-name {
            text-align: center;
            font-size: 0.85em;
            font-variant: small-caps;
            margin: 0;
            padding: 8px 0;

            >>> .status {
                vertical-align: middle;
                margin-left: 2px;
            }
        }
    }

    .inventory {
        overflow: hidden;
        overflow-x: scroll;

        /* SCROLLBAR STYLING */

        --scrollbarBG: rgba(0, 0, 0, 0.4);
        --thumbBG: rgba(0, 116, 223, 1);
        --border-radius: 4px;

        scrollbar-width: thin;
        scrollbar-color: var(--thumbBG) var(--scrollbarBG);

        &::-webkit-scrollbar {
            height: 8px;
            border-radius: var(--border-radius);
        }

        &::-webkit-scrollbar-track {
            background: var(--scrollbarBG);
            border-radius: var(--border-radius);
        }

        &::-webkit-scrollbar-thumb {
            background-color: var(--thumbBG);
            border-radius: var(--border-radius);
        }
    }
}
</style>
