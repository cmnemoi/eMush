<template>
    <div class="space-battle">
        <div class="sub-space-battle left">
            <div class="turrets left">
                <div class="turret" v-for="(turret, key) in player?.spaceBattle?.turrets" :key="key">
                    <p>{{ turret.charges }} charges</p>
                </div>
            </div>
            <div class="patrol-ships right">
                <div class="patrol-ship" v-for="(patrolShip, key) in player?.spaceBattle?.patrolShips" :key="key">
                    <p>{{ patrolShip.pilot }}</p>
                </div>
            </div>
        </div>
        <div :class="player?.isInAPatrolShip() ? 'sub-space-battle left' : 'sub-space-battle right'">
            <div :class="player?.isInAPatrolShip() ? 'hunters left' : 'hunters right'">
                <div class="hunter" v-for="(hunter, key) in player?.spaceBattle?.hunters" :key="key">
                    <p>{{ hunter.name }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { Player } from '@/entities/Player';
import { SpaceBattle } from '@/entities/SpaceBattle';
import { defineComponent } from 'vue';


export default defineComponent({
    name: 'SpaceBattleView',
    props: {
        player: Player,
    },
    methods: {
        getSpaceBattleContainerClass() {
            return {
                'space-battle': true,
                'left': this.player?.spaceBattle?.isPlayerAttacker,
                'right': !this.player?.spaceBattle?.isPlayerAttacker
            };
        }
    }
});

</script>

<style lang="scss" scoped>

.space-battle {
    position: absolute;
    width: 100%;
    height: 100%;
    align-self: flex-start;

    .sub-space-battle {
        position: absolute;
        width: 20%;
    }

    .patrol-ships {
        position: absolute;
        background-color: green;
    }

    .turrets {
        position: absolute;
        background-color: blue;
    }

    .hunters {
        position: absolute;
        background-color: red;
    }

    .left {
        align-self: flex-start;
    }

    .right {
        align-self: flex-end;
    }

}




</style>