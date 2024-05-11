<template>
    <div v-if="gameConfig" class="center">
        <div class="flex-row wrap">
            <Input
                :label="$t('admin.gameConfig.name')"
                id="gameConfig_name"
                v-model="gameConfig.name"
                type="text"
                :errors="errors.name"
            />
        </div>
        <h3>{{ $t("admin.gameConfig.daedalusConfig") }}</h3>
        <ChildManager
            :child="gameConfig.daedalusConfig"
            id="gameConfig_daedalusConfig"
            @add-id="selectNewDaedalusConfig"
        >
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildManager>
        <h3>{{ $t("admin.gameConfig.difficultyConfig") }}</h3>
        <ChildManager
            :child="gameConfig.difficultyConfig"
            id="gameConfig_difficultyConfig"
            @add-i-d="selectNewDifficultyConfig"
        >
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildManager>
        <h3>{{ $t("admin.gameConfig.characterConfigs") }}</h3>
        <ChildCollectionManager
            :children="gameConfig.charactersConfig"
            id="gameConfig_characterConfigs"
            @add-id="addNewCharacterConfig"
            @remove="removeCharacterConfig"
        >
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        <h3>{{ $t("admin.gameConfig.statusConfigs") }}</h3>
        <ChildCollectionManager
            :children="gameConfig.statusConfigs"
            id="gameConfig_statusConfigs"
            @add-id="addNewStatusConfig"
            @remove="removeStatusConfig"
        >
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        <h3>{{ $t("admin.gameConfig.equipmentConfigs") }}</h3>
        <ChildCollectionManager
            :children="gameConfig.equipmentsConfig"
            id="gameConfig_equipmentConfigs"
            @add-id="addNewEquipmentConfig"
            @remove="removeEquipmentConfig"
        >
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        <h3>{{ $t("admin.gameConfig.triumphConfigs") }}</h3>
        <ChildCollectionManager
            :children="gameConfig.triumphConfig"
            id="gameConfig_triumphConfigs"
            @add-id="addNewTriumphConfig"
            @remove="removeTriumphConfig"
        >
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        <h3>{{ $t("admin.gameConfig.diseaseCauseConfigs") }}</h3>
        <ChildCollectionManager
            :children="gameConfig.diseaseCauseConfig"
            id="gameConfig_diseaseCauseConfigs"
            @add-id="addNewDiseaseCauseConfig"
            @remove="removeDiseaseCauseConfig"
        >
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        <h3>{{ $t("admin.gameConfig.diseaseConfigs") }}</h3>
        <ChildCollectionManager
            :children="gameConfig.diseaseConfig"
            id="gameConfig_diseaseConfigs"
            @add-id="addNewDiseaseConfig"
            @remove="removeDiseaseConfig"
        >
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        <h3>{{ $t("admin.gameConfig.consumableDiseaseConfigs") }}</h3>
        <ChildCollectionManager
            :children="gameConfig.consumableDiseaseConfig"
            id="gameConfig_consumableDiseaseConfigs"
            @add-id="addConsumableDiseaseConfig"
            @remove="removeConsumableDiseaseConfig"
        >
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        <UpdateConfigButtons :create="false" @update="update"/>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import { CharacterConfig } from "@/entities/Config/CharacterConfig";
import { ConsumableDiseaseConfig } from "@/entities/Config/ConsumableDiseaseConfig";
import { DaedalusConfig } from "@/entities/Config/DaedalusConfig";
import { DifficultyConfig } from "@/entities/Config/DifficultyConfig";
import { DiseaseCauseConfig } from "@/entities/Config/DiseaseCauseConfig";
import { DiseaseConfig } from "@/entities/Config/DiseaseConfig";
import { EquipmentConfig } from "@/entities/Config/EquipmentConfig";
import { GameConfig } from "@/entities/Config/GameConfig";
import { StatusConfig } from "@/entities/Config/StatusConfig";
import { TriumphConfig } from "@/entities/Config/TriumphConfig";
import { handleErrors } from "@/utils/apiValidationErrors";
import ChildCollectionManager from "@/components/Utils/ChildcollectionManager.vue";
import ChildManager from "@/components/Utils/ChildManager.vue";
import Input from "@/components/Utils/Input.vue";
import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { removeItem } from "@/utils/misc";
import { resourceLimits } from "worker_threads";
import { gameConfig } from "@/store/game_config.module";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";


