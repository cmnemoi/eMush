<template>
    <div class="ship-panel">
        <div v-if="! loading" class="ship-panel-content">
            <p class="room">
                {{ room.name }}
                <Statuses :statuses="room.statuses" type="room" />
            </p>
            <PhaserShip
                :player="player"
            />
            <MiniMap
                v-if="isMinimapAvailable"
                :my-position="room"
                :minimap="player.daedalus.minimap"
            />
            <RoomInventoryPanel v-if="isInventoryOpen" :items="room.items" />
            <component
                :is="targetPanel"
                v-else-if="selectedTarget"
                :target="selectedTarget"
            />
        </div>
        <p v-else class="loading">
            {{ $t('loading') }}
        </p>
    </div>
</template>

<script lang="ts">
import CrewmatePanel from "@/components/Game/Ship/CrewmatePanel.vue";
import EquipmentPanel from "@/components/Game/Ship/EquipmentPanel.vue";
import MiniMap from "@/components/Game/Ship/MiniMap.vue";
import RoomInventoryPanel from "@/components/Game/Ship/RoomInventoryPanel.vue";
import Statuses from "@/components/Utils/Statuses.vue";
import TextualInterface from "@/components/Game/Ship/TextualInterface.vue";
import { PhaserRooms, Room } from "@/entities/Room";
import { Player } from "@/entities/Player";
import { mapActions, mapState, mapGetters } from "vuex";
import PhaserShip from "@/components/Game/Ship/PhaserShip.vue";
import { defineComponent } from "vue";
import { Action } from "@/entities/Action";
import { Equipment } from "@/entities/Equipment";

export default defineComponent ({
    name: "ShipPanel",
    components: {
        PhaserShip,
        CrewmatePanel,
        EquipmentPanel,
        MiniMap,
        RoomInventoryPanel,
        Statuses,
        TextualInterface
    },
    props: {
        room: Room,
        player: Player
    },
    computed: {
        ...mapGetters('room', [
            'isInventoryOpen',
            'selectedTarget'
        ]),
        targetPanel() {
            return this.selectedTarget instanceof Player ? CrewmatePanel : EquipmentPanel;
        },
        ...mapState('daedalus', [
            'isMinimapAvailable'
        ])
    }
});
</script>

<style lang="scss" scoped>

.ship-panel {
    position: relative;
    flex-direction: row;
    width: 424px;
    height: 460px;
    background: #09092d url("~@/assets/images/shipview/background.png") center repeat;

    .ship-panel-content {
        width: 100%;
        flex-direction: row;
    }

    .ship-view {
        width: 100%;
        height: 100%;

        @include corner-bezel(6.5px, 6.5px, 0);
    }

    .room {
        position: absolute;
        z-index: 5;
        margin: 8px 16px;
        font-family: $font-pixel-square;
        font-size: 10px;
        font-weight: 700;
    }
}

</style>
