<template>
    <div v-if="difficultyConfig" class="center">
        <div class="flex-row">
            <Input
                :label="$t('admin.difficultyConfig.name')"
                id="difficultyConfig_name"
                v-model="difficultyConfig.name"
                type="text"
                :errors="errors.name"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.difficultyConfig.equipmentBreakRate')"
                id="difficultyConfig_equipmentBreakRate"
                v-model="difficultyConfig.equipmentBreakRate"
                type="number"
                :errors="errors.name"
            />
            <Input
                :label="$t('admin.difficultyConfig.equipmentBreakRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.equipmentBreakRate"
                type="number"
                :errors="errors.equipmentBreakRate"
            />
            
            <Input
                :label="$t('admin.difficultyConfig.doorBreakRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.doorBreakRate"
                type="number"
                :errors="errors.doorBreakRate"
            />
            
            <Input
                :label="$t('admin.difficultyConfig.equipmentFireBreakRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.equipmentFireBreakRate"
                type="number"
                :errors="errors.equipmentFireBreakRate"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.difficultyConfig.startingFireRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.startingFireRate"
                type="number"
                :errors="errors.startingFireRate"
            />
            
            <Input
                :label="$t('admin.difficultyConfig.propagatingFireRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.propagatingFireRate"
                type="number"
                :errors="errors.propagatingFireRate"
            />
            
            <Input
                :label="$t('admin.difficultyConfig.hullFireDamageRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.hullFireDamageRate"
                type="number"
                :errors="errors.hullFireDamageRate"
            />
            
            <Input
                :label="$t('admin.difficultyConfig.tremorRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.tremorRate"
                type="number"
                :errors="errors.tremorRate"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.difficultyConfig.electricArcRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.electricArcRate"
                type="number"
                :errors="errors.electricArcRate"
            />
            
            <Input
                :label="$t('admin.difficultyConfig.metalPlateRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.metalPlateRate"
                type="number"
                :errors="errors.metalPlateRate"
            />
            
            <Input
                :label="$t('admin.difficultyConfig.panicCrisisRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.panicCrisisRate"
                type="number"
                :errors="errors.panicCrisisRate"
            />
            <Input
                :label="$t('admin.difficultyConfig.plantDiseaseRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.plantDiseaseRate"
                type="number"
                :errors="errors.plantDiseaseRate"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.difficultyConfig.cycleDiseaseRate')"
                id="difficultyConfig_attribute"
                v-model="difficultyConfig.cycleDiseaseRate"
                type="number"
                :errors="errors.cycleDiseaseRate"
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
import { DifficultyConfig } from "@/entities/Config/DifficultyConfig";
import { handleErrors } from "@/utils/apiValidationErrors";
import Input from "@/components/Utils/Input.vue";

interface DifficultyConfigState {
    difficultyConfig: null|DifficultyConfig
    errors: any
}

export default defineComponent({
    name: "DifficultyConfigDetailPage",
    components: {
        Input
    },
    data: function (): DifficultyConfigState {
        return {
            difficultyConfig: null,
            errors: {}
        };
    },
    methods: {
        update(): void {
            if (this.difficultyConfig === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateDifficultyConfig(this.difficultyConfig)
                .then((res: DifficultyConfig | null) => {
                    this.difficultyConfig = res;
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
        const difficultyConfigId = Number(this.$route.params.difficultyConfigId);
        GameConfigService.loadDifficultyConfig(difficultyConfigId).then((res: DifficultyConfig | null) => {
            this.difficultyConfig = res;
        });
    }
});
</script>

<style lang="scss" scoped>

</style>
