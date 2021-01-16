<template>
    <div class="ship-panel" @click="clickOnTarget(getPlayer, $event)">
        <p v-if="! loading" class="room">
            {{ room.name }}
        </p>
        <div v-if="! loading" class="ship-view">
            <div class="textual">
                <h1>Doors</h1>
                <div v-for="door in room.doors" :key="door.id" class="door">
                    <p>{{ door.direction }} :</p>
                    <ActionButton
                        v-for="(action, key) in door.actions"
                        :key="key"
                        class="door-action-button"
                        :action="action"
                        @click="executeDoorAction(door, action)"
                    />
                </div>

                <h1>Equipment</h1>
                <div v-for="(equipment,key) in room.equipments" :key="key">
                    <p @click="clickOnTarget(equipment, $event); $event.stopPropagation()">
                        {{ equipment.name }}
                    </p>
                </div>
                <h1>Players</h1>
                <div v-for="(player,key) in room.players" :key="key">
                    <p @click="clickOnTarget(player, $event)">
                        {{ player.characterValue }}
                    </p>
                </div>
            </div>
        </div>
        <MiniMap v-if="! loading" />
        <RoomInventoryPanel v-if="! loading" :items="room.items" />
        <p v-if="loading" class="loading">
            Loading...
        </p>
    </div>
</template>

<script>
import MiniMap from "@/components/Game/Ship/MiniMap";
import RoomInventoryPanel from "@/components/Game/Ship/RoomInventoryPanel";
import ActionButton from "@/components/Utils/ActionButton";
import { Room } from "@/entities/Room";
import ActionService from "@/services/action.service";
import { mapActions, mapGetters } from "vuex";

export default {
    name: "ShipPanel",
    components: {
        ActionButton,
        MiniMap,
        RoomInventoryPanel
    },
    props: {
        room: Room
    },
    computed: {
        ...mapGetters('player', [
            'getPlayer',
            'loading'
        ])
    },
    methods: {
        async executeDoorAction(door, action) {
            this.setLoading();
            await ActionService.executeDoorAction(door, action);
            await this.reloadPlayer();
        },
        clickOnTarget: function (target, event) {
            this.selectTarget({ target:target }); event.stopPropagation();
        },
        ...mapActions('player', [
            'reloadPlayer',
            'selectTarget',
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

/* PROVISIONAL SHIP INTERACTIONS */

.textual {
    overflow: auto;
    padding: 18px 12px;
    font-size: 0.83em;

    h1,
    h2,
    h3 {
        color: #cf1830;
        font-size: 1.5em;
        font-variant: small-caps;
        margin: 12px 0 4px 0;
    }

    p {
        margin: 12px 0 4px 0;
        font-weight: 700;
    }

    & > *:last-child {
        margin-bottom: 188px;
    }

    .door-action-button {
        max-width: 120px;
    }
}

</style>
