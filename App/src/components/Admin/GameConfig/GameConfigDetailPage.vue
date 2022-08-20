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
                <ErrorList v-if="errors.name" :errors="errors.name"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="config_language">{{ $t('config.language') }}</label>
                <input
                    id="config_language"
                    ref="config_language"
                    v-model="gameConfig.language"
                    type="text"
                >
                <ErrorList v-if="errors.language" :errors="errors.language"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="config_timeZone">{{ $t('config.timeZone') }}</label>
                <input
                    id="config_timeZone"
                    ref="config_timeZone"
                    v-model="gameConfig.timeZone"
                    type="text"
                >
                <ErrorList v-if="errors.timeZone" :errors="errors.timeZone"></ErrorList>
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
                <ErrorList v-if="errors.nbMush" :errors="errors.nbMush"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="config_cyclePerGameDay">{{ $t('config.cyclePerGameDay') }}</label>
                <input
                    id="config_cyclePerGameDay"
                    ref="config_cyclePerGameDay"
                    v-model="gameConfig.cyclePerGameDay"
                    type="text"
                >
                <ErrorList v-if="errors.cyclePerGameDay" :errors="errors.cyclePerGameDay"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="config_cycleLength">{{ $t('config.cycleLength') }}</label>
                <input
                    id="config_cycleLength"
                    ref="config_cycleLength"
                    v-model="gameConfig.cycleLength"
                    type="text"
                >
                <ErrorList v-if="errors.cycleLength" :errors="errors.cycleLength"></ErrorList>
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
                <ErrorList v-if="errors.maxNumberPrivateChannel" :errors="errors.maxNumberPrivateChannel"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="config_maxItemInInventory">{{ $t('config.maxItemInInventory') }}</label>
                <input
                    id="config_maxItemInInventory"
                    ref="config_maxItemInInventory"
                    v-model="gameConfig.maxItemInInventory"
                    type="number"
                >
                <ErrorList v-if="errors.maxItemInInventory" :errors="errors.maxItemInInventory"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="config_initSatiety">{{ $t('config.initSatiety') }}</label>
                <input
                    id="config_initSatiety"
                    ref="config_initSatiety"
                    v-model="gameConfig.initSatiety"
                    type="number"
                >
                <ErrorList v-if="errors.initSatiety" :errors="errors.initSatiety"></ErrorList>
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
                <ErrorList v-if="errors.initHealthPoint" :errors="errors.initHealthPoint"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="config_maxHealthPoint">{{ $t('config.maxHealthPoint') }}</label>
                <input
                    id="config_maxHealthPoint"
                    ref="config_maxHealthPoint"
                    v-model="gameConfig.maxHealthPoint"
                    type="number"
                >
                <ErrorList v-if="errors.maxHealthPoint" :errors="errors.maxHealthPoint"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="config_initMoralPoint">{{ $t('config.initMoralPoint') }}</label>
                <input
                    id="config_initMoralPoint"
                    ref="config_initMoralPoint"
                    v-model="gameConfig.initMoralPoint"
                    type="number"
                >
                <ErrorList v-if="errors.initMoralPoint" :errors="errors.initMoralPoint"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="config_maxMoralPoint">{{ $t('config.maxMoralPoint') }}</label>
                <input
                    id="config_maxMoralPoint"
                    ref="config_maxMoralPoint"
                    v-model="gameConfig.maxMoralPoint"
                    type="number"
                >
                <ErrorList v-if="errors.maxMoralPoint" :errors="errors.maxMoralPoint"></ErrorList>
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
                <ErrorList v-if="errors.initActionPoint" :errors="errors.initActionPoint"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="config_maxActionPoint">{{ $t('config.maxActionPoint') }}</label>
                <input
                    id="config_maxActionPoint"
                    ref="config_maxActionPoint"
                    v-model="gameConfig.maxActionPoint"
                    type="number"
                >
                <ErrorList v-if="errors.maxActionPoint" :errors="errors.maxActionPoint"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="config_initMovementPoint">{{ $t('config.initMovementPoint') }}</label>
                <input
                    id="config_initMovementPoint"
                    ref="config_initMovementPoint"
                    v-model="gameConfig.initMovementPoint"
                    type="number"
                >
                <ErrorList v-if="errors.initMovementPoint" :errors="errors.initMovementPoint"></ErrorList>
            </div>
            <div class="flex-grow-1">
                <label for="config_maxMovementPoint">{{ $t('config.maxMovementPoint') }}</label>
                <input
                    id="config_maxMovementPoint"
                    ref="config_maxMovementPoint"
                    v-model="gameConfig.maxMovementPoint"
                    type="number"
                >
                <ErrorList v-if="errors.maxMovementPoint" :errors="errors.maxMovementPoint"></ErrorList>
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
import ErrorList from "@/components/Utils/ErrorList.vue";
import { handleErrors } from "@/utils/apiValidationErrors";

interface GameConfigState {
    gameConfig: null|GameConfig
    errors: any
}

export default defineComponent({
    name: "GameConfigDetailPage",
    components: {
        ErrorList
    },
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
            GameConfigService.updateGameConfig(this.gameConfig)
                .then((res: GameConfig | null) => {
                    this.gameConfig = res;
                })
                .catch((error) => {
                    if (error.response) {
                        if (error.response.data.violations) {
                            this.errors = handleErrors(error.response.data.violations);
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
        GameConfigService.loadGameConfig(gameConfigId).then((res: GameConfig | null) => {
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
