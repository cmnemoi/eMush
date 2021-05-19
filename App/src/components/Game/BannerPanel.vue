<template>
    <div class="game-banner">
        <div class="character-banner">
            <a class="in-game-level" href="#" />
            <div class="in-game-level-progress">
                <div />
            </div>
            <div class="character-info">
                <span class="character-name">{{ player.characterValue }}</span>
                <!--        <div class="titles">-->
                <!--          <div class="title"><img class="title-img" src="@/assets/images/title_01.png"><span class="title-name">Commandant</span></div>-->
                <!--        </div>-->
            </div>
        </div>

        <div class="daedalus-banner">
            <Alerts :daedalus="daedalus"></Alerts>

            <div class="daedalus-info">
                <ul>
                    <li>{{ daedalus.oxygen }}<img src="@/assets/images/o2.png"></li>
                    <ToolTip><template v-slot:tooltipname>{{ daedalus.fuel }}<img src="@/assets/images/fuel.png"></template><template v-slot:tooltipcontenth1>Fuel en réserve [ {{ daedalus.fuel }} / 32 ]</template><template v-slot:tooltipcontentp>Le <strong>fuel</strong> est indispensable pour ne pas dériver dans l'espace jusqu'à la fin des temps. Cette ressource peut être trouvée lors de vos <strong>explorations</strong> sur les planètes. Cet indicateur n'affiche pas le fuel dans la <strong>Chambre de Combustion</strong>.</template></ToolTip>
                    <!--<li>{{ daedalus.fuel }}<img src="@/assets/images/fuel.png"></li>-->
                    <li>{{ daedalus.hull }}<img src="@/assets/images/shield.png"></li>
                    <li>{{ player.triumph }}<img src="@/assets/images/triumph.png"></li>
                </ul>
            </div>
            <div class="daedalus-crew">
                <ol>
                    <li v-for="(key) in daedalus.humanPlayerAlive" :key="key">
                        <img class="alive" src="@/assets/images/p_alive.png" alt="crewmate">
                    </li>
                    <li v-for="(key) in daedalus.mushPlayerAlive" :key="key">
                        <img class="mush" src="@/assets/images/p_mush.png" alt="mush">
                    </li>
                    <li v-for="(key) in daedalus.cryogenizedPlayers" :key="key">
                        <img class="cryo" src="@/assets/images/p_cryo.png" alt="in cryosleep">
                    </li>
                    <li v-for="(key) in daedalus.mushPlayerDead" :key="key">
                        <img class="deadmush" src="@/assets/images/p_deadmush.png" alt="dead mush">
                    </li>
                    <li v-for="(key) in daedalus.humanPlayerDead" :key="key">
                        <img class="dead" src="@/assets/images/p_dead.png" alt="dead crewmate">
                    </li>
                </ol>
            </div>
            <div class="cycle-time">
                <img class="casio-img" src="@/assets/images/casio.png">
                <countdown-timer :end-date="daedalus.nextCycle">
                    <template #default="slotProps">
                        <div class="flex-row">
                            <span v-show="slotProps.hour > 0" class="cycle-time-left">{{ slotProps.hour }}h</span>
                            <span class="cycle-time-left">{{ slotProps.min }}m</span>
                            <span class="cycle-time-left">{{ slotProps.sec }}s</span>
                        </div>
                    </template>
                </countdown-timer>
            </div>
        </div>
    </div>
</template>

<script>
import { Daedalus } from "@/entities/Daedalus";
import { Player } from "@/entities/Player";
import CountdownTimer from "@/components/Utils/CountdownTimer";
import Alerts from "@/components/Game/Ship/Alerts";
import ToolTip from "@/components/Utils/ToolTip"

export default {
    name: "BannerPanel",
    components: {Alerts, CountdownTimer, ToolTip },
    props: {
        player: Player,
        daedalus: Daedalus
    },
    data: function () {
        return {
        };
    },
};
</script>

<style  lang="scss" scoped>

.titles,
.title,
.game-banner,
.daedalus-banner,
.character-banner,
.daedalus-players,
.cycle-time {
    flex-direction: row;
}

p,
a,
li,
span {
    font-size: 0.85em;
    letter-spacing: 0.03em;
    font-variant: small-caps;
}

.game-banner {
    margin-bottom: 8px;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
}

.character-banner {
    display: flex;
    flex-direction: row;

    .in-game-level {
        @include button-style(1.4em);
        @include corner-bezel(12px, 0);

        width: 52px;
        height: 35px;
        border: 1px solid rgba(4, 21, 156, 1);
        border-radius: 4px;
        margin: auto;
        padding-right: 4px;
        font-family: Days-One;
    }

    .in-game-level-progress {
        position: relative;
        width: 11px;
        height: 33px;
        z-index: 2;
        margin: 1px;
        margin-left: -10px;
        border-radius: 4px;
        border: 1px solid #4077b5;
        background: #4077b5;
        box-shadow: 0 0 5px 1px #15273c inset, 0 0 0 1px #234164;

        & > div {
            position: relative;
            width: 100%;
            height: 100%;
            border-radius: 3px;
            background: rgb(128, 72, 112);
            background: linear-gradient(90deg, rgba(255, 137, 204, 1) 0%, rgba(255, 137, 204, 1) 25%, rgba(213, 38, 89, 1) 94%, rgba(128, 18, 47, 1) 100%);
            box-shadow: 0 0 2px 1px rgba(71, 10, 26, 0.7) inset;
        }

        &::after {
            content: "";
            position: absolute;
            bottom: 2px;
            width: 9px;
            height: 11px;
            background: transparent url('~@/assets/images/levelup_arrow_ingame.png') no-repeat center;
        }
    }

    .character-info {
        display: flex;
        justify-content: center;
        margin-left: 6px;

        .character-name {
            margin-bottom: 1px;
            font-size: 0.91em;
            font-weight: 700;
            text-transform: uppercase;
        }

        .title {
            display: flex;
            flex-direction: row;
            align-items: center;

            .title-name {
                margin-left: 4px;
                font-size: 0.68em;
                font-weight: bold;
                font-variant: small-caps;
                white-space: nowrap;
                color: rgba(255, 255, 255, 0.75);
            }
        }
    }
}

.daedalus-banner {
    align-items: center;

    & > div {
        margin: 0 12px;
        align-items: center;
    }

    .daedalus-info li {
        display: flex;
        align-items: center;

        &:not(:last-child) {
            padding-right: 1em;
        }

        img {
            padding: 2px 0 0 4px;

        }
    }

    .daedalus-crew img.cryo {
        opacity: 0.5;
    }

    .cycle-time img {
        margin-right: 0.4em;
    }
}

@keyframes alarms-border-color {
    0% { border: 1px solid #ff4e64; }
    50% { border: 1px solid rgba(58, 106, 171, 1); }
    100% { border: 1px solid #ff4e64; }
}

</style>
