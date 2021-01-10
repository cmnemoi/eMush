<template>
    <div class="inventory-container">
        <Inventory
            class="inventory"
            :items="items"
            :min-slot="7"
            @select="selectItem"
        />
        <div v-if="selectedItem" class="item-details">
            <p class="item-name">
                {{ selectedItem.name }}
                <span v-for="(status, key) in selectedItem.statuses" :key="key">
                    <img :src="statusIcon(status)">
                    <span v-if="status.charge > 0">x{{ status.charge }}</span>
                </span>
            </p>
            <div class="item-actions">
                <ActionButton
                    v-for="(action, key) in selectedItem.actions"
                    :key="key"
                    class="item-action-button"
                    :action="action"
                    @click="executeItemAction(action)"
                />
            </div>
        </div>
    </div>
</template>

<script>
import Inventory from "@/components/Game/Inventory";
import ActionButton from "@/components/Utils/ActionButton";
import ActionService from "@/services/action.service";
import { mapActions } from "vuex";
import { statusItemEnum } from "@/enums/status.item.enum";

export default {
    name: "RoomInventoryPanel",
    components: {
        Inventory,
        ActionButton
    },
    props: {
        items: Array
    },
    data: () => {
        return {
            selectedItem: null
        };
    },
    methods: {
        selectItem: function(item) {
            this.selectedItem = item;
        },
        statusIcon: function(status) {
            const statusImages = statusItemEnum[status.key];
            return typeof statusImages !== 'undefined' ? statusImages.icon : null;
        },
        async executeItemAction(action) {
            this.setLoading();
            await ActionService.executeItemAction(this.selectedItem, action);
            await this.reloadPlayer();
        },
        ...mapActions('player', [
            'reloadPlayer',
            'setLoading'
        ])
    }
};
</script>

<style lang="scss" scoped>

.inventory-container {
    z-index: 5;
    position: absolute;
    bottom: 0;
    width: 100%;
    padding-right: 16px; // for textual scrollbar

    & .item-name {
        text-align: center;
        font-size: 0.85em;
        font-variant: small-caps;
        margin: 0;
        padding: 8px 0;
        background: #09092d;

        img {
            vertical-align: middle;
            margin-left: 2px;
        }
    }

    .item-actions {
        position: relative;
        background: #222a6b;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: space-between;
        padding: 12px 8px;
        min-height: 105px;
        align-content: flex-start;
        align-items: flex-start;

        &::before {
            content: "";
            position: absolute;
            top: 0;
            left: calc(50% - 8px);
            width: 0;
            height: 0;
            border-top: 8px solid #09092d;
            border-left: 8px solid transparent;
            border-right: 8px solid transparent;
        }

        .item-action-button {
            flex-basis: 48%;
            margin: 0;
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
