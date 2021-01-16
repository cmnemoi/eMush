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
                        <span v-for="(status, key) in equipment.statuses" :key="key">
                          <img :src="statusIcon(status)">
                          <span v-if="status.charge > 0">x{{ status.charge }}</span>
                      </span>
                    </p>
                </div>
                <h1>Players</h1>
                <div v-for="(player,key) in room.players" :key="key">
                    <p @click="clickOnTarget(player, $event)">
                        {{ player.characterValue }}
                    </p>
                </div>
            </div>
        </div> <!-- PLACEHOLDER -->
        <div v-if="! loading" class="map-container">
            <div class="map">
                <img src="@/assets/images/shipmap.svg">
                <ul class="crew-position">
                    <li class="self" />
                    <li />
                    <li />
                    <li />
                    <li />
                    <li />
                    <li />
                    <li />
                    <li />
                    <li />
                    <li />
                    <li />
                    <li />
                    <li />
                    <li />
                    <li />
                </ul>
            </div>
        </div>
        <RoomInventoryPanel v-if="! loading" :items="room.items" />
        <p v-if="loading" class="loading">
            Loading...
        </p>
    </div>
</template>

<script>
import RoomInventoryPanel from "@/components/Game/Ship/RoomInventoryPanel";
import ActionButton from "@/components/Utils/ActionButton";
import { Room } from "@/entities/Room";
import ActionService from "@/services/action.service";
import { mapActions, mapGetters } from "vuex";
import {statusItemEnum} from "@/enums/status.item.enum";

export default {
    name: "ShipPanel",
    components: {
        ActionButton,
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
      statusIcon: function(status) {
        const statusImages = statusItemEnum[status.key];
        return typeof statusImages !== 'undefined' ? statusImages.icon : null;
      },
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

    & .map-container {
        align-self: flex-end;
        z-index: 5;
        bottom: 200px;
        width: 240px;
        height: 200px;
        background: #070724;

        @include corner-bezel(0, 18px, 0);

        transform: scale(0.5);
        transform-origin: bottom left;
        transition: transform 0.5s;

        &:hover {
            transform: scale(1);
            transition: transform 0.6s 0.2s;
        }

        & .map {
            width: 184px;
            height: 96px;
            margin: auto;
            transform: rotate(30deg);
        }

        & .crew-position {
            position: absolute;

            li {
                position: absolute;
                width: 4px;
                height: 4px;
                background: #f88;

                @for $i from 1 through 16 { // randomize crew position, for testing only
                    &:nth-child(#{$i}) {
                        left: random(156) + 10px;
                        top: random(52) + 18px;
                    }
                }

                &.self {
                    z-index: 2;
                    width: 6px;
                    height: 6px;
                    background: #889c28;
                    animation: self-position-color 1.1s infinite;
                }
            }
        }
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

@keyframes self-position-color {
    0% { background: #ff0; }
    39% { background: #ff0; }
    40% { background: #889c28; }
    100% { background: #889c28; }
}

</style>
