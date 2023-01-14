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
        <h3>{{ $t("admin.gameConfig.daedalusConfig") }}</h3>
        <Pannel>
            <template #header>
                <div class="header-container">
                    <slot name="header" v-bind="gameConfig.daedalusConfig"/>
                    <span>{{ gameConfig.daedalusConfig?.id  }} - {{ gameConfig.daedalusConfig?.name  }}</span>
                    <button @click="test">{{$t('admin.buttons.delete')}}</button>
                </div>
            </template>
        </Pannel>
        <h3>{{ $t("admin.gameConfig.characterConfigs") }}</h3>
        <ChildCollectionManager :children="gameConfig.characterConfigs" @addId="addNewCharacterConfig" @remove="removeCharacterConfig">
            <template #header="child">
                <span>{{ child.id }} - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        <h3>{{ $t("admin.gameConfig.statusConfigs") }}</h3>
        <ChildCollectionManager :children="gameConfig.statusConfigs" @addId="addNewStatusConfig" @remove="removeStatusConfig">
            <template #header="child">
                <span>{{ child.id }} - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        <h3>{{ $t("admin.gameConfig.equipmentConfigs") }}</h3>
        <ChildCollectionManager :children="gameConfig.equipmentConfigs" @addId="addNewEquipmentConfig" @remove="removeEquipmentConfig">
            <template #header="child">
                <span>{{ child.id }} - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        <h3>{{ $t("admin.gameConfig.diseaseCauseConfigs") }}</h3>
        <ChildCollectionManager :children="gameConfig.diseaseCauseConfigs" @addId="addNewDiseaseCauseConfig" @remove="removeDiseaseCauseConfig">
            <template #header="child">
                <span>{{ child.id }} - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        <button class="action-button" type="submit" @click="update">
            {{ $t('admin.save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { CharacterConfig } from "@/entities/Config/CharacterConfig";
import { DaedalusConfig } from "@/entities/Config/DaedalusConfig";
import { DiseaseCauseConfig } from "@/entities/Config/DiseaseCauseConfig";
import { EquipmentConfig } from "@/entities/Config/EquipmentConfig";
import { GameConfig } from "@/entities/Config/GameConfig";
import { StatusConfig } from "@/entities/Config/StatusConfig";
import { handleErrors } from "@/utils/apiValidationErrors";
import ChildCollectionManager from "@/components/Utils/ChildcollectionManager.vue";
import Input from "@/components/Utils/Input.vue";
import Pannel from "@/components/Utils/Pannel.vue";
import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { removeItem } from "@/utils/misc";

interface GameConfigState {
    gameConfig: null|GameConfig
    errors: any
}

export default defineComponent({
    name: "GameConfigDetailPage",
    components: {
        ChildCollectionManager,
        Input,
        Pannel
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
            // @ts-ignore
            GameConfigService.updateGameConfig(this.gameConfig)
                .then((res: GameConfig | null) => {
                    this.gameConfig = res;
                    if (this.gameConfig !== null){
                        ApiService.get(urlJoin(process.env.VUE_APP_API_URL + 'game_configs', String(this.gameConfig.id), 'daedalus_config'))
                            .then((result) => {
                                const daedalusConfig: DaedalusConfig = new DaedalusConfig();
                                daedalusConfig.load(result.data);

                                if (this.gameConfig instanceof GameConfig) {
                                    this.gameConfig.daedalusConfig = daedalusConfig;
                                }
                            });
                        ApiService.get(urlJoin(process.env.VUE_APP_API_URL + 'game_configs', String(this.gameConfig.id), 'character_configs'))
                            .then((result) => {
                                const characterConfigs: CharacterConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    characterConfigs.push((new CharacterConfig()).load(datum));
                                });

                                if (this.gameConfig instanceof GameConfig) {
                                    this.gameConfig.characterConfigs = characterConfigs;
                                }
                            });
                        ApiService.get(urlJoin(process.env.VUE_APP_API_URL + 'game_configs', String(this.gameConfig.id), 'equipment_configs?pagination=true'))
                            .then((result) => {
                                const equipmentConfigs: EquipmentConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    equipmentConfigs.push((new EquipmentConfig()).load(datum));
                                });
                            
                                if (this.gameConfig instanceof GameConfig) {
                                    this.gameConfig.equipmentConfigs = equipmentConfigs;
                                }
                            });
                        ApiService.get(urlJoin(process.env.VUE_APP_API_URL + 'game_configs', String(this.gameConfig.id), 'status_configs?pagination=true'))
                            .then((result) => {
                                const statusConfigs: StatusConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    statusConfigs.push((new StatusConfig()).load(datum));
                                });
                            
                                if (this.gameConfig instanceof GameConfig) {
                                    this.gameConfig.statusConfigs = statusConfigs;
                                }
                            });
                        ApiService.get(urlJoin(process.env.VUE_APP_API_URL + 'game_configs', String(this.gameConfig.id), 'disease_cause_configs?pagination=true'))
                            .then((result) => {
                                const diseaseCauseConfigs: DiseaseCauseConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    diseaseCauseConfigs.push((new DiseaseCauseConfig()).load(datum));
                                });
                            
                                if (this.gameConfig instanceof GameConfig) {
                                    this.gameConfig.diseaseCauseConfigs = diseaseCauseConfigs;
                                }
                            });
                    }
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
        addNewCharacterConfig(selectedId: integer){
            GameConfigService.loadCharacterConfig(selectedId).then((res) => {
                if (res && this.gameConfig && this.gameConfig.characterConfigs){
                    this.gameConfig.characterConfigs.push(res);
                }
            });
        },
        removeCharacterConfig(characterConfig: any){
            if (this.gameConfig && this.gameConfig.characterConfigs){
                this.gameConfig.characterConfigs = removeItem(this.gameConfig.characterConfigs, characterConfig);
            }
        },
        addNewEquipmentConfig(selectedId: integer){
            GameConfigService.loadEquipmentConfig(selectedId).then((res) => {
                if (res && this.gameConfig && this.gameConfig.equipmentConfigs){
                    this.gameConfig.equipmentConfigs.push(res);
                }
            });
        },
        removeEquipmentConfig(equipmentConfig: any){
            if (this.gameConfig && this.gameConfig.equipmentConfigs){
                this.gameConfig.equipmentConfigs = removeItem(this.gameConfig.equipmentConfigs, equipmentConfig);
            }
        },
        addNewStatusConfig(selectedId: integer){
            GameConfigService.loadStatusConfig(selectedId).then((res) => {
                if (res && this.gameConfig && this.gameConfig.statusConfigs){
                    this.gameConfig.statusConfigs.push(res);
                }
            });
        },
        removeStatusConfig(statusConfig: any){
            if (this.gameConfig && this.gameConfig.statusConfigs){
                this.gameConfig.statusConfigs = removeItem(this.gameConfig.statusConfigs, statusConfig);
            }
        },
        addNewDiseaseCauseConfig(selectedId: integer){
            GameConfigService.loadDiseaseCauseConfig(selectedId).then((res) => {
                if (res && this.gameConfig && this.gameConfig.daedalusConfig && this.gameConfig.diseaseCauseConfigs){
                    this.gameConfig.diseaseCauseConfigs.push(res);
                }
            });
        },
        removeDiseaseCauseConfig(diseaseCauseConfig: any){
            if (this.gameConfig && this.gameConfig.diseaseCauseConfigs){
                this.gameConfig.diseaseCauseConfigs = removeItem(this.gameConfig.diseaseCauseConfigs, diseaseCauseConfig);
            }
        },
    },
    beforeMount() {
        const gameConfigId = Number(this.$route.params.gameConfigId);
        GameConfigService.loadGameConfig(gameConfigId).then((res: GameConfig | null) => {
            this.gameConfig = res;
            ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'game_configs', String(gameConfigId), 'daedalus_config'))
                .then((result) => {
                    const daedalusConfig: DaedalusConfig = new DaedalusConfig();
                    daedalusConfig.load(result.data);

                    if (this.gameConfig instanceof GameConfig) {
                        this.gameConfig.daedalusConfig = daedalusConfig;
                    }
                });
            ApiService.get(urlJoin(process.env.VUE_APP_API_URL+'game_configs', String(gameConfigId), 'character_configs'))
                .then((result) => {
                    const characterConfigs: CharacterConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        characterConfigs.push((new CharacterConfig()).load(datum));
                    });

                    if (this.gameConfig instanceof GameConfig) {
                        this.gameConfig.characterConfigs = characterConfigs;
                    }
                });
            ApiService.get(urlJoin(process.env.VUE_APP_API_URL + 'game_configs', String(gameConfigId), 'equipment_configs?pagination=true'))
                .then((result) => {
                    const equipmentConfigs: EquipmentConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        equipmentConfigs.push((new EquipmentConfig()).load(datum));
                    });
                    
                    if (this.gameConfig instanceof GameConfig) {
                        this.gameConfig.equipmentConfigs = equipmentConfigs;
                    }
                });
            ApiService.get(urlJoin(process.env.VUE_APP_API_URL + 'game_configs', String(gameConfigId), 'status_configs?pagination=true'))
                .then((result) => {
                    const statusConfigs: StatusConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        statusConfigs.push((new StatusConfig()).load(datum));
                    });
                    
                    if (this.gameConfig instanceof GameConfig) {
                        this.gameConfig.statusConfigs = statusConfigs;
                    }
                });
            ApiService.get(urlJoin(process.env.VUE_APP_API_URL + 'game_configs', String(gameConfigId), 'disease_cause_configs?pagination=true'))
                .then((result) => {
                    const diseaseCauseConfigs: DiseaseCauseConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        diseaseCauseConfigs.push((new DiseaseCauseConfig()).load(datum));
                    });
                    
                    if (this.gameConfig instanceof GameConfig) {
                        this.gameConfig.diseaseCauseConfigs = diseaseCauseConfigs;
                    }
                });
        });
    }
});
</script>

<style lang="scss" scoped>

</style>
