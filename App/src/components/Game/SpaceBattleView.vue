<template>
    <div class="space-battle-container">
        <div class="space-battle left">
            <div class="fighters left">
                <div :class="isPlayerInRoom(turret.name) ? 'fighter green' : 'fighter'"
                     id="turret-container"
                     v-for="(turret, key) in player?.spaceBattle?.turrets"
                     :key="key">
                    <img v-if="turret.occupiers?.length > 0"
                         class="player-body"
                         :src="getPlayerCharacterBodyByName(getRandomTurretOccupier(turret))"
                         :alt="getRandomTurretOccupier(turret)">
                    <span class="turret-charges">
                        <img class="turret-img" :src="require('@/assets/images/turret.png')" alt="turret">
                        <span>
                            {{ turret.charges }}
                            <img class="charges-img" :src="require('@/assets/images/status/charge.png')" alt="charges">
                        </span>
                    </span>
                </div>
            </div>
            <div class="fighters right">
                <div :class="isPlayerInRoom(patrolShip.name) ? 'fighter green' : 'fighter'"
                     id="patrolship-container"
                     v-for="(patrolShip, key) in player?.spaceBattle?.patrolShips"
                     :key="key">
                    <img
                        class="player-body"
                        :src="getPlayerCharacterBodyByName(patrolShip.pilot)"
                        :alt="patrolShip.pilot">
                    <div class="patrol-ship-data">
                        <img class="patrol-ship-img" :src="require('@/assets/images/patrol_ship.png')" alt="patrol ship">
                        <span class="patrol-ship-armor">
                            {{ patrolShip.armor }}
                            <img class="armor-img" :src="require('@/assets/images/shield.png')" alt="armor">
                        </span>
                        <span class="patrol-ship-charges">
                            {{ patrolShip.charges }}
                            <img class="charges-img" :src="require('@/assets/images/status/charge.png')" alt="charge">
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="space-battle right">
            <div class="fighters right">
                <div class="fighter"
                     id="hunter-container"
                     v-for="(hunter, key) in player?.spaceBattle?.hunters"
                     :key="key">
                    <img :src="getHunterImage(hunter)" alt="hunter">
                    <span class="hunter-health">
                        {{ hunter.health }}
                        <img class="armor-img" :src="require('@/assets/images/shield.png')" alt="armor">
                    </span>
                </div>
            </div>
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
        getRandomTurretOccupier(turret: SpaceBattleTurret) : string | undefined {
            if (turret.occupiers.length === 0) return;

            // if turret occupiers contains the player watching the battle, always display them
            const playerName = this.player?.character?.key?.toString();
            if (playerName && turret.occupiers.includes(playerName)) {
                return playerName;
            }

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
    }
});

</script>

<style lang="scss" scoped>
.space-battle-container {
    position: absolute;
    width: 100%;
    height: 100%;
    align-self: flex-start;
    margin-top: 30px;
    font-size: 10px;
    font-weight: 700;

    .space-battle {
        position: absolute;
        width: 35%;

        .fighters {
            position: absolute;

            .fighter {
                width: 65px;
                height: 45px;
                margin: 1px;
                background-color: $slightlyDeepBlue;
                border: solid;
                border-color: $greyBlue;
                border-width: 1px;
                
                @include corner-bezel(0, 5px, 0, 0);

                .player-body {
                    position: absolute;
                    width: 15px;
                    height: 40px;
                    align-self: flex-start;
                }
            }

            #turret-container {

                .turret-charges {
                    position: absolute;
                    align-self: flex-end;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    width: 45px;

                    .turret-img {
                        width: 35px;
                        height: 25px;
                    }
                }
            }

            #hunter-container {
                width: 40px;
                height: 40px;
                display: flex;
                flex-direction: column;
                align-items: center;

                @include corner-bezel(3px, 0);
            }
        }
    }

    .left {
        align-self: flex-start;
        margin-left: 8px;
    }

    .right {
        align-self: flex-end;
    }
    
    .green {
        background-color: $deepGreen !important;
        border-color: $green !important;
    }

    .player-body {
        position: absolute;
        width: 15px;
        height: 40px;
        align-self: flex-start;
    }
}




</style>