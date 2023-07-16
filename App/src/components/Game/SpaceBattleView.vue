<template>
    <div class="daedalus-fighters-container">
        <div :class="['fighter-container', { 'green': isPlayerInRoom(turret.key) }]" v-for="(turret, key) in player?.spaceBattle?.turrets" :key="key">
            <div class="sub-fighter-container image">
                <img v-if="!turretIsEmpty(turret)"
                     class="player-body"
                     :src="getPlayerCharacterBodyByName(getTurretOccupier(turret))"
                     :alt="getTurretOccupier(turret)">
            </div>
            <div class="sub-fighter-container stats">
                <div class="sub-fighter-container-inner">
                    <img class="turret-img" :src="require('@/assets/images/spaceBattleTurret.png')" alt="turret">
                </div>
                <div class="sub-fighter-container-inner">
                    <p class="quantity">{{ turret.charges }}</p>
                    <img class="charges-img" :src="require('@/assets/images/status/charge.png')" alt="charges">
                </div>
            </div>
        </div>
        <div :class="['fighter-container', { 'green': isPlayerInRoom(patrolShip.key) }]" v-for="(patrolShip, key) in player?.spaceBattle?.patrolShips" :key="key">
            <div class="sub-fighter-container image">
                <img
                    class="player-body"
                    :src="getPlayerCharacterBodyByName(patrolShip.pilot)"
                    :alt="patrolShip.pilot">
            </div>
            <div class="sub-fighter-container stats ship">
                <div class="sub-fighter-container-inner ship">
                    <img v-if="!patrolShip.isPasiphae()"
                         class="patrol-ship-img"
                         :src="require('@/assets/images/patrol_ship.png')"
                         alt="patrol ship">
                    <img v-else
                         class="pasiphae-img"
                         :src="require('@/assets/images/pasiphae.png')"
                         alt="pasiphae">
                </div>
                <div class="sub-fighter-container-inner stats">
                    <p class="quantity">{{ patrolShip.armor }}</p>
                    <img class="armor-img" :src="require('@/assets/images/shield.png')" alt="armor">
                    <p class="quantity" v-if="!patrolShip.isPasiphae()">{{ patrolShip.charges }}</p>
                    <img class="charges-img"
                         v-if="!patrolShip.isPasiphae()"
                         :src="require('@/assets/images/status/charge.png')"
                         alt="charges">
                </div>
            </div>
        </div>
    </div>
    <div class="hunters-container">
        <Tippy tag="div"
               class="hunter-container"
               v-for="(hunter, key) in player?.spaceBattle?.hunters"
               :key="key">
            <div :class="'sub-fighter-container-inner ship' + ' ' + hunter.key">
                <img class="hunter-img" :src="getHunterImage(hunter)" :alt="hunter.key">
            </div>
            <div class="sub-fighter-container-inner stats">
                <p class="quantity">{{ hunter.health }}</p>
                <img class="armor-img" :src="require('@/assets/images/shield.png')" alt="armor">
            </div>
            <template #content>
                <h1 v-html="formatContent(hunter.name)" /><br />
                <p v-html="formatContent(hunter.description)" />
            </template>
        </Tippy>
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
        getPlayerCharacterBodyByName(playerKey: string | undefined) : string | undefined {
            if (playerKey === undefined) return;
            return characterEnum[playerKey].body;
        },
        getPlayerCharacterBody(player: Player | undefined) : string | undefined {
            if (player === undefined) return;
            return characterEnum[player.character.key].body;
        },
        getTurretOccupier(turret: SpaceBattleTurret) : string | undefined {
            if (turret.occupiers.length === 0) return;

            // if turret occupiers contains the player watching the battle, always display them
            const playerKey = this.player?.character?.key?.toString();
            if (playerKey && turret.occupiers.includes(playerKey)) {
                return playerKey;
            }

            // else, pick a random occupier to display
            const randomIndex = Math.floor(Math.random() * turret.occupiers.length);
            return turret.occupiers[randomIndex];
        },
        getHunterImage(hunter: Hunter) : string {
            return hunterEnum[hunter.key].image;
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
        left: 13px;
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
        padding: 2px 2px 1px 1px;
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
    }

    .fighter-container.green {
        background-color: $deepGreen !important;
        border-color: $green !important;
    }

    .sub-fighter-container {
        flex: 1;
    }

    .sub-fighter-container.image {
        width: 20px;
    }

    .sub-fighter-container.stats {
        width: calc(100% - 20px);
        height: calc(100% - 0px);
        flex: 2;
    }

    .sub-fighter-container-inner {
        width: calc(100% - 2px);
        height: 40%;
        margin: auto;
        flex-direction: row;
        justify-content: center;
        align-items: center;
    }

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

    .pasiphae-img {
        width: 50px;
        height: 50px;
        clip-path: circle(75%);
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
        right: 1px;
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
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .hunter-img {
        width: 30px;
        height: 30px;
        object-fit: none;
        object-position: center;
    }
    
    .sub-fighter-container-inner.ship {
        margin: 0px;
        height: 60%;
        overflow: hidden;
    }
    .sub-fighter-container-inner.stats {
        margin-left: 1px;
    }

    div.tippy-tooltip {
        margin-right: 5px;
        margin-left: 5px;
    }
    
</style>