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
                    <img class="turret-img" :src="getImgUrl('spaceBattleTurret.png')" alt="turret">
                </div>
                <div class="stats">
                    <p class="quantity">{{ turret.charges }}</p>
                    <img class="charges-img" :src="getImgUrl('status/charge.png')" alt="charges">
                    <img
                        v-if="turret.isBroken"
                        class="broken-img"
                        :src="getImgUrl('alerts/broken.png')"
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
            <div class="operator" v-if="patrolShip.drone">
                <img
                    class="drone-img"
                    :src="getImgUrl('drone.png')"
                    alt="drone">
            </div>
            <div>
                <div class="ship-img-container">
                    <img
                        v-if="!patrolShip.isPasiphae()"
                        class="patrol-ship-img"
                        :src="getImgUrl('patrol_ship.png')"
                        alt="patrol ship">
                    <img
                        v-else
                        class="pasiphae-img"
                        :src="getImgUrl('pasiphae.png')"
                        alt="pasiphae">
                </div>
                <div class="stats">
                    <p v-if="patrolShip.armor" class="quantity">{{ patrolShip.armor }}</p>
                    <img
                        v-if="patrolShip.armor"
                        class="armor-img"
                        :src="getImgUrl('shield.png')"
                        alt="armor">
                    <p class="quantity" v-if="!patrolShip.isPasiphae()">{{ patrolShip.charges }}</p>
                    <img
                        class="charges-img"
                        v-if="!patrolShip.isPasiphae()"
                        :src="getImgUrl('status/charge.png')"
                        alt="charges">
                    <img
                        v-if="patrolShip.isBroken"
                        class="broken-img"
                        :src="getImgUrl('alerts/broken.png')"
                        alt="broken">
                </div>
            </div>
        </div>
    </div>
    <div class="hunters-container">
        <Tippy
            v-for="group in hunterGroups"
            :key="group.hunters[0].id"
            tag="div"
            class="hunter-group"
            @mousedown.stop="toggleHunterSelection(group.hunters[0])"
            @animationend="resetHunterState()"
        >
            <div
                class="hunter"
                :class="[{ 'highlight': isSelected(group.hunters[0]) }, { 'hit': isHit(group.hunters[0]) }, { 'kill': isKilled(group.hunters[0]) }]"
            >
                <div class="ship-img-container">
                    <img
                        v-if="group.hunters[0].transportImage"
                        :class="group.hunters[0].transportImage + '-img'"
                        :src="getHunterImage(group.hunters[0].transportImage)"
                        :alt="group.hunters[0].name">
                    <img
                        v-else
                        :class="group.hunters[0].key + '-img'"
                        :src="getHunterImage(group.hunters[0].key)"
                        :alt="group.hunters[0].name">
                </div>
                <div class="stats">
                    <p class="quantity">{{ group.hunters[0].health }}</p>
                    <img class="armor-img" :src="getImgUrl('shield.png')" alt="armor">
                    <span v-if="group.length() > 1" class="count">x{{ group.length() }}</span>
                </div>
            </div>
            <div v-for="i in Math.min(group.length() - 1, 2)" :key="i" :class="['hunter', `offset-${i}`]"/>
            <template #content>
                <h1 v-html="formatContent(group.hunters[0].name)" />
                <p v-html="formatContent(group.hunters[0].description)" />
            </template>
        </Tippy>
    </div>

</template>

<script lang="ts">
import { characterEnum } from '@/enums/character';
import { HunterImageEnum } from '@/enums/hunter.enum';
import { Player } from '@/entities/Player';
import { Hunter, HunterGroup } from '@/entities/Hunter';
import { SpaceBattleTurret } from '@/entities/SpaceBattleTurret';
import { defineComponent } from 'vue';
import { mapActions, mapGetters, mapMutations } from "vuex";
import { Item } from "@/entities/Item";
import { Action } from "@/entities/Action";
import { getImgUrl } from '@/utils/getImgUrl';

export default defineComponent({
    name: 'SpaceBattleView',
    props: {
        player: Player
    },
    computed: {
        ...mapGetters({
            getSpaceShip: 'room/getSpaceShip',
            isHunterBeenHit: 'action/isHunterBeenHit',
            isHunterBeenKilled: 'action/isHunterBeenKilled',
            selectedTarget: 'room/selectedTarget',
            targetedHunterId: 'action/targetedHunterId'
        }),
        getSelectedTarget(): Item | Hunter | null {
            if (this.selectedTarget instanceof Hunter) { return this.selectedTarget;}
            return null;
        },
        hunterGroups(): Array<HunterGroup> {
            return HunterGroup.fromHunterArray(this.player?.spaceBattle?.hunters);
        }
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
        getImgUrl,
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
        getHunterImage(hunterKey: string) : string {
            return HunterImageEnum[hunterKey];
        },
        isHit(hunter: Hunter) : boolean {
            return this.isHunterBeenHit && hunter.id === this.targetedHunterId;
        },
        isKilled(hunter: Hunter) : boolean {
            return this.isHunterBeenKilled && hunter.id === this.targetedHunterId;
        },
        isPlayerInRoom(roomKey: string | undefined) : boolean {
            if (roomKey === undefined) return false;
            return this.player?.room?.key === roomKey;
        },
        isSelected: function(hunter: Hunter): boolean {
            return this.getSelectedTarget instanceof Hunter && this.getSelectedTarget === hunter;
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
        }
    }
});

</script>

<style lang="scss" scoped>
@use "sass:color";

$hit-color: #fd1f07;

.allies-container {
    position: absolute;
    flex-wrap: wrap;
    top: 20px;
    left: 13px;
    max-height: 320px;
}

.hunters-container {
    position: absolute;
    display: flex;
    flex-direction: column;
    flex-wrap: wrap-reverse;
    align-items: center;
    gap: 7px;
    max-height: 290px;
    top: 20px;
    right: 9px;
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

.count {
    position: absolute;
    right: 0;
    bottom: 0;
    font-size: 0.7em;
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

    .transport_1-img {
        top: -53px;
        left: -30px;
    }

    .transport_2-img {
        top: -32px;
        left: -3px;
    }

    .transport_3-img {
        top: -5px;
        left: -8px;
    }

    .transport_4-img {
        top: -5px;
        left: -7px;
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

.hunter-group {
    position: relative;
    width: 42px;
    height: 42px;
    display: flex;
    align-self: center;
}

.hunter {
    position: relative;
    width: 42px;
    height: 42px;
    border-top-left-radius: 5px;
    border-top-right-radius: 0;
    cursor: pointer;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 10;

    &:hover, &:focus {
        background-color: color.adjust($slightlyDeepBlue, $lightness: 10%);
        border-color: color.adjust($greyBlue, $lightness: 15%);
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

.offset-1, .offset-2, .offset-3 {
    min-width: 42px;
    min-height: 42px;
    position: absolute;
}
.offset-1 {
    bottom: -4px;
    right: -4px;
    z-index: 3;
}
.offset-2 {
    bottom: -6px;
    right: -6px;
    z-index: 2;
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
