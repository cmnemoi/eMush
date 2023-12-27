<template>
    <div class="allies-container">
        <div :class="['turret', { 'green': isPlayerInRoom(turret.key) }]" v-for="(turret, key) in player?.spaceBattle?.turrets" :key="key">
            <div class="operator">
                <img
                    v-if="turret.isOccupied()"
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
                    <img v-if="turret.isBroken"
                         class="broken-img"
                         :src="require('@/assets/images/alerts/broken.png')"
                         alt="broken">
                </div>
            </div>
        </div>
        <div :class="['fighter', { 'green': isPlayerInRoom(patrolShip.key) }]" v-for="(patrolShip, key) in player?.spaceBattle?.patrolShips" :key="key">
            <div class="operator" v-if="patrolShip.pilot">
                <img
                    class="player-body"
                    :src="getPlayerCharacterBodyByName(patrolShip.pilot)"
                    :alt="patrolShip.pilot">
            </div>
            <div>
                <div class="ship-img-container">
                    <img
                        v-if="!patrolShip.isPasiphae()"
                        class="patrol-ship-img"
                        :src="require('@/assets/images/patrol_ship.png')"
                        alt="patrol ship">
                    <img
                        v-else
                        class="pasiphae-img"
                        :src="require('@/assets/images/pasiphae.png')"
                        alt="pasiphae">
                </div>
                <div class="stats">
                    <p v-if="patrolShip.armor" class="quantity">{{ patrolShip.armor }}</p>
                    <img v-if="patrolShip.armor"
                         class="armor-img"
                         :src="require('@/assets/images/shield.png')"
                         alt="armor">
                    <p class="quantity" v-if="!patrolShip.isPasiphae()">{{ patrolShip.charges }}</p>
                    <img
                        class="charges-img"
                        v-if="!patrolShip.isPasiphae()"
                        :src="require('@/assets/images/status/charge.png')"
                        alt="charges">
                    <img v-if="patrolShip.isBroken"
                         class="broken-img"
                         :src="require('@/assets/images/alerts/broken.png')"
                         alt="broken">
                </div>
            </div>
        </div>
    </div>
    <div class="hunters-container">
        <Tippy
            tag="div"
            class="hunter"
            :class="[{ 'highlight': isHunterSelected(hunter) }, { 'hit': isHit(hunter) }, { 'kill': isKilled(hunter) }]"
            @mousedown.stop="toggleHunterSelection(hunter)"
            @animationend="resetHunterState()"
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
import { mapActions, mapGetters, mapMutations } from "vuex";
import { Item } from "@/entities/Item";
import { Action } from "@/entities/Action";

export default defineComponent({
    name: 'SpaceBattleView',
    props: {
        player: Player,
    },
    computed: {
        ...mapGetters({
            getSpaceShip: 'room/getSpaceShip',
            isHunterBeenHit: 'action/isHunterBeenHit',
            isHunterBeenKilled: 'action/isHunterBeenKilled',
            selectedTarget: 'room/selectedTarget',
        }),
        getSelectedTarget(): Item | Hunter | null
        {
            if (this.selectedTarget instanceof Hunter) { return this.selectedTarget;}
            return null;
        },
    },
    methods: {
        ...mapActions({
            'executeAction': 'action/executeAction',
            'selectTarget': 'room/selectTarget'
        }),
        ...mapMutations({
            'setIsHunterBeenHit': 'action/setIsHunterBeenHit',
            'setIsHunterBeenKilled': 'action/setIsHunterBeenKilled'
        }),
        async executeTargetAction(target: Hunter | null, action: Action): Promise<void> {
            if (action.canExecute) {
                await this.executeAction({ target: target, action: action });
            }
        },
        getPlayerCharacterBodyByName(playerKey: string) : string {
            return characterEnum[playerKey].body;
        },
        getTurretOccupier(turret: SpaceBattleTurret) : string | undefined {
            if (turret.isEmpty()) return;

            // if turret occupiers contains the player watching the battle, always display them
            if (turret.isOccupiedByPlayer(this.player)) return this.player?.character.key;

            // else, pick a random occupier to display
            const randomIndex = Math.floor(Math.random() * turret.occupiers.length);
            return turret.occupiers[randomIndex];
        },
        getHunterImage(hunter: Hunter) : string {
            return hunterEnum[hunter.key].image;
        },
        isHit(hunter: Hunter) : boolean {
            return this.isHunterBeenHit && this.getSelectedTarget === hunter;
        },
        isKilled(hunter: Hunter) : boolean {
            return this.isHunterBeenKilled && this.getSelectedTarget === hunter;
        },
        isHunterSelected: function(hunter: Hunter): boolean {
            return this.getSelectedTarget instanceof Hunter && this.getSelectedTarget === hunter;
        },
        isPlayerInRoom(roomKey: string | undefined) : boolean {
            if (roomKey === undefined) return false;
            return this.player?.room?.key === roomKey;
        },
        resetHunterState() {
            // we need to reset the state to avoid the animation to be triggered again before next hit
            // we do it after 1s to let the animation finish
            setTimeout(() => {
                this.setIsHunterBeenHit(false);
                this.setIsHunterBeenKilled(false);
            }, 1000);
        },
        selectHunter(hunter: Hunter | null): void {
            this.selectTarget({ target: hunter });
        },
        toggleHunterSelection(hunter: Hunter | null): void {
            if (this.getSelectedTarget === hunter) {
                this.selectTarget({ target: this.getSpaceShip });
            } else {
                this.selectTarget({ target: hunter });
            }
        },
    }
});

