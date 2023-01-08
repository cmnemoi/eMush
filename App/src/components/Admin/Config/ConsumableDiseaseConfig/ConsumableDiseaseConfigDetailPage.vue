<template>
    <div v-if="consumableDiseaseConfig" class="center">
        <h2>{{ $t('admin.consumableDiseaseConfig.pageTitle') }} {{ consumableDiseaseConfig.name }}</h2>
        
        <MapManager
            :label="$t('admin.consumableDiseaseConfig.curesName')"
            :map="consumableDiseaseConfig.curesName"
            mapIndexesType="string"
            @addTuple="addCuresName"
            @removeIndex="removeCuresName"
        />
        <MapManager
            :label="$t('admin.consumableDiseaseConfig.diseasesName')"
            :map="consumableDiseaseConfig.diseasesName"
            mapIndexesType="string"
            @addTuple="addDiseasesName"
            @removeIndex="removeDiseasesName"
        />
        <MapManager
            :label="$t('admin.consumableDiseaseConfig.diseasesChances')"
            :map="consumableDiseaseConfig.diseasesChances"
            mapIndexesType="number"
            @addTuple="addDiseasesChances"
            @removeIndex="removeDiseasesChances"
        />
        <MapManager
            :label="$t('admin.consumableDiseaseConfig.curesChances')"
            :map="consumableDiseaseConfig.curesChances"
            mapIndexesType="number"
            @addTuple="addCuresChances"
            @removeIndex="removeCuresChances"
        />
        <MapManager
            :label="$t('admin.consumableDiseaseConfig.diseasesDelayMin')"
            :map="consumableDiseaseConfig.diseasesDelayMin"
            mapIndexesType="number"
            @addTuple="addDiseasesDelayMin"
            @removeIndex="removeDiseasesDelayMin"
        />
        <MapManager
            :label="$t('admin.consumableDiseaseConfig.diseasesDelayLength')"
            :map="consumableDiseaseConfig.diseasesDelayLength"
            mapIndexesType="number"
            @addTuple="addDiseasesDelayLength"
            @removeIndex="removeDiseasesDelayLength"
        />
        <MapManager
            :label="$t('admin.consumableDiseaseConfig.effectNumber')"
            :map="consumableDiseaseConfig.effectNumber"
            mapIndexesType="number"
            @addTuple="addEffectNumber"
            @removeIndex="removeEffectNumber"
        />


        <button class="action-button" type="submit" @click="update">
            {{ $t('admin.save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { ConsumableDiseaseConfig } from "@/entities/Config/ConsumableDiseaseConfig";
import { handleErrors } from "@/utils/apiValidationErrors";
import { removeItem } from "@/utils/misc";
import MapManager from "@/components/Utils/MapManager.vue";
import ApiService from "@/services/api.service";
import urlJoin from "url-join";

interface ConsumableDiseaseConfigState {
    consumableDiseaseConfig: null|ConsumableDiseaseConfig
    errors: any,
}

export default defineComponent({
    name: "consumableDiseaseConfigDetailPage",
    components: {
        MapManager
    },
    data: function (): ConsumableDiseaseConfigState {
        return {
            consumableDiseaseConfig: null,
            errors: {}
        };
    },
    methods: {
        update(): void {
            if (this.consumableDiseaseConfig === null) {
                return;
            }
            this.errors = {};
            GameConfigService.updateConsumableDiseaseConfig(this.consumableDiseaseConfig)
                .then((res: ConsumableDiseaseConfig | null) => {
                    this.consumableDiseaseConfig = res;
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
        },
        addCuresName(tuple: any[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.consumableDiseaseConfig && this.consumableDiseaseConfig.curesName) {
                this.consumableDiseaseConfig.curesName.set(index, value);
            }
        },
        removeCuresName(index: string): void {
            if (this.consumableDiseaseConfig && this.consumableDiseaseConfig.curesName) {
                this.consumableDiseaseConfig.curesName.delete(index);
            }
        },
        addDiseasesName(tuple: any[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.consumableDiseaseConfig && this.consumableDiseaseConfig.diseasesName) {
                this.consumableDiseaseConfig.diseasesName.set(index, value);
            }
        },
        removeDiseasesName(index: string): void {
            if (this.consumableDiseaseConfig && this.consumableDiseaseConfig.diseasesName) {
                this.consumableDiseaseConfig.diseasesName.delete(index);
            }
        },
        addDiseasesChances(tuple: any[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.consumableDiseaseConfig && this.consumableDiseaseConfig.diseasesChances) {
                this.consumableDiseaseConfig.diseasesChances.set(index, value);
            }
        },
        removeDiseasesChances(index: number): void {
            if (this.consumableDiseaseConfig && this.consumableDiseaseConfig.diseasesChances) {
                this.consumableDiseaseConfig.diseasesChances.delete(index);
            }
        },
        addCuresChances(tuple: any[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.consumableDiseaseConfig && this.consumableDiseaseConfig.curesChances) {
                this.consumableDiseaseConfig.curesChances.set(index, value);
            }
        },
        removeCuresChances(index: number): void {
            if (this.consumableDiseaseConfig && this.consumableDiseaseConfig.curesChances) {
                this.consumableDiseaseConfig.curesChances.delete(index);
            }
        },
        addDiseasesDelayMin(tuple: any[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.consumableDiseaseConfig && this.consumableDiseaseConfig.diseasesDelayMin) {
                this.consumableDiseaseConfig.diseasesDelayMin.set(index, value);
            }
        },
        removeDiseasesDelayMin(index: number): void {
            if (this.consumableDiseaseConfig && this.consumableDiseaseConfig.diseasesDelayMin) {
                this.consumableDiseaseConfig.diseasesDelayMin.delete(index);
            }
        },
        addDiseasesDelayLength(tuple: any[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.consumableDiseaseConfig && this.consumableDiseaseConfig.diseasesDelayLength) {
                this.consumableDiseaseConfig.diseasesDelayLength.set(index, value);
            }
        },
        removeDiseasesDelayLength(index: number): void {
            if (this.consumableDiseaseConfig && this.consumableDiseaseConfig.diseasesDelayLength) {
                this.consumableDiseaseConfig.diseasesDelayLength.delete(index);
            }
        },
        addEffectNumber(tuple: any[]): void {
            const index = tuple[0];
            const value = tuple[1];
            if (this.consumableDiseaseConfig && this.consumableDiseaseConfig.effectNumber) {
                this.consumableDiseaseConfig.effectNumber.set(index, value);
            }
        },
        removeEffectNumber(index: number): void {
            if (this.consumableDiseaseConfig && this.consumableDiseaseConfig.effectNumber) {
                this.consumableDiseaseConfig.effectNumber.delete(index);
            }
        },
    },
    beforeMount() {
        const consumableDiseaseConfigId = String(this.$route.params.consumableDiseaseConfigId);
        GameConfigService.loadConsumableDiseaseConfig(Number(consumableDiseaseConfigId)).then((res: ConsumableDiseaseConfig | null) => {
            this.consumableDiseaseConfig = res;
        });
    }
});
</script>

<style lang="scss" scoped>

.consumableDiseaseConfigCheckbox {
    margin-left: 10px;
    margin-right: 10px;
}

</style>
