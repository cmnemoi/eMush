<template>
    <div class="allies-container">
        <div :class="['turret', { 'green': isPlayerInRoom(turret.key) }]" v-for="(turret, key) in player?.spaceBattle?.turrets" :key="key">
            <div class="operator">
                <img v-if="!turretIsEmpty(turret)"
                     class="player-body"
                     :src="getPlayerCharacterBodyByName(getTurretOccupier(turret))"
                     :alt="getTurretOccupier(turret)">
            </div>
            <div>
                <div class="ship-img-container">
                    <img class="turret-img" :src="require('@/assets/images/spaceBattleTurret.png')" alt="turret">
                </div>
                <div class="stats">
                    <p class="quantity">{{ turret.charges }}</p>
                    <img class="charges-img" :src="require('@/assets/images/status/charge.png')" alt="charges">
                </div>
            </div>
        </div>
        <div :class="['fighter', { 'green': isPlayerInRoom(patrolShip.key) }]" v-for="(patrolShip, key) in player?.spaceBattle?.patrolShips" :key="key">
            <div class="operator">
                <img
                    class="player-body"
                    :src="getPlayerCharacterBodyByName(patrolShip.pilot)"
                    :alt="patrolShip.pilot">
            </div>
            <div>
                <div class="ship-img-container">
                    <img v-if="!patrolShip.isPasiphae()"
                         class="patrol-ship-img"
                         :src="require('@/assets/images/patrol_ship.png')"
                         alt="patrol ship">
                    <img v-else
                         class="pasiphae-img"
                         :src="require('@/assets/images/pasiphae.png')"
                         alt="pasiphae">
                </div>
                <div class="stats">
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
               class="hunter"
               :class="isHunterSelected(hunter) ? 'highlight' : ''"
               @mousedown.stop="$emit('select', hunter)"
               v-for="(hunter, key) in player?.spaceBattle?.hunters"
               :key="key">
            <div class="ship-img-container">
                <img :class="hunter.key + '-img'" :src="getHunterImage(hunter)" :alt="hunter.key">
            </div>
            <div class="stats">
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
        selectedHunter: {
            type: Hunter,
            required: false,
            default: null
        }
    },
    emits: [
        'select'
    ],
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
        isHunterSelected: function(hunter: Hunter): boolean {
            return this.selectedHunter instanceof Hunter && this.selectedHunter.id === hunter.id;
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

.allies-container, .hunters-container {
    position: absolute;
    top: 25px;
    // width: 129px;
    max-height: 320px;
    // display: flex;
    // flex-direction: column;
    // padding: 1px;
}

.allies-container {
    left: 13px;
    flex-wrap: wrap;
}

.hunters-container {
    // position: absolute;
    right: 2px;
    // width: 85px;
    // max-height: 303px;
    // display: flex;
    // flex-direction: column;
    flex-wrap: wrap-reverse;
    // align-content: end;
    // padding: 1px;
}

.turret, .fighter, .hunter {
    width: 68px;
    height: 48px;
    margin: 1px;
    padding: 1px;
    background-color: $slightlyDeepBlue;
    border: 1px solid $greyBlue;
    border-top-right-radius: 10px;
    // display: flex;
    flex-direction: row;
    // flex-wrap: wrap;

    &.green {
        background-color: $deepGreen;
        border-color: $green;
    }
}

.sub-fighter-container {
    // flex: 1;
}

.operator {
    width: 20px;
    overflow: visible;
}

.stats {
    flex-direction: row;
    justify-content: center;
    align-items: center;
    z-index: 2;
    font-weight: 700;
    font-size: .75em;
    letter-spacing: 0.05em;
    text-shadow: 0 0 5px $deepBlue;
    // font-family: $font-pixel-square;

    p { margin: 0; }
}

.hunters-container .stats {
    // height: 6px;
}

/*     .sub-fighter-container-inner {
    width: calc(100% - 2px);
    height: 40%;
    margin: auto;
    flex-direction: row;
    justify-content: center;
    align-items: center;
} */

.ship-img-container {
    width: 38px;
    height: 30px;
    margin: 0 2px;
    overflow: hidden;

    img {
        position: relative;
        margin: auto;
    }

    .patrol-ship-img {
        width: calc(103px / 1.5);
        transform: scaleX(-1);
        top: -8px;
        left: -16px;
    }

    .pasiphae-img {
        width: calc(85px / 1.5);
        transform: scaleX(-1);
        top: -15px;
        left: -12px;
    }

    .hunter-img {
        top: -3px;
        left: -7px;
    }

    .spider-img {
        top: -25px;
        left: -14px;
    }

    .asteroid-img {
        top: -17px;
        left: -25px;
    }

    .dice-img {
        top: -11px;
        left: 0;
    }

    .trax-img {
        top: -12px;
        left: -10px;
    }
}

.hunters-container .ship-img-container {
    // width: 33px;
    height: 26px;
}

img {
    width: fit-content;
    height: fit-content;
}

.turret-img {
    // margin-top: 7px;
}

/* .quantity {
    margin: 0;
    // padding: 1px;
    font-weight: 700;
    font-size: .75em;
    text-shadow: 0 0 5px $deepBlue;
} */

.patrol-ship-stats {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: row;
}

.hunter {
    width: 42px;
    height: 42px;
    // background-color: $slightlyDeepBlue;
    // margin: 1px;
    // border: solid;
    // border-color: $greyBlue;
    // padding: 2px;
    // border-width: 1px;
    border-top-left-radius: 5px;
    border-top-right-radius: 0;
    cursor: pointer;
    // display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;

    &:hover, &:focus {
        background-color: lighten($slightlyDeepBlue, 10%);
        border-color: lighten($greyBlue, 15%);
    }

    &:active, &.highlight {
        z-index: 5;
        box-shadow: 0px 0px 8px 3px red;
    }
}


/////

/* .hunter-img {
    width: 30px;
    height: 30px;
    object-fit: none;
    object-position: center;
} */

/* .sub-fighter-container-inner.ship {
    margin: 0px;
    height: 60%;
    overflow: hidden;
}
.sub-fighter-container-inner.stats {
    margin-left: 1px;
}
 */
/* div.tippy-tooltip {
    margin-right: 5px;
    margin-left: 5px;
} */

</style>