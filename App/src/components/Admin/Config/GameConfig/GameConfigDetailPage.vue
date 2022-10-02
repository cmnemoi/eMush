<template>
    <div v-if="gameConfig" class="center">
        <div class="flex-row">
            <Input
                :label="$t('admin.gameConfig.name')"
                id="gameConfig_name"
                v-model="gameConfig.name"
                type="text"
                :errors="errors.name"
            />
            <Input
                :label="$t('admin.gameConfig.language')"
                id="gameConfig_language"
                v-model="gameConfig.language"
                type="text"
                :errors="errors.language"
            />
            <Input
                :label="$t('admin.gameConfig.timeZone')"
                id="gameConfig_timeZone"
                v-model="gameConfig.timeZone"
                type="text"
                :errors="errors.timeZone"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.gameConfig.nbMush')"
                id="gameConfig_nbMush"
                v-model="gameConfig.nbMush"
                type="number"
                :errors="errors.nbMush"
            />
            <Input
                :label="$t('admin.gameConfig.cyclePerGameDay')"
                id="gameConfig_cyclePerGameDay"
                v-model="gameConfig.cyclePerGameDay"
                type="number"
                :errors="errors.cyclePerGameDay"
            />
            <Input
                :label="$t('admin.gameConfig.cycleLength')"
                id="gameConfig_cycleLength"
                v-model="gameConfig.cycleLength"
                type="number"
                :errors="errors.cycleLength"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.gameConfig.maxNumberPrivateChannel')"
                id="gameConfig_maxNumberPrivateChannel"
                v-model="gameConfig.maxNumberPrivateChannel"
                type="number"
                :errors="errors.maxNumberPrivateChannel"
            />
            <Input
                :label="$t('admin.gameConfig.maxItemInInventory')"
                id="gameConfig_maxItemInInventory"
                v-model="gameConfig.maxItemInInventory"
                type="number"
                :errors="errors.maxItemInInventory"
            />
            <Input
                :label="$t('admin.gameConfig.initSatiety')"
                id="gameConfig_initSatiety"
                v-model="gameConfig.initSatiety"
                type="number"
                :errors="errors.initSatiety"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.gameConfig.initHealthPoint')"
                id="gameConfig_initHealthPoint"
                v-model="gameConfig.initHealthPoint"
                type="number"
                :errors="errors.initHealthPoint"
            />
            <Input
                :label="$t('admin.gameConfig.maxHealthPoint')"
                id="gameConfig_maxHealthPoint"
                v-model="gameConfig.maxHealthPoint"
                type="number"
                :errors="errors.maxHealthPoint"
            />
            <Input
                :label="$t('admin.gameConfig.initMoralPoint')"
                id="gameConfig_initMoralPoint"
                v-model="gameConfig.initMoralPoint"
                type="number"
                :errors="errors.initMoralPoint"
            />
            <Input
                :label="$t('admin.gameConfig.maxMoralPoint')"
                id="gameConfig_maxMoralPoint"
                v-model="gameConfig.maxMoralPoint"
                type="number"
                :errors="errors.maxMoralPoint"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.gameConfig.initActionPoint')"
                id="gameConfig_initActionPoint"
                v-model="gameConfig.initActionPoint"
                type="number"
                :errors="errors.initActionPoint"
            />
            <Input
                :label="$t('admin.gameConfig.maxActionPoint')"
                id="gameConfig_maxActionPoint"
                v-model="gameConfig.maxActionPoint"
                type="number"
                :errors="errors.maxActionPoint"
            />
            <Input
                :label="$t('admin.gameConfig.initMovementPoint')"
                id="gameConfig_initMovementPoint"
                v-model="gameConfig.initMovementPoint"
                type="number"
                :errors="errors.initMovementPoint"
            />
            <Input
                :label="$t('admin.gameConfig.maxMovementPoint')"
                id="gameConfig_maxMovementPoint"
                v-model="gameConfig.maxMovementPoint"
                type="number"
                :errors="errors.maxMovementPoint"
            />
        </div>
        <button class="action-button" type="submit" @click="update">
            {{ $t('save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { GameConfig } from "@/entities/Config/GameConfig";
import { handleErrors } from "@/utils/apiValidationErrors";
import Input from "@/components/Utils/Input.vue";

interface GameConfigState {
    gameConfig: null|GameConfig
    errors: any
}

export default defineComponent({
    name: "GameConfigDetailPage",
    components: {
        Input
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
    beforeMount() {
        const gameConfigId = Number(this.$route.params.gameConfigId);
        GameConfigService.loadGameConfig(gameConfigId).then((res: GameConfig | null) => {
            this.gameConfig = res;
        });
    }
});
</script>

<style lang="scss" scoped>

</style>
