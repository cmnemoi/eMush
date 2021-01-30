<template>
    <div class="ship-panel">
        <div v-if="! loading" class="ship-panel-content">
            <p class="room">
                {{ room.name }}
                <Statuses :statuses="room.statuses" type="room" />
            </p>
            <TextualInterface
                class="ship-view"
                :room="room"
                @clickOnDoor="executeDoorAction"
                @clickOnInventory="openInventory"
                @clickOnTarget="setTarget"
                @clickOnNothing="setTarget(null)"
            />
            <MiniMap />
            <RoomInventoryPanel v-if="isInventoryOpen" :items="room.items" />
            <component
                :is="targetPanel"
                v-else-if="selectedTarget"
                :target="selectedTarget"
                @executeAction="executeTargetAction"
            />
        </div>
        <p v-else class="loading">
            Loading...
        </p>
    </div>
</template>

<script>
import CrewmatePanel from "@/components/Game/Ship/CrewmatePanel";
import EquipmentPanel from "@/components/Game/Ship/EquipmentPanel";
import MiniMap from "@/components/Game/Ship/MiniMap";
import RoomInventoryPanel from "@/components/Game/Ship/RoomInventoryPanel";
import Statuses from "@/components/Utils/Statuses";
import TextualInterface from "@/components/Game/Ship/TextualInterface";
import { Room } from "@/entities/Room";
import { Player } from "@/entities/Player";
import ActionService from "@/services/action.service";
import { mapActions, mapState } from "vuex";

export default {
    name: "ShipPanel",
    components: {
        CrewmatePanel,
        EquipmentPanel,
        MiniMap,
        RoomInventoryPanel,
        Statuses,
        TextualInterface
    },
    props: {
        room: Room
    },
    data() {
        return {
            isInventoryOpen: false,
            selectedTarget: null
        };
    },
    computed: {
        ...mapState('player', [
            'loading'
        ]),
        targetPanel() {
            return this.selectedTarget instanceof Player ? CrewmatePanel : EquipmentPanel;
        }
    },
    methods: {
        ...mapActions('communication', [
            'clearRoomLogs'
        ]),
        async executeDoorAction({ door, action }) {
            this.setLoading();
            this.isInventoryOpen = false;
            this.selectedTarget = null;
            this.clearRoomLogs();
            await ActionService.executeTargetAction(door, action);
            await this.reloadPlayer();
        },
        async executeTargetAction(action) {
            this.setLoading();
            await ActionService.executeTargetAction(this.selectedTarget, action);
            await this.reloadPlayer();
            this.selectedTarget = null;
        },
        setTarget(target) {
            this.selectedTarget = target;
            this.isInventoryOpen = false;
        },
        openInventory() {
            this.isInventoryOpen = true;
        },
        ...mapActions('player', [
            'reloadPlayer',
            'setLoading'
        ])
    }
};
</script>

<style lang="scss" scoped>

.ship-panel {
    position: relative;
    flex-direction: row;
    width: 424px;
    height: 460px;
    background: #09092d url("~@/assets/images/shipview/background.png") center repeat;

    .ship-panel-content {
        flex-direction: row;
    }

    & .ship-view {
        position: absolute;
        width: 100%;
        height: 100%;

        @include corner-bezel(6.5px, 6.5px, 0);
    }

    & .room {
        position: absolute;
        z-index: 5;
        margin: 8px 16px;
        font-family: 'Pixel-Square';
        font-size: 10px;
        font-weight: 700;
    }
}

</style>
