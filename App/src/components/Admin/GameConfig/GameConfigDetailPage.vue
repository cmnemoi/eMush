<template>
    <div v-if="gameConfig" class="center">
        <div class="flex-row">
        </div>
        <div class="flex-row">
            <div class="flex-grow-1">
                <label for="config_name">{{ $t('config.name') }}</label>
                <input
                    id="config_name"
                    ref="config_name"
                    v-model="gameConfig.name"
                    type="text"
                    readonly
                >
                <ul v-if="errors.name">
                    <li v-for="(value, key) in errors.name" :key="key">{{ value }}</li>
                </ul>
            </div>
            <div class="flex-grow-1">
                <label for="config_language">{{ $t('config.language') }}</label>
                <input
                    id="config_language"
                    ref="config_language"
                    v-model="gameConfig.language"
                    type="text"
                >
                <ul v-if="errors.language">
                    <li v-for="(value, key) in errors.language" :key="key">{{ value }}</li>
                </ul>
            </div>
            <div class="flex-grow-1">
                <label for="config_timeZone">{{ $t('config.timeZone') }}</label>
                <input
                    id="config_timeZone"
                    ref="config_timeZone"
                    v-model="gameConfig.timeZone"
                    type="text"
                >
                <ul v-if="errors.timeZone">
                    <li v-for="(value, key) in errors.timeZone" :key="key">{{ value }}</li>
                </ul>
            </div>
        </div>
        <div class="flex-row">
            <div class="flex-grow-1">
                <label for="config_nbMush">{{ $t('config.nbMush') }}</label>
                <input
                    id="config_nbMush"
                    ref="config_nbMush"
                    v-model="gameConfig.nbMush"
                    type="number"
                >
                <ul v-if="errors.nbMush">
                    <li v-for="(value, key) in errors.nbMush" :key="key">{{ value }}</li>
                </ul>
            </div>
            <div class="flex-grow-1">
                <label for="config_cyclePerGameDay">{{ $t('config.cyclePerGameDay') }}</label>
                <input
                    id="config_cyclePerGameDay"
                    ref="config_cyclePerGameDay"
                    v-model="gameConfig.cyclePerGameDay"
                    type="text"
                >
                <ul v-if="errors.cyclePerGameDay">
                    <li v-for="(value, key) in errors.cyclePerGameDay" :key="key">{{ value }}</li>
                </ul>
            </div>
            <div class="flex-grow-1">
                <label for="config_cycleLength">{{ $t('config.cycleLength') }}</label>
                <input
                    id="config_cycleLength"
                    ref="config_cycleLength"
                    v-model="gameConfig.cycleLength"
                    type="text"
                >
                <ul v-if="errors.cycleLength">
                    <li v-for="(value, key) in errors.cycleLength" :key="key">{{ value }}</li>
                </ul>
            </div>
        </div>
        <div class="flex-row">
            <div class="flex-">
                <label for="config_maxNumberPrivateChannel">{{ $t('config.maxNumberPrivateChannel') }}</label>
                <input
                    id="config_maxNumberPrivateChannel"
                    ref="config_maxNumberPrivateChannel"
                    v-model="gameConfig.maxNumberPrivateChannel"
                    type="number"
                >
                <ul v-if="errors.maxNumberPrivateChannel">
                    <li v-for="(value, key) in errors.maxNumberPrivateChannel" :key="key">{{ value }}</li>
                </ul>
            </div>
            <div class="flex-grow-1">
                <label for="config_maxItemInInventory">{{ $t('config.maxItemInInventory') }}</label>
                <input
                    id="config_maxItemInInventory"
                    ref="config_maxItemInInventory"
                    v-model="gameConfig.maxItemInInventory"
                    type="number"
                >
                <ul v-if="errors.maxItemInInventory">
                    <li v-for="(value, key) in errors.maxItemInInventory" :key="key">{{ value }}</li>
                </ul>
            </div>
            <div class="flex-grow-1">
                <label for="config_initSatiety">{{ $t('config.initSatiety') }}</label>
                <input
                    id="config_initSatiety"
                    ref="config_initSatiety"
                    v-model="gameConfig.initSatiety"
                    type="number"
                >
                <ul v-if="errors.initSatiety">
                    <li v-for="(value, key) in errors.initSatiety" :key="key">{{ value }}</li>
                </ul>
            </div>
        </div>
        <div class="flex-row">
            <div class="flex-grow-1">
                <label for="config_initHealthPoint">{{ $t('config.initHealthPoint') }}</label>
                <input
                    id="config_initHealthPoint"
                    ref="config_initHealthPoint"
                    v-model="gameConfig.initHealthPoint"
                    type="number"
                >
                <ul v-if="errors.initHealthPoint">
                    <li v-for="(value, key) in errors.initHealthPoint" :key="key">{{ value }}</li>
                </ul>
            </div>
            <div class="flex-grow-1">
                <label for="config_maxHealthPoint">{{ $t('config.maxHealthPoint') }}</label>
                <input
                    id="config_maxHealthPoint"
                    ref="config_maxHealthPoint"
                    v-model="gameConfig.maxHealthPoint"
                    type="number"
                >
                <ul v-if="errors.maxHealthPoint">
                    <li v-for="(value, key) in errors.maxHealthPoint" :key="key">{{ value }}</li>
                </ul>
            </div>
            <div class="flex-grow-1">
                <label for="config_initMoralPoint">{{ $t('config.initMoralPoint') }}</label>
                <input
                    id="config_initMoralPoint"
                    ref="config_initMoralPoint"
                    v-model="gameConfig.initMoralPoint"
                    type="number"
                >
                <ul v-if="errors.initMoralPoint">
                    <li v-for="(value, key) in errors.initMoralPoint" :key="key">{{ value }}</li>
                </ul>
            </div>
            <div class="flex-grow-1">
                <label for="config_maxMoralPoint">{{ $t('config.maxMoralPoint') }}</label>
                <input
                    id="config_maxMoralPoint"
                    ref="config_maxMoralPoint"
                    v-model="gameConfig.maxMoralPoint"
                    type="number"
                >
                <ul v-if="errors.maxMoralPoint">
                    <li v-for="(value, key) in errors.maxMoralPoint" :key="key">{{ value }}</li>
                </ul>
            </div>
        </div>
        <div class="flex-row">
            <div class="flex-grow-1">
                <label for="config_initActionPoint">{{ $t('config.initActionPoint') }}</label>
                <input
                    id="config_initActionPoint"
                    ref="config_initActionPoint"
                    v-model="gameConfig.initActionPoint"
                    type="number"
                >
                <ul v-if="errors.initActionPoint">
                    <li v-for="(value, key) in errors.initActionPoint" :key="key">{{ value }}</li>
                </ul>
            </div>
            <div class="flex-grow-1">
                <label for="config_maxActionPoint">{{ $t('config.maxActionPoint') }}</label>
                <input
                    id="config_maxActionPoint"
                    ref="config_maxActionPoint"
                    v-model="gameConfig.maxActionPoint"
                    type="number"
                >
                <ul v-if="errors.maxActionPoint">
                    <li v-for="(value, key) in errors.maxActionPoint" :key="key">{{ value }}</li>
                </ul>
            </div>
            <div class="flex-grow-1">
                <label for="config_initMovementPoint">{{ $t('config.initMovementPoint') }}</label>
                <input
                    id="config_initMovementPoint"
                    ref="config_initMovementPoint"
                    v-model="gameConfig.initMovementPoint"
                    type="number"
                >
                <ul v-if="errors.initMovementPoint">
                    <li v-for="(value, key) in errors.initMovementPoint" :key="key">{{ value }}</li>
                </ul>
            </div>
            <div class="flex-grow-1">
                <label for="config_maxMovementPoint">{{ $t('config.maxMovementPoint') }}</label>
                <input
                    id="config_maxMovementPoint"
                    ref="config_maxMovementPoint"
                    v-model="gameConfig.maxMovementPoint"
                    type="number"
                >
                <ul v-if="errors.maxMovementPoint">
                    <li v-for="(value, key) in errors.maxMovementPoint" :key="key">{{ value }}</li>
                </ul>
            </div>
        </div>
        <button class="button" type="submit" @click="update">
            {{ $t('save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { GameConfig } from "@/entities/Config/GameConfig";
import { mapGetters } from "vuex";

interface GameConfigState {
    gameConfig: null|GameConfig
    errors: any
}

export default defineComponent({
    name: "GameConfigDetailPage",
    data: function (): GameConfigState {
        return {
            gameConfig: null,
            errors: {}
        };
    },
    methods: {
        update(): void {
            if (this.gameConfig === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateDefaultGameConfig(this.gameConfig).then((res: GameConfig | null) => {
                this.gameConfig = res;
            }).catch((error) => {
                if (error.response) {
                    if (error.response.data.violations) {
                        error.response.data.violations.forEach((datum : { propertyPath: string, title: string }) => {
                            if (datum.propertyPath !== undefined) {
                                if (this.errors[datum.propertyPath] === undefined) {
                                    this.errors[datum.propertyPath] = [];
                                }
                                this.errors[datum.propertyPath].push(datum.title);
                            }
                        });
                    }
                } else if (error.request) {
                    // The request was made but no response was received
                    console.error(error.request);
                } else {
                    // Something happened in setting up the request that triggered an Error
                    console.error('Error', error.message);
                }
            });
        }
    },
    computed: {
        ...mapGetters('auth', [
            'loggedIn',
            'getUserInfo'
        ])
    },
    beforeMount() {
        const gameConfigId = Number(this.$route.params.gameConfigId);
        GameConfigService.loadDefaultGameConfig(gameConfigId).then((res: GameConfig | null) => {
            this.gameConfig = res;
        });
    }
});
</script>

<style lang="scss" scoped>
button {
    cursor: pointer;
    margin: 0 20px;
    padding: 5px 10px;
    color: white;
    font-size: 1.1em;
    letter-spacing: .06em;

    &:hover,
    &:active {
        color: #dffaff;
        text-shadow: 0 0 1px rgb(255, 255, 255), 0 0 1px rgb(255, 255, 255);
    }
}
</style>
