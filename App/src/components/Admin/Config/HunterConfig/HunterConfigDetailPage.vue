<template>
    <div v-if="hunterConfig" class="center">
        <div class="flex-row">
            <Input
                :label="$t('admin.hunterConfig.name')"
                id="hunterConfig_name"
                v-model="hunterConfig.name"
                type="text"
                :errors="errors.name"
            />
            <Input
                :label="$t('admin.hunterConfig.hunterName')"
                id="hunterConfig_hunterName"
                v-model="hunterConfig.hunterName"
                type="text"
                :errors="errors.hunterName"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.hunterConfig.initialHealth')"
                id="hunterConfig_initialHealth"
                v-model="hunterConfig.initialHealth"
                type="number"
                :errors="errors.initialHealth"
            />
            <Input
                :label="$t('admin.hunterConfig.hitChance')"
                id="hunterConfig_hitChance"
                v-model="hunterConfig.hitChance"
                type="number"
                :errors="errors.hitChance"
            />
            <Input
                :label="$t('admin.hunterConfig.dodgeChance')"
                id="hunterConfig_dodgeChance"
                v-model="hunterConfig.dodgeChance"
                type="number"
                :errors="errors.dodgeChance"
            />
            <Input
                :label="$t('admin.hunterConfig.drawCost')"
                id="hunterConfig_drawCost"
                v-model="hunterConfig.drawCost"
                type="number"
                :errors="errors.drawCost"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.hunterConfig.maxPerWave')"
                id="hunterConfig_maxPerWave"
                v-model="hunterConfig.maxPerWave"
                type="number"
                :errors="errors.maxPerWave"
            />
            <Input
                :label="$t('admin.hunterConfig.drawWeight')"
                id="hunterConfig_drawWeight"
                v-model="hunterConfig.drawWeight"
                type="number"
                :errors="errors.drawWeight"
            />
            <Input
                :label="$t('admin.hunterConfig.spawnDifficulty')"
                id="hunterConfig_spawnDifficulty"
                v-model="hunterConfig.spawnDifficulty"
                type="number"
                :errors="errors.spawnDifficulty"
            />
            <Input
                :label="$t('admin.hunterConfig.bonusAfterFailedShot')"
                id="hunterConfig_bonusAfterFailedShot"
                v-model="hunterConfig.bonusAfterFailedShot"
                type="number"
                :errors="errors.bonusAfterFailedShot"
            />
            <Input
                :label="$t('admin.hunterConfig.numberOfActionsPerCycle')"
                id="hunterConfig_numberOfActionsPerCycle"
                v-model="hunterConfig.numberOfActionsPerCycle"
                type="number"
                :errors="errors.numberOfActionsPerCycle"
            />
        </div>
        <MapManager
            :label="$t('admin.hunterConfig.damageRange')"
            :map="hunterConfig.damageRange"
            mapIndexesType="number"
            mapValuesType="number"
            @addTuple="addDamage"
            @removeIndex="removeDamage"
        />
        <MapManager
            :label="$t('admin.hunterConfig.scrapDropTable')"
            :map="hunterConfig.scrapDropTable"
            mapIndexesType="string"
            mapValuesType="number"
            @addTuple="addScrapDrop"
            @removeIndex="removeScrapDrop"
        />
        <MapManager
            :label="$t('admin.hunterConfig.numberOfDroppedScrap')"
            :map="hunterConfig.numberOfDroppedScrap"
            mapIndexesType="string"
            mapValuesType="number"
            @addTuple="addNumberDrop"
            @removeIndex="removeNumberDrop"
        />
        <h3>{{ $t("admin.hunterConfig.initialStatuses") }}</h3>
        <ChildCollectionManager :children="hunterConfig.initialStatuses" @addId="addNewStatusConfig" @remove="removeStatusConfig">
            <template #header="child">
                <span><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
    </div>
    <UpdateConfigButtons
        @create="create"
        @update="update"
    />
</template>

<script lang="ts">
import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { defineComponent } from "vue";
import { handleErrors } from "@/utils/apiValidationErrors";
import { removeItem } from "@/utils/misc";
import ChildCollectionManager from "@/components/Utils/ChildcollectionManager.vue";
import Input from "@/components/Utils/Input.vue";
import MapManager from "@/components/Utils/MapManager.vue";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";
import GameConfigService from "@/services/game_config.service";
import HunterConfigService from "@/services/hunter.config.service";
import { HunterConfig } from "@/entities/Config/HunterConfig";
import { StatusConfig } from "@/entities/Config/StatusConfig";


interface HunterConfigState {
    hunterConfig: null|HunterConfig
    errors: any
}

