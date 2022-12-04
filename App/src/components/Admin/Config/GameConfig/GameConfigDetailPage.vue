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
        </div>
        <button class="action-button" type="submit" @click="update">
            {{ $t('admin.save') }}
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
