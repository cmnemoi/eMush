<template>
    <div v-if="consumableDiseaseConfig" class="center">
        <h2>{{ $t('admin.consumableDiseaseConfig.pageTitle') }} {{ consumableDiseaseConfig.name }}</h2>
        <div class="flex-row">
            <Input
                :label="$t('admin.consumableDiseaseConfig.name')"
                id="consumableDiseaseConfig_name"
                v-model="consumableDiseaseConfig.name"
                type="text"
                :errors="errors.name"
            />
            <Input
                :label="$t('admin.consumableDiseaseConfig.causeName')"
                id="consumableDiseaseConfig_causeName"
                v-model="consumableDiseaseConfig.causeName"
                type="text"
                :errors="errors.name"
            />
        </div>
        <MapManager
            :label="$t('admin.consumableDiseaseConfig.curesName')"
            :map="consumableDiseaseConfig.curesName"
            mapIndexesType="string"
            mapValuesType="number"
            @addTuple="addCuresName"
            @removeIndex="removeCuresName"
        />
        <MapManager
            :label="$t('admin.consumableDiseaseConfig.diseasesName')"
            :map="consumableDiseaseConfig.diseasesName"
            mapIndexesType="string"
            mapValuesType="number"
            @addTuple="addDiseasesName"
            @removeIndex="removeDiseasesName"
        />
        <MapManager
            :label="$t('admin.consumableDiseaseConfig.diseasesChances')"
            :map="consumableDiseaseConfig.diseasesChances"
            mapIndexesType="number"
            mapValuesType="number"
            @addTuple="addDiseasesChances"
            @removeIndex="removeDiseasesChances"
        />
        <MapManager
            :label="$t('admin.consumableDiseaseConfig.curesChances')"
            :map="consumableDiseaseConfig.curesChances"
            mapIndexesType="number"
            mapValuesType="number"
            @addTuple="addCuresChances"
            @removeIndex="removeCuresChances"
        />
        <MapManager
            :label="$t('admin.consumableDiseaseConfig.diseasesDelayMin')"
            :map="consumableDiseaseConfig.diseasesDelayMin"
            mapIndexesType="number"
            mapValuesType="number"
            @addTuple="addDiseasesDelayMin"
            @removeIndex="removeDiseasesDelayMin"
        />
        <MapManager
            :label="$t('admin.consumableDiseaseConfig.diseasesDelayLength')"
            :map="consumableDiseaseConfig.diseasesDelayLength"
            mapIndexesType="number"
            mapValuesType="number"
            @addTuple="addDiseasesDelayLength"
            @removeIndex="removeDiseasesDelayLength"
        />
        <MapManager
            :label="$t('admin.consumableDiseaseConfig.effectNumber')"
            :map="consumableDiseaseConfig.effectNumber"
            mapIndexesType="number"
            mapValuesType="number"
            @addTuple="addEffectNumber"
            @removeIndex="removeEffectNumber"
        />
        <UpdateConfigButtons
            @create="create"
            @update="update"
        />
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { ConsumableDiseaseConfig } from "@/entities/Config/ConsumableDiseaseConfig";
import { handleErrors } from "@/utils/apiValidationErrors";
import urlJoin from "url-join";
import Input from "@/components/Utils/Input.vue";
import MapManager from "@/components/Utils/MapManager.vue";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";

interface ConsumableDiseaseConfigState {
    consumableDiseaseConfig: null|ConsumableDiseaseConfig
    errors: any,
}

export default defineComponent({
    name: "consumableDiseaseConfigDetailPage",
    components: {
        Input,
        MapManager,
        UpdateConfigButtons,
    },
    data: function (): ConsumableDiseaseConfigState {
        return {
            consumableDiseaseConfig: null,
            errors: {}
        };
    },
    methods: {
        create(): void {
            if (!this.consumableDiseaseConfig) return;
            
            const newConsumableDiseaseConfig = this.consumableDiseaseConfig;
            newConsumableDiseaseConfig.id = null;
            
            // @ts-ignore
            GameConfigService.createConsumableDiseaseConfig(newConsumableDiseaseConfig)
                .then((res: ConsumableDiseaseConfig | null) => {
                    const newConsumableDiseaseConfigUrl = urlJoin(import.meta.env.VITE_URL + '/config/consumable-disease-config', String(res?.id));
                    window.location.href = newConsumableDiseaseConfigUrl;
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
        update(): void {
            if (this.consumableDiseaseConfig === null) {
                return;
            }
            this.errors = {};
            //@ts-ignore
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
