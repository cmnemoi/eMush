<template>
    <div class="daedalus-fighters-container">
        <div :class="['fighter-container', { 'green': isPlayerInRoom(turret.name) }]" v-for="(turret, key) in player?.spaceBattle?.turrets" :key="key">
            <div class="sub-child gray">
                <img v-if="!turretIsEmpty(turret)"
                    class="player-body"
                    :src="getPlayerCharacterBodyByName(getTurretOccupier(turret))"
                    :alt="getTurretOccupier(turret)">
            </div>
            <div class="sub-child lightcoral">
                <div class="sub-child-inner antiquewhite">
                    <img class="turret-img" :src="require('@/assets/images/spaceBattleTurret.png')" alt="turret">
                </div>
                <div class="sub-child-inner antiquewhite">
                    <p class="quantity">{{ turret.charges }}</p>
                    <img class="charges-img" :src="require('@/assets/images/status/charge.png')" alt="charges">
                </div>
            </div>
        </div>
        <div :class="['fighter-container', { 'green': isPlayerInRoom(patrolShip.name) }]" v-for="(patrolShip, key) in player?.spaceBattle?.patrolShips" :key="key">
            <div class="sub-child gray">
                <img
                class="player-body"
                :src="getPlayerCharacterBodyByName(patrolShip.pilot)"
                :alt="patrolShip.pilot">
            </div>
            <div class="sub-child lightcoral">
                <div class="sub-child-inner antiquewhite">
                    <img class="patrol-ship-img" :src="require('@/assets/images/patrol_ship.png')" alt="patrol ship">
                </div>
                <div class="sub-child-inner antiquewhite">
                    <p class="quantity">{{ patrolShip.armor }}</p>
                    <img class="armor-img" :src="require('@/assets/images/shield.png')" alt="armor">
                    <p class="quantity">{{ patrolShip.charges }}</p>
                    <img class="charges-img" :src="require('@/assets/images/status/charge.png')" alt="charge">
                </div>
            </div>
        </div>
  </div>
  <div class="hunters-container">
    <div class="hunter-container" v-for="(hunter, key) in player?.spaceBattle?.hunters" :key="key">
        <img :src="getHunterImage(hunter)" alt="hunter">
        <p class="quantity">{{ hunter.health }}</p>
        <img class="armor-img" :src="require('@/assets/images/shield.png')" alt="armor">
    </div>
  </div>
</template>

<script lang="ts">
import { characterEnum } from '@/enums/character';
import { hunterEnum } from '@/enums/hunter.enum';
import { Player } from '@/entities/Player';
import { Hunter } from '@/entities/Hunter';
import { SpaceBattleTurret } from '@/entities/SpaceBattleTurret';
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'SpaceBattleView',
    props: {
        player: Player,
    },
    methods: {
        getPlayerCharacterBodyByName(playerName: string | undefined) : string | undefined {
            if (playerName === undefined) return;
            return characterEnum[playerName].body;
        },
        getPlayerCharacterBody(player: Player | undefined) : string | undefined {
            if (player === undefined) return;
            return characterEnum[player.character.key].body;
        },
        getTurretOccupier(turret: SpaceBattleTurret) : string | undefined {
            if (turret.occupiers.length === 0) return;

            // if turret occupiers contains the player watching the battle, always display them
            const playerName = this.player?.character?.key?.toString();
            if (playerName && turret.occupiers.includes(playerName)) {
                return playerName;
            }

            // else, pick a random occupier to display
            const randomIndex = Math.floor(Math.random() * turret.occupiers.length);
            return turret.occupiers[randomIndex];
        },
        getHunterImage(hunter: Hunter) : string {
            return hunterEnum[hunter.name].image;
        },
        isPlayerInRoom(roomKey: string | undefined) : boolean {
            if (roomKey === undefined) return false;
            return this.player?.room?.key === roomKey;
        },
        turretIsEmpty(turret: SpaceBattleTurret) : boolean {
            if (turret.occupiers === null) return true;
            return turret?.occupiers?.length === 0;
        },
    }
});

</script>

<style lang="scss" scoped>

    .daedalus-fighters-container {
        position: absolute;
        top: 25px;
        left: 15px;
        width: 129px;
        height: 303px;
        display: flex;
        flex-direction: column;
        flex-wrap: wrap;
        padding: 1px;
    }
    .fighter-container {
        width: 60px;
        height: 48px;
        background-color: $slightlyDeepBlue;
        margin: 1px;
        border: solid;
        border-color: $greyBlue;
        padding: 2px;
        border-width: 1px;
        border-top-right-radius: 10px;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
    }

    .fighter-container.green {
        background-color: $deepGreen !important;
        border-color: $green !important;
    }

    .sub-child {
        flex: 1;
    }

    .sub-child.gray {
        width: 20px;
        // background-color: gray;
    }

    .sub-child.lightcoral {
        width: calc(100% - 20px);
        height: calc(100% - 0px);
        // background-color: lightcoral;
        flex: 2;
    }

    .sub-child-inner {
        width: calc(100% - 4px);
        height: calc(50% - 2px);
        margin: 2px;
        flex-direction: row;
        justify-content: center;
        align-items: center;
    }

    // .sub-child-inner.antiquewhite {
    //     background-color: antiquewhite;
    // }

    .player-body {
        width: fit-content;
        height: fit-content;
    }
    .armor-img {
        width: 10px;
        height: 10px;
    }
    .charges-img {
        width: 10px;
        height: 10px;
    }
    .turret-img {
        margin-top: 7px;
    }
    .patrol-ship-img {
        width: 50px;
        height: 50px;
        clip-path: circle(50%);
        transform: scaleX(-1);
    }
    .quantity {
        width: fit-content;
        height: fit-content;
        margin: 0;
        font-weight: 700;
        font-size: 10px;
        padding: 1px;
    }
    
    .patrol-ship-stats {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: row;
    }

    .hunters-container {
        position: absolute;
        top: 25px;
        left: 341px;
        width: 85px;
        height: 303px;
        display: flex;
        flex-direction: column;
        flex-wrap: wrap;
        align-content: end;
        padding: 1px;
    }

    .hunter-container {
        width: 40px;
        height: 40px;
        background-color: $slightlyDeepBlue;
        margin: 1px;
        border: solid;
        border-color: $greyBlue;
        padding: 2px;
        border-width: 1px;
        border-top-left-radius: 5px;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
    }

    
</style>