interface GameConfigState {
    gameConfig: null|GameConfig
    errors: any
}

export default defineComponent({
    name: "GameConfigDetailPage",
    components: {
        ChildCollectionManager,
        ChildManager,
        Input,
        UpdateConfigButtons
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
                        ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'game_configs', String(this.gameConfig.id), 'daedalus_config'))
                            .then((result) => {
                                const daedalusConfig: DaedalusConfig = new DaedalusConfig();
                                daedalusConfig.load(result.data);

                                if (this.gameConfig instanceof GameConfig) {
                                    this.gameConfig.daedalusConfig = daedalusConfig;
                                }
                            });
                        ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'game_configs', String(this.gameConfig.id), 'difficulty_config'))
                            .then((result) => {
                                const difficultyConfig: DifficultyConfig = new DifficultyConfig();
                                difficultyConfig.load(result.data);

                                if (this.gameConfig instanceof GameConfig) {
                                    this.gameConfig.difficultyConfig = difficultyConfig;
                                }
                            });
                        ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'game_configs', String(this.gameConfig.id), 'characters_configs'))
                            .then((result) => {
                                const charactersConfig: CharacterConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    charactersConfig.push((new CharacterConfig()).load(datum));
                                });

                                if (this.gameConfig instanceof GameConfig) {
                                    this.gameConfig.charactersConfig = charactersConfig;
                                }
                            });
                        ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'game_configs', String(this.gameConfig.id), 'equipments_configs?pagination=false'))
                            .then((result) => {
                                const equipmentsConfig: EquipmentConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    equipmentsConfig.push((new EquipmentConfig()).load(datum));
                                });

                                if (this.gameConfig instanceof GameConfig) {
                                    this.gameConfig.equipmentsConfig = equipmentsConfig;
                                }
                            });
                        ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'game_configs', String(this.gameConfig.id), 'status_configs?pagination=false'))
                            .then((result) => {
                                const statusConfigs: StatusConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    statusConfigs.push((new StatusConfig()).load(datum));
                                });

                                if (this.gameConfig instanceof GameConfig) {
                                    this.gameConfig.statusConfigs = statusConfigs;
                                }
                            });
                        ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'game_configs', String(this.gameConfig.id), 'triumph_configs?pagination=false'))
                            .then((result) => {
                                const triumphConfigs: TriumphConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    triumphConfigs.push((new TriumphConfig()).load(datum));
                                });

                                if (this.gameConfig instanceof GameConfig) {
                                    this.gameConfig.triumphConfig = triumphConfigs;
                                }
                            });
                        ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'game_configs', String(this.gameConfig.id), 'disease_cause_configs?pagination=false'))
                            .then((result) => {
                                const diseaseCauseConfigs: DiseaseCauseConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    diseaseCauseConfigs.push((new DiseaseCauseConfig()).load(datum));
                                });

                                if (this.gameConfig instanceof GameConfig) {
                                    this.gameConfig.diseaseCauseConfig = diseaseCauseConfigs;
                                }
                            });
                        ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'game_configs', String(this.gameConfig.id), 'disease_configs?pagination=false'))
                            .then((result) => {
                                const diseaseConfigs: DiseaseConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    diseaseConfigs.push((new DiseaseConfig()).load(datum));
                                });

                                if (this.gameConfig instanceof GameConfig) {
                                    this.gameConfig.diseaseConfig = diseaseConfigs;
                                }
                            });
                        ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'game_configs', String(this.gameConfig.id), 'consumable_disease_configs?pagination=false'))
                            .then((result) => {
                                const consumableDiseaseConfigs: ConsumableDiseaseConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    consumableDiseaseConfigs.push((new ConsumableDiseaseConfig()).load(datum));
                                });

                                if (this.gameConfig instanceof GameConfig) {
                                    this.gameConfig.consumableDiseaseConfig = consumableDiseaseConfigs;
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
                if (res && this.gameConfig && this.gameConfig.charactersConfig){
                    this.gameConfig.charactersConfig.push(res);
                }
            });
        },
        removeCharacterConfig(characterConfig: any){
            if (this.gameConfig && this.gameConfig.charactersConfig){
                this.gameConfig.charactersConfig = removeItem(this.gameConfig.charactersConfig, characterConfig);
            }
        },
        addNewEquipmentConfig(selectedId: integer){
            GameConfigService.loadEquipmentConfig(selectedId).then((res) => {
                if (res && this.gameConfig && this.gameConfig.equipmentsConfig){
                    this.gameConfig.equipmentsConfig.push(res);
                }
            });
        },
        removeEquipmentConfig(equipmentConfig: any){
            if (this.gameConfig && this.gameConfig.equipmentsConfig){
                this.gameConfig.equipmentsConfig = removeItem(this.gameConfig.equipmentsConfig, equipmentConfig);
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
        selectNewDaedalusConfig(selectedId: integer){
            GameConfigService.loadDaedalusConfig(selectedId).then((res) => {
                if (res && this.gameConfig){
                    this.gameConfig.daedalusConfig = res;
                }
            });
        },
        selectNewDifficultyConfig(selectedId: integer){
            GameConfigService.loadDifficultyConfig(selectedId).then((res) => {
                if (res && this.gameConfig){
                    this.gameConfig.difficultyConfig = res;
                }
            });
        },
        addNewTriumphConfig(selectedId: integer){
            GameConfigService.loadTriumphConfig(selectedId).then((res) => {
                if (res && this.gameConfig && this.gameConfig.triumphConfig){
                    this.gameConfig.triumphConfig.push(res);
                }
            });
        },
        removeTriumphConfig(triumphConfig: any){
            if (this.gameConfig && this.gameConfig.triumphConfig){
                this.gameConfig.triumphConfig = removeItem(this.gameConfig.triumphConfig, triumphConfig);
            }
        },
        addNewDiseaseCauseConfig(selectedId: integer){
            GameConfigService.loadDiseaseCauseConfig(selectedId).then((res) => {
                if (res && this.gameConfig && this.gameConfig.diseaseCauseConfig){
                    this.gameConfig.diseaseCauseConfig.push(res);
                }
            });
        },
        removeDiseaseCauseConfig(diseaseCauseConfig: any){
            if (this.gameConfig && this.gameConfig.diseaseCauseConfig){
                this.gameConfig.diseaseCauseConfig = removeItem(this.gameConfig.diseaseCauseConfig, diseaseCauseConfig);
            }
        },
        addNewDiseaseConfig(selectedId: integer){
            GameConfigService.loadDiseaseConfig(selectedId).then((res) => {
                if (res && this.gameConfig && this.gameConfig.diseaseConfig){
                    this.gameConfig.diseaseConfig.push(res);
                }
            });
        },
        removeDiseaseConfig(diseaseConfig: any){
            if (this.gameConfig && this.gameConfig.diseaseConfig){
                this.gameConfig.diseaseConfig = removeItem(this.gameConfig.diseaseConfig, diseaseConfig);
            }
        },
        addConsumableDiseaseConfig(selectedId: integer){
            GameConfigService.loadConsumableDiseaseConfig(selectedId).then((res) => {
                if (res && this.gameConfig && this.gameConfig.consumableDiseaseConfig){
                    this.gameConfig.consumableDiseaseConfig.push(res);
                }
            });
        },
        removeConsumableDiseaseConfig(consumableDiseaseConfig: any){
            if (this.gameConfig && this.gameConfig.consumableDiseaseConfig){
                this.gameConfig.consumableDiseaseConfig = removeItem(this.gameConfig.consumableDiseaseConfig, consumableDiseaseConfig);
            }
        }
    },
    beforeMount() {
        const gameConfigId = Number(this.$route.params.gameConfigId);
        GameConfigService.loadGameConfig(gameConfigId).then((res: GameConfig | null) => {
            this.gameConfig = res;
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+'game_configs', String(gameConfigId), 'daedalus_config'))
                .then((result) => {
                    const daedalusConfig: DaedalusConfig = new DaedalusConfig();
                    daedalusConfig.load(result.data);

                    if (this.gameConfig instanceof GameConfig) {
                        this.gameConfig.daedalusConfig = daedalusConfig;
                    }
                });
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+'game_configs', String(gameConfigId), 'difficulty_config'))
                .then((result) => {
                    const difficultyConfig: DifficultyConfig = new DifficultyConfig();
                    difficultyConfig.load(result.data);

                    if (this.gameConfig instanceof GameConfig) {
                        this.gameConfig.difficultyConfig = difficultyConfig;
                    }
                });
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+'game_configs', String(gameConfigId), 'characters_configs'))
                .then((result) => {
                    const charactersConfig: CharacterConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        charactersConfig.push((new CharacterConfig()).load(datum));
                    });

                    if (this.gameConfig instanceof GameConfig) {
                        this.gameConfig.charactersConfig = charactersConfig;
                    }
                });
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'game_configs', String(gameConfigId), 'equipments_configs?pagination=false'))
                .then((result) => {
                    const equipmentsConfig: EquipmentConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        equipmentsConfig.push((new EquipmentConfig()).load(datum));
                    });

                    if (this.gameConfig instanceof GameConfig) {
                        this.gameConfig.equipmentsConfig = equipmentsConfig;
                    }
                });
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'game_configs', String(gameConfigId), 'status_configs?pagination=false'))
                .then((result) => {
                    const statusConfigs: StatusConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        statusConfigs.push((new StatusConfig()).load(datum));
                    });

                    if (this.gameConfig instanceof GameConfig) {
                        this.gameConfig.statusConfigs = statusConfigs;
                    }
                });
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'game_configs', String(gameConfigId), 'triumph_configs?pagination=false'))
                .then((result) => {
                    const triumphConfigs: TriumphConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        triumphConfigs.push((new TriumphConfig()).load(datum));
                    });

                    if (this.gameConfig instanceof GameConfig) {
                        this.gameConfig.triumphConfig = triumphConfigs;
                    }
                });
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'game_configs', String(gameConfigId), 'disease_cause_configs?pagination=false'))
                .then((result) => {
                    const diseaseCauseConfigs: DiseaseCauseConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        diseaseCauseConfigs.push((new DiseaseCauseConfig()).load(datum));
                    });

                    if (this.gameConfig instanceof GameConfig) {
                        this.gameConfig.diseaseCauseConfig = diseaseCauseConfigs;
                    }
                });
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'game_configs', String(gameConfigId), 'disease_configs?pagination=false'))
                .then((result) => {
                    const diseaseConfigs: DiseaseConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        diseaseConfigs.push((new DiseaseConfig()).load(datum));
                    });

                    if (this.gameConfig instanceof GameConfig) {
                        this.gameConfig.diseaseConfig = diseaseConfigs;
                    }
                });
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'game_configs', String(gameConfigId), 'consumable_disease_configs?pagination=false'))
                .then((result) => {
                    const consumableDiseaseConfigs: ConsumableDiseaseConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        consumableDiseaseConfigs.push((new ConsumableDiseaseConfig()).load(datum));
                    });

                    if (this.gameConfig instanceof GameConfig) {
                        this.gameConfig.consumableDiseaseConfig = consumableDiseaseConfigs;
                    }
                });
        });
    }
});
</script>

<style lang="scss" scoped>

</style>
