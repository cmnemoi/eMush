<template>
    <div v-if="loggedIn">
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
                </div>
                <div class="flex-grow-1">
                    <label for="config_language">{{ $t('config.language') }}</label>
                    <input
                        id="config_language"
                        ref="config_language"
                        v-model="gameConfig.language"
                        type="text"
                    >
                </div>
                <div class="flex-grow-1">
                    <label for="config_timeZone">{{ $t('config.timeZone') }}</label>
                    <input
                        id="config_timeZone"
                        ref="config_timeZone"
                        v-model="gameConfig.timeZone"
                        type="text"
                    >
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
                </div>
                <div class="flex-grow-1">
                    <label for="config_cyclePerGameDay">{{ $t('config.cyclePerGameDay') }}</label>
                    <input
                        id="config_cyclePerGameDay"
                        ref="config_cyclePerGameDay"
                        v-model="gameConfig.cyclePerGameDay"
                        type="text"
                    >
                </div>
                <div class="flex-grow-1">
                    <label for="config_cycleLength">{{ $t('config.cycleLength') }}</label>
                    <input
                        id="config_cycleLength"
                        ref="config_cycleLength"
                        v-model="gameConfig.cycleLength"
                        type="text"
                    >
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
                </div>
                <div class="flex-grow-1">
                    <label for="config_maxItemInInventory">{{ $t('config.maxItemInInventory') }}</label>
                    <input
                        id="config_maxItemInInventory"
                        ref="config_maxItemInInventory"
                        v-model="gameConfig.maxItemInInventory"
                        type="number"
                    >
                </div>
                <div class="flex-grow-1">
                    <label for="config_initSatiety">{{ $t('config.initSatiety') }}</label>
                    <input
                        id="config_initSatiety"
                        ref="config_initSatiety"
                        v-model="gameConfig.initSatiety"
                        type="number"
                    >
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
                </div>
                <div class="flex-grow-1">
                    <label for="config_maxHealthPoint">{{ $t('config.maxHealthPoint') }}</label>
                    <input
                        id="config_maxHealthPoint"
                        ref="config_maxHealthPoint"
                        v-model="gameConfig.maxHealthPoint"
                        type="number"
                    >
                </div>
                <div class="flex-grow-1">
                    <label for="config_initMoralPoint">{{ $t('config.initMoralPoint') }}</label>
                    <input
                        id="config_initMoralPoint"
                        ref="config_initMoralPoint"
                        v-model="gameConfig.initMoralPoint"
                        type="number"
                    >
                </div>
                <div class="flex-grow-1">
                    <label for="config_maxMoralPoint">{{ $t('config.maxMoralPoint') }}</label>
                    <input
                        id="config_maxMoralPoint"
                        ref="config_maxMoralPoint"
                        v-model="gameConfig.maxMoralPoint"
                        type="number"
                    >
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
                </div>
                <div class="flex-grow-1">
                    <label for="config_maxActionPoint">{{ $t('config.maxActionPoint') }}</label>
                    <input
                        id="config_maxActionPoint"
                        ref="config_maxActionPoint"
                        v-model="gameConfig.maxActionPoint"
                        type="number"
                    >
                </div>
                <div class="flex-grow-1">
                    <label for="config_initMovementPoint">{{ $t('config.initMovementPoint') }}</label>
                    <input
                        id="config_initMovementPoint"
                        ref="config_initMovementPoint"
                        v-model="gameConfig.initMovementPoint"
                        type="number"
                    >
                </div>
                <div class="flex-grow-1">
                    <label for="config_maxMovementPoint">{{ $t('config.maxMovementPoint') }}</label>
                    <input
                        id="config_maxMovementPoint"
                        ref="config_maxMovementPoint"
                        v-model="gameConfig.maxMovementPoint"
                        type="number"
                    >
                </div>
            </div>
            <button class="button" type="submit" @click="update">
                {{ $t('save') }}
            </button>
        </div>
    </div>
    <div v-else>
        <home-page />
    </div>
</template>

<script lang="ts">
import { mapGetters } from "vuex";
import HomePage from "@/components/HomePage.vue";
import { defineComponent } from "vue";
import { GameConfig } from "@/entities/Config/GameConfig";
import GameConfigService from "@/services/game_config.service";

interface GameConfigState {
    loading: boolean,
    gameConfig: null|GameConfig
}

export default defineComponent ({
    name: "DefaultConfigPage",
    components: {
        HomePage,
    },
    data: function (): GameConfigState {
        return {
            loading: false,
            gameConfig: null
        };
    },
    beforeMount() {
        this.loading = true;
        GameConfigService.loadDefaultGameConfig(1).then((res: GameConfig | null) => {
            this.loading = false;
            this.gameConfig = res;
        });
    },
    methods: {
        update(): void {
            if (this.gameConfig === null) {
                return;
            }

            this.loading = true;
            GameConfigService.updateDefaultGameConfig(this.gameConfig).then((res: GameConfig | null) => {
                this.loading = false;
                this.gameConfig = res;
            }).catch(() => {
                this.loading = false;
            });
        },
    },
    computed: {
        ...mapGetters('auth', [
            'loggedIn',
            'getUserInfo'
        ])
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
