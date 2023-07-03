<template>
    <div class="space-battle-container">
        <div class="space-battle left">
            <div class="fighters left">
                <div :class="player?.room?.key === turret.name ? 'fighter green' : 'fighter'" id="turret-container" v-for="(turret, key) in player?.spaceBattle?.turrets" :key="turret.id">
                    <img v-if="player?.room?.key === turret.name" class="turret-player" :src="getPlayerCharacterBody(player)" alt="turret-player">
                    <span class="turret-charges">
                        <img class="turret-img" :src="require('@/assets/images/turret.png')" alt="turret">
                        <span>
                            {{ turret.charges }}
                            <img class="charges-img" :src="require('@/assets/images/status/charge.png')" alt="charge">
                        </span>
                    </span>
                </div>
            </div>
            <div class="fighters right">
                <div class="fighter" id="patrolship-container" v-for="(patrolShip, key) in player?.spaceBattle?.patrolShips" :key="patrolShip.id">
                    <p>{{ patrolShip.pilot }}</p>
                </div>
            </div>
        </div>
        <div :class="player?.isInAPatrolShip() ? 'space-battle left' : 'space-battle right'">
            <div :class="player?.isInAPatrolShip() ? 'fighters left' : 'fighters right'">
                <div class="fighter" id="hunter-container" v-for="(hunter, key) in player?.spaceBattle?.hunters" :key="hunter.id">
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
import { defineComponent } from 'vue';
import { Hunter } from '@/entities/Hunter';


export default defineComponent({
    name: 'SpaceBattleView',
    props: {
        player: Player,
    },
    methods: {
        getPlayerCharacterBody(player: Player | undefined) {
            if (player === undefined) return;
            return characterEnum[player.character.key].body;
        },
        getHunterImage(hunter: Hunter) {
            return hunterEnum[hunter.name].image;
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

    .space-battle {
        position: absolute;
        width: 20%;

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
            }

            #turret-container {

                .turret-player {
                    position: absolute;
                    width: 15px;
                    height: 40px;
                    align-self: flex-start;
                }
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
}




</style>