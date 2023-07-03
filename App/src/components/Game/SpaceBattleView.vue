<template>
    <div class="space-battle-container">
        <div class="space-battle left">
            <div class="fighters left">
                <div :class="player?.room?.key === turret.name ? 'fighter green' : 'fighter'" id="turret" v-for="(turret, key) in player?.spaceBattle?.turrets" :key="turret.id">
                    <p>{{ turret.charges }} charges</p>
                </div>
            </div>
            <div class="fighters right">
                <div class="fighter" id="patrolship" v-for="(patrolShip, key) in player?.spaceBattle?.patrolShips" :key="patrolShip.id">
                    <p>{{ patrolShip.pilot }}</p>
                </div>
            </div>
        </div>
        <div :class="player?.isInAPatrolShip() ? 'space-battle left' : 'space-battle right'">
            <div :class="player?.isInAPatrolShip() ? 'fighters left' : 'fighters right'">
                <div class="fighter" id="hunter" v-for="(hunter, key) in player?.spaceBattle?.hunters" :key="hunter.id">
                    <p>{{ hunter.name }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Player } from '@/entities/Player';
import { defineComponent } from 'vue';


export default defineComponent({
    name: 'SpaceBattleView',
    props: {
        player: Player,
    },
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
                background-color: #122270;
                border: solid;
                border-color: #396AAF;
                border-width: 1px;
            }

            #hunter {
                width: 40px;
                height: 40px;
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
        background-color: #48A209 !important;
        border-color: #C1EA57 !important;
    }
}




</style>