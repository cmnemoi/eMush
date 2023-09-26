<template>
    <div class="inventory-container">
        <Inventory
            class="inventory"
            :items="items"
            :min-slot="7"
            :selectedItem="getSelectedItem"
            @select="selectItem"
        />
        <div class="item-details">
            <div class="name-container">
                <p v-if="getSelectedItem" class="item-name">
                    {{ getSelectedItem.name }}
                    <Statuses :statuses="getSelectedItem.statuses" type="item" />
                </p>
            </div>
            <ActionPanel v-if="getSelectedItem !== null"/>
        </div>
    </div>
</template>

<script lang="ts">
import Inventory from "@/components/Game/Inventory.vue";
import ActionPanel from "@/components/Game/Ship/ActionPanel.vue";
import Statuses from "@/components/Utils/Statuses.vue";
import { mapActions, mapGetters } from "vuex";
import { defineComponent } from "vue";
import { Action } from "@/entities/Action";
import { Item } from "@/entities/Item";


export default defineComponent ({
    name: "RoomInventoryPanel",
    components: {
        ActionPanel,
        Inventory,
        Statuses
    },
    computed: {
        ...mapGetters('room', [
            'selectedTarget'
        ]),
        getSelectedItem(): Item | null
        {
            if (this.selectedTarget instanceof Item) { return this.selectedTarget;}
            return null;
        }
    },
    props: {
        items: Array
    },
    methods: {
        ...mapActions({
            'executeAction': 'action/executeAction',
            'selectTarget': 'room/selectTarget'
        }),
        async executeItemAction(action: Action) {
            this.executeAction({ target: this.getSelectedItem, action });
        },
        selectItem(target: Item | null): void {
            this.selectTarget({ target: target });
        }
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
            font-variant: small-caps;
            margin: 0;
            padding: 8px 0;

            &::v-deep .status {
                vertical-align: middle;
                margin-left: 2px;
            }
        }
    }
}

.inventory {
    overflow: hidden;
    overflow-x: scroll;

    @extend %game-scrollbar;
    --scrollbarBG: rgba(0, 0, 0, 0.4);
    scrollbar-width: thin;
    &::-webkit-scrollbar {
        height: 8px;
    }
}
</style>
