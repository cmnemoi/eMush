<template>
    <div v-if="characterConfig" class="center">
        <div class="flex-row">
            <Input
                :label="$t('admin.characterConfig.name')"
                id="characterConfig_name"
                v-model="characterConfig.name"
                type="text"
                :errors="errors.name"
            ></Input>
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.characterConfig.characterName')"
                id="characterConfig_characterName"
                v-model="characterConfig.characterName"
                type="text"
                :errors="errors.characterName"
            />

            <Input
                :label="$t('admin.characterConfig.maxNumberPrivateChannel')"
                id="characterConfig_maxNumberPrivateChannel"
                v-model="characterConfig.maxNumberPrivateChannel"
                type="number"
                :errors="errors.maxNumberPrivateChannel"
            />

            <Input
                :label="$t('admin.characterConfig.initHealthPoint')"
                id="characterConfig_initHealthPoint"
                v-model="characterConfig.initHealthPoint"
                type="number"
                :errors="errors.initHealthPoint"
            />

            <Input
                :label="$t('admin.characterConfig.maxHealthPoint')"
                id="characterConfig_maxHealthPoint"
                v-model="characterConfig.maxHealthPoint"
                type="number"
                :errors="errors.maxHealthPoint"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.characterConfig.initMoralPoint')"
                id="characterConfig_initMoralPoint"
                v-model="characterConfig.initMoralPoint"
                type="number"
                :errors="errors.initMoralPoint"
            />

            <Input
                :label="$t('admin.characterConfig.maxMoralPoint')"
                id="characterConfig_maxMoralPoint"
                v-model="characterConfig.maxMoralPoint"
                type="number"
                :errors="errors.maxMoralPoint"
            />

            <Input
                :label="$t('admin.characterConfig.initSatiety')"
                id="characterConfig_initSatiety"
                v-model="characterConfig.initSatiety"
                type="number"
                :errors="errors.initSatiety"
            />

            <Input
                :label="$t('admin.characterConfig.initActionPoint')"
                id="characterConfig_initActionPoint"
                v-model="characterConfig.initActionPoint"
                type="number"
                :errors="errors.initActionPoint"
            />
        </div>
        <div class="flex-row">
            <Input
                :label="$t('admin.characterConfig.maxActionPoint')"
                id="characterConfig_maxActionPoint"
                v-model="characterConfig.maxActionPoint"
                type="number"
                :errors="errors.maxActionPoint"
            />

            <Input
                :label="$t('admin.characterConfig.initMovementPoint')"
                id="characterConfig_initMovementPoint"
                v-model="characterConfig.initMovementPoint"
                type="number"
                :errors="errors.initMovementPoint"
            />

            <Input
                :label="$t('admin.characterConfig.maxMovementPoint')"
                id="characterConfig_maxMovementPoint"
                v-model="characterConfig.maxMovementPoint"
                type="number"
                :errors="errors.maxMovementPoint"
            />

            <Input
                :label="$t('admin.characterConfig.maxItemInInventory')"
                id="characterConfig_maxItemInInventory"
                v-model="characterConfig.maxItemInInventory"
                type="number"
                :errors="errors.maxItemInInventory"
            />
        </div>
        <h3> {{$t('admin.characterConfig.initStatuses')}} </h3>
        <ChildCollectionManager :children="characterConfig.initStatuses" @addId="selectNewInitStatus" @remove="removeInitStatus">
            <template #header="child">
                <span>{{ child.id }} - {{ child.name }}</span>
            </template>
            <template #body="child">
                <span>{{ $t('admin.characterConfig.name') }} {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        <h3> {{$t('admin.characterConfig.actions')}} </h3>
        <ChildCollectionManager :children="characterConfig.actions" @addId="selectNewAction" @remove="removeAction">
            <template #header="child">
                <span>{{ child.id }} - {{ child.name }}</span>
            </template>
            <template #body="child">
                <span>{{ $t('admin.characterConfig.name') }} {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        <h3> {{$t('admin.characterConfig.skills')}} </h3>
        <StringArrayManager
            :array="characterConfig.skills"
            @addElement="characterConfig.skills?.push($event)"
            @removeElement="characterConfig.skills?.splice($event, 1)"
        />
        <h3> {{$t('admin.characterConfig.startingItems')}} </h3>
        <ChildCollectionManager :children="characterConfig.startingItems" @addId="selectNewStartingItem" @remove="removeStartingItem">
            <template #header="child">
                <span>{{ child.id }} - {{ child.name }}</span>
            </template>
            <template #body="child">
                <span>{{ $t('admin.characterConfig.name') }} {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        <h3> {{$t('admin.characterConfig.initDiseases')}} </h3>
        <ChildCollectionManager :children="characterConfig.initDiseases" @addId="selectNewInitDisease" @remove="removeInitDisease">
            <template #header="child">
                <span>{{ child.id }} - {{ child.name }}</span>
            </template>
            <template #body="child">
                <span>{{ $t('admin.characterConfig.name') }} {{ child.name }}</span>
            </template>
        </ChildCollectionManager>
        <UpdateConfigButtons @create="create" @update="update"/>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import ActionService from "@/services/action.service";
import { handleErrors } from "@/utils/apiValidationErrors";
import Input from "@/components/Utils/Input.vue";
import { CharacterConfig } from "@/entities/Config/CharacterConfig";
import { removeItem } from "@/utils/misc";
import ChildCollectionManager from "@/components/Utils/ChildcollectionManager.vue";
import { StatusConfig } from "@/entities/Config/StatusConfig";
import { Action } from "@/entities/Action";
import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { EquipmentConfig } from "@/entities/Config/EquipmentConfig";
import { DiseaseConfig } from "@/entities/Config/DiseaseConfig";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";
import StringArrayManager from "@/components/Utils/StringArrayManager.vue";

interface CharacterConfigState {
    characterConfig: CharacterConfig|null
    errors: any
}

export default defineComponent({
    name: "CharacterConfigDetailPage",
    components: {
        ChildCollectionManager,
        Input,
        UpdateConfigButtons,
        StringArrayManager
    },
    data: function (): CharacterConfigState {
        return {
            characterConfig: null,
            errors: {}

        };
    },
    methods: {
        create(): void {
            if (this.characterConfig === null) return;

            const newCharacterConfig = this.characterConfig;
            newCharacterConfig.id = null;

            // @ts-ignore
            GameConfigService.createCharacterConfig(newCharacterConfig)
                .then((res: CharacterConfig | null) => {
                    const newCharacterConfigUrl = urlJoin(import.meta.env.VITE_URL + '/config/character-config', String(res?.id));
                    window.location.href = newCharacterConfigUrl;
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
            if (this.characterConfig === null) {
                return;
            }
            this.errors = {};
            //@ts-ignore
            GameConfigService.updateCharacterConfig(this.characterConfig) 
                .then((res: CharacterConfig | null) => { 
                    this.characterConfig = res;
                    if (this.characterConfig !== null) {
                        ApiService.get(urlJoin(import.meta.env.VITE_API_URL + 'character_configs', String(this.characterConfig.id), 'init_statuses'))
                            .then((result) => {
                                const initStatuses: StatusConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const currentStatusConfig = (new StatusConfig()).load(datum);
                                    initStatuses.push(currentStatusConfig);
                                });
                                if (this.characterConfig instanceof CharacterConfig) {
                                    this.characterConfig.initStatuses = initStatuses;
                                }
                            });
                        ApiService.get(urlJoin(import.meta.env.VITE_API_URL + 'character_configs', String(this.characterConfig.id), 'actions'))
                            .then((result) => {
                                const actions: Action[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const currentAction = (new Action()).load(datum);
                                    actions.push(currentAction);
                                });
                                if (this.characterConfig instanceof CharacterConfig) {
                                    this.characterConfig.actions = actions;
                                }
                            });
                        ApiService.get(urlJoin(import.meta.env.VITE_API_URL + 'character_configs', String(this.characterConfig.id), 'starting_items'))
                            .then((result) => {
                                const startingItems: EquipmentConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const currentEquipmentConfig = (new EquipmentConfig()).load(datum);
                                    startingItems.push(currentEquipmentConfig);
                                });
                                if (this.characterConfig instanceof CharacterConfig) {
                                    this.characterConfig.startingItems = startingItems;
                                }
                            });
                        ApiService.get(urlJoin(import.meta.env.VITE_API_URL + 'character_configs', String(this.characterConfig.id), 'init_diseases'))
                            .then((result) => {
                                const initDiseases: DiseaseConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const currentDiseaseConfig = (new DiseaseConfig()).load(datum);
                                    initDiseases.push(currentDiseaseConfig);
                                });
                                if (this.characterConfig instanceof CharacterConfig) {
                                    this.characterConfig.initDiseases = initDiseases;
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
        selectNewInitStatus(selectedId: any) {
            GameConfigService.loadStatusConfig(selectedId).then((res) => {
                if (res && this.characterConfig && this.characterConfig.initStatuses) {
                    this.characterConfig.initStatuses.push(res);
                }
            });
        },
        removeInitStatus(child: any) {
            if (this.characterConfig && this.characterConfig.initStatuses) {
                this.characterConfig.initStatuses = removeItem(this.characterConfig.initStatuses, child);
            }
        },
        selectNewAction(selectedId: any) {
            ActionService.loadAction(selectedId).then((res) => {
                if (res && this.characterConfig && this.characterConfig.actions) {
                    this.characterConfig.actions.push(res);
                }
            });
        },
        removeAction(child: any) {
            if (this.characterConfig && this.characterConfig.actions) {
                this.characterConfig.actions = removeItem(this.characterConfig.actions, child);
            }
        },
        selectNewStartingItem(selectedId: any) {
            GameConfigService.loadEquipmentConfig(selectedId).then((res) => {
                if (res && this.characterConfig && this.characterConfig.startingItems) {
                    this.characterConfig.startingItems.push(res);
                }
            });
        },
        removeStartingItem(child: any) {
            if (this.characterConfig && this.characterConfig.startingItems) {
                this.characterConfig.startingItems = removeItem(this.characterConfig.startingItems, child);
            }
        },
        selectNewInitDisease(selectedId: any) {
            GameConfigService.loadDiseaseConfig(selectedId).then((res) => {
                if (res && this.characterConfig && this.characterConfig.initDiseases) {
                    this.characterConfig.initDiseases.push(res);
                }
            });
        },
        removeInitDisease(child: any) {
            if (this.characterConfig && this.characterConfig.initDiseases) {
                this.characterConfig.initDiseases = removeItem(this.characterConfig.initDiseases, child);
            }
        },
    },
    beforeMount() {
        const characterConfigId = String(this.$route.params.characterConfigId);
        GameConfigService.loadCharacterConfig(Number(characterConfigId)).then((res: CharacterConfig | null) => {
            this.characterConfig = res;
            ApiService.get(urlJoin(import.meta.env.VITE_API_URL + 'character_configs', characterConfigId, 'init_statuses'))
                .then((result) => {
                    const initStatuses : StatusConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        const currentStatusConfig = (new StatusConfig()).load(datum);
                        initStatuses.push(currentStatusConfig);
                    });
                    if (this.characterConfig instanceof CharacterConfig) {
                        this.characterConfig.initStatuses = initStatuses;
                    }
                });
            ApiService.get(urlJoin(import.meta.env.VITE_API_URL + 'character_configs', characterConfigId, 'actions'))
                .then((result) => {
                    const actions : Action[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        const currentAction = (new Action()).load(datum);
                        actions.push(currentAction);
                    });
                    if (this.characterConfig instanceof CharacterConfig) {
                        this.characterConfig.actions = actions;
                    }
                });
            ApiService.get(urlJoin(import.meta.env.VITE_API_URL + 'character_configs', characterConfigId, 'starting_items'))
                .then((result) => {
                    const startingItems : EquipmentConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        const currentEquipmentConfig = (new EquipmentConfig()).load(datum);
                        startingItems.push(currentEquipmentConfig);
                    });
                    if (this.characterConfig instanceof CharacterConfig) {
                        this.characterConfig.startingItems = startingItems;
                    }
                });
            ApiService.get(urlJoin(import.meta.env.VITE_API_URL + 'character_configs', characterConfigId, 'init_diseases'))
                .then((result) => {
                    const initDiseases : DiseaseConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        const currentDiseaseConfig = (new DiseaseConfig()).load(datum);
                        initDiseases.push(currentDiseaseConfig);
                    });
                    if (this.characterConfig instanceof CharacterConfig) {
                        this.characterConfig.initDiseases = initDiseases;
                    }
                });
        });
    }
});
</script>


<style lang="scss" scoped>

</style>