</script>

<style lang="scss" scoped>

$hit-color: #fd1f07;
.allies-container, .hunters-container {
    position: absolute;
    top: 25px;
    max-height: 320px;
}

.allies-container {
    left: 13px;
    flex-wrap: wrap;
}

.hunters-container {
    right: 2px;
    flex-wrap: wrap-reverse;
}

.turret, .fighter, .hunter {
    width: 68px;
    height: 48px;
    margin: 1px;
    padding: 1px;
    background-color: $slightlyDeepBlue;
    border: 1px solid $greyBlue;
    border-top-right-radius: 8px;
    flex-direction: row;

    &.green {
        background-color: $deepGreen;
        border-color: $green;
    }
}

.operator {
    flex-direction: row;
    justify-content: center;
    width: 20px;
    overflow: visible;
    z-index: 2;
}

.stats {
    flex-direction: row;
    justify-content: center;
    align-items: center;
    z-index: 3;
    font-weight: 700;
    font-size: .75em;
    letter-spacing: 0.05em;
    text-shadow: 0 0 5px $deepBlue;

    p { margin: 0; }
}

.ship-img-container {
    width: 38px;
    height: 30px;
    margin: 0 3px;
    overflow: hidden;
    align-self: flex-end;

    img {
        position: relative;
        margin: auto;
    }

    // lists all ships and setup their display parameters
    .patrol-ship-img {
        width: calc(103px / 1.8);
        transform: scaleX(-1);
        top: -5px;
        left: -9px;
    }

    .pasiphae-img {
        width: calc(85px / 1.8);
        transform: scaleX(-1);
        top: -11px;
        left: -8px;
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
    height: 26px;
    align-self: center;
}

img {
    width: fit-content;
    height: fit-content;
}

.patrol-ship-stats {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: row;
}

.hunter {
    width: 42px;
    height: 42px;
    border-top-left-radius: 5px;
    border-top-right-radius: 0;
    cursor: pointer;
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

    &.hit {
        animation: hit-color 0.2s linear 8 alternate, hit-shake .5s ease-out 1 forwards;
        z-index: 3;
    }

    &.kill {
        opacity: 0;
        background-color: $hit-color;
        transition: opacity 1s ease-in 0.3s;
        animation: hit-shake .5s ease-out 1 forwards;
        z-index: 3;
    }
}

@keyframes hit-color {
    30% { background-color: #122270; }
    70% { background-color: $hit-color; }
    100% { background-color: $hit-color; }
}

@keyframes hit-shake {
    0% { transform: translate(0, 0); }
    15% { transform: translate(-15%, -15%); }
    30% { transform: translate(10%, 10%); }
    45% { transform: translate(-5%, -5%); }
    60% { transform: translate(5%, 5%); }
    80% { transform: translate(-5%, -5%); }
    100% { transform: translate(0, 0); }
}

</style>