export default defineComponent({
    name: "HunterConfigDetailPage",
    components: {
        ChildCollectionManager,
        Input,
        MapManager,
        UpdateConfigButtons
    },
    data: function (): HunterConfigState {
        return {
            hunterConfig: null,
            errors: {}
        };
    },
    methods: {
        create(): void {
            const newHunterConfig = (new HunterConfig()).load(this.hunterConfig?.jsonEncode());
            
            newHunterConfig.id = null;
            HunterConfigService.createHunterConfig(newHunterConfig).then((res: HunterConfig | null) => {
                this.hunterConfig = res;
                if (null === this.hunterConfig) return null;
                ApiService.get(urlJoin(process.env.VUE_APP_API_URL + 'hunter_configs', String(this.hunterConfig.id), 'initial_statuses'))
                    .then((result) => {
                        const initialStatuses: StatusConfig[] = [];
                        result.data['hydra:member'].forEach((datum: any) => {
                            initialStatuses.push((new StatusConfig()).load(datum));
                        });

                        if (this.hunterConfig instanceof HunterConfig) {
                            this.hunterConfig.initialStatuses = initialStatuses;
                        }
                    });
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
            if (this.hunterConfig === null) {
                return;
            }
            this.errors = {};
            HunterConfigService.updateHunterConfig(this.hunterConfig)
                .then((res: HunterConfig | null) => {
                    this.hunterConfig = res;
                    if (null === this.hunterConfig) return null;
                    ApiService.get(urlJoin(process.env.VUE_APP_API_URL + 'hunter_configs', String(this.hunterConfig.id), 'initial_statuses'))
                        .then((result) => {
                            const initialStatuses: StatusConfig[] = [];
                            result.data['hydra:member'].forEach((datum: any) => {
                                initialStatuses.push((new StatusConfig()).load(datum));
                            });

                            if (this.hunterConfig instanceof HunterConfig) {
                                this.hunterConfig.initialStatuses = initialStatuses;
                            }
                        });
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
        addDamage(tuple: number[]): void {
            if (this.hunterConfig === null || this.hunterConfig.damageRange === null) {
                return;
            }
            const key = tuple[0];
            const value = tuple[1];
            this.hunterConfig.damageRange.set(key, value);
        },
        removeDamage(key: number): void {
            if (this.hunterConfig === null || this.hunterConfig.damageRange === null) {
                return;
            }
            this.hunterConfig.damageRange.delete(key);
        },
        addScrapDrop(tuple: any[]): void {
            if (this.hunterConfig === null || this.hunterConfig.scrapDropTable === null) {
                return;
            }
            const key = tuple[0];
            const value = tuple[1];
            this.hunterConfig.scrapDropTable.set(key, value);
        },
        removeScrapDrop(key: string): void {
            if (this.hunterConfig === null || this.hunterConfig.scrapDropTable === null) {
                return;
            }
            this.hunterConfig.scrapDropTable.delete(key);
        },
        addNumberDrop(tuple: number[]): void {
            if (this.hunterConfig === null || this.hunterConfig.numberOfDroppedScrap === null) {
                return;
            }
            const key = tuple[0];
            const value = tuple[1];
            this.hunterConfig.numberOfDroppedScrap.set(key, value);
        },
        removeNumberDrop(key: number): void {
            if (this.hunterConfig === null || this.hunterConfig.numberOfDroppedScrap === null) {
                return;
            }
            this.hunterConfig.numberOfDroppedScrap.delete(key);
        },
        addNewStatusConfig(selectedId: integer){
            GameConfigService.loadStatusConfig(selectedId).then((res) => {
                if (res && this.hunterConfig && this.hunterConfig.initialStatuses){
                    this.hunterConfig.initialStatuses.push(res);
                }
            });
        },
        removeStatusConfig(statusConfig: any){
            if (this.hunterConfig && this.hunterConfig.initialStatuses){
                this.hunterConfig.initialStatuses = removeItem(this.hunterConfig.initialStatuses, statusConfig);
            }
        },
    },
    beforeMount() {   
        const hunterConfigId = Number(this.$route.params.hunterConfigId);
        HunterConfigService.loadHunterConfig(hunterConfigId).then((res: HunterConfig | null) => {
            this.hunterConfig = res;
            ApiService.get(urlJoin(process.env.VUE_APP_API_URL + 'hunter_configs', String(hunterConfigId), 'initial_statuses'))
                .then((result) => {
                    const initialStatuses: StatusConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        initialStatuses.push((new StatusConfig()).load(datum));
                    });

                    if (this.hunterConfig instanceof HunterConfig) {
                        this.hunterConfig.initialStatuses = initialStatuses;
                    }
                });
        });     
    }
});
</script>

<style lang="scss" scoped>

</style>
