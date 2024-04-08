<template>
    <div v-if="equipmentConfig" class="center">
        <h2>{{ $t('admin.equipmentConfig.pageTitle') }} <em>{{ equipmentConfig.equipmentName }}</em></h2>
        <div class="flex-row wrap">
            <Input
                :label="$t('admin.equipmentConfig.name')"
                id="equipmentConfig_name"
                v-model="equipmentConfig.name"
                type="text"
                :errors="errors.name"
            ></Input>
            <Input
                :label="$t('admin.equipmentConfig.equipmentName')"
                id="equipmentConfig_equipmentName"
                v-model="equipmentConfig.equipmentName"
                type="text"
                :errors="errors.equipmentName"
            ></Input>
        </div>
        <div class="flex-row wrap">
            <div class="checkbox-container">
                <input
                    type="checkbox"
                    id="equipmentConfig_isBreakable"
                    v-model="equipmentConfig.isBreakable"
                />
                <label for="equipmentConfig_isBreakable">{{ equipmentConfig.isBreakable ? $t('admin.equipmentConfig.isBreakable') : $t('admin.equipmentConfig.isNotBreakable') }}</label>
            </div>
            <div class="checkbox-container">
                <input
                    type="checkbox"
                    id="equipmentConfig_isFireBreakable"
                    v-model="equipmentConfig.isFireBreakable"
                />
                <label for="equipmentConfig_isFireBreakable">{{ equipmentConfig.isFireBreakable ? $t('admin.equipmentConfig.isFireBreakable') : $t('admin.equipmentConfig.isNotFireBreakable') }}</label>
            </div>
            <div class="checkbox-container">
                <input
                    type="checkbox"
                    id="equipmentConfig_isFireDestroyable"
                    v-model="equipmentConfig.isFireDestroyable"
                />
                <label for="equipmentConfig_isFireDestroyable">{{ equipmentConfig.isFireDestroyable ? $t('admin.equipmentConfig.isFireDestroyable') : $t('admin.equipmentConfig.isNotFireDestroyable') }}</label>
            </div>
            <div class="checkbox-container" v-if="equipmentConfig.equipmentType === 'ItemConfig'">
                <input
                    type="checkbox"
                    id="equipmentConfig_isPersonal"
                    v-model="equipmentConfig.isPersonal"
                />
                <label for="equipmentConfig_isPersonal">{{ equipmentConfig.isPersonal ? $t('admin.equipmentConfig.isPersonal') : $t('admin.equipmentConfig.isNotPersonal') }}</label>
            </div>
            <div class="checkbox-container" v-if="equipmentConfig.equipmentType === 'ItemConfig'">
                <input
                    type="checkbox"
                    id="equipmentConfig_isStackable"
                    v-model="equipmentConfig.isStackable"
                />
                <label for="equipmentConfig_isStackable">{{ equipmentConfig.isStackable ? $t('admin.equipmentConfig.isStackable') : $t('admin.equipmentConfig.isNotStackable') }}</label>
            </div>
        </div>

        <h3>{{ $t('admin.equipmentConfig.dismountedProducts') }}</h3>
        <StringArrayManager
            :label="$t('admin.equipmentConfig.addDismountedProducts')"
            :array="equipmentConfig.dismountedProducts"
            :selection="products"
            id="equipmentConfig_addDismountedProducts"
            @addElement="addDismountedProduct"
            @removeElement="removeDismountedProduct"
        />

        <h3>{{ $t('admin.equipmentConfig.actions') }}</h3>
        <ChildCollectionManager
            :children="equipmentConfig.actions"
            id="equipmentConfig_actions"
            @addId="selectNewAction"
            @remove="removeAction"
        >
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>

        <h3>{{ $t('admin.equipmentConfig.initStatuses') }}</h3>
        <ChildCollectionManager
            :children="equipmentConfig.initStatuses"
            id="equipmentConfig_initStatuses"
            @addId="selectNewInitStatuses"
            @remove="removeInitStatuses"
        >
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>

        <h3>{{ $t('admin.equipmentConfig.mechanics') }}</h3>
        <ChildCollectionManager
            :children="equipmentConfig.mechanics"
            id="equipmentConfig_mechanics"
            @addId="selectNewMechanics"
            @remove="removeMechanics"
        >
            <template #header="child">
                <span :title="child.name"><strong>{{ child.id }}</strong> - {{ child.name }}</span>
            </template>
        </ChildCollectionManager>

        <UpdateConfigButtons @create="create" @update="update"/>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import GameConfigService from "@/services/game_config.service";
import ActionService from "@/services/action.service";
import { EquipmentConfig } from "@/entities/Config/EquipmentConfig";
import { handleErrors } from "@/utils/apiValidationErrors";
import Input from "@/components/Utils/Input.vue";
import { removeItem } from "@/utils/misc";
import ChildCollectionManager from "@/components/Utils/ChildcollectionManager.vue";
import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import { Action } from "@/entities/Action";
import { StatusConfig } from "@/entities/Config/StatusConfig";
import { Mechanics } from "@/entities/Config/Mechanics";
import UpdateConfigButtons from "@/components/Utils/UpdateConfigButtons.vue";
import StringArrayManager from "@/components/Utils/StringArrayManager.vue";

interface EquipmentConfigState {
    equipmentConfig: null|EquipmentConfig
    errors: any,
    products: Array<string>,
    productToAdd: string
}

export default defineComponent({
    name: "EquipmentConfigDetailPage",
    components: {
        ChildCollectionManager,
        Input,
        StringArrayManager,
        UpdateConfigButtons
    },
    data: function (): EquipmentConfigState {
        return {
            equipmentConfig: null,
            errors: {},
            products: ["metal_scraps", "plastic_scraps", "thick_tube"],
            productToAdd: ""
        };
    },
    methods: {
        create(): void {
            if (this.equipmentConfig === null) return;

            const newEquipmentConfig = this.equipmentConfig;
            newEquipmentConfig.id = null;

            // @ts-ignore
            GameConfigService.createEquipmentConfig(newEquipmentConfig).then((res: EquipmentConfig | null) => {
                const newEquipmentConfigUrl = urlJoin(import.meta.env.VITE_APP_URL + '/config/equipment-config', String(res?.id));
                window.location.href = newEquipmentConfigUrl;
            });
        },
        update(): void {
            if (this.equipmentConfig === null) {
                return;
            }
            this.errors = {};
            // @ts-ignore
            GameConfigService.updateEquipmentConfig(this.equipmentConfig)
                .then((res: EquipmentConfig | null) => {
                    this.equipmentConfig = res;
                    if (this.equipmentConfig !== null) {
                        ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'equipment_configs', String(this.equipmentConfig.id), 'actions'))
                            .then((result) => {
                                const actions: Action[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const currentAction = (new Action()).load(datum);
                                    actions.push(currentAction);
                                });
                                if (this.equipmentConfig instanceof EquipmentConfig) {
                                    this.equipmentConfig.actions = actions;
                                }
                            });
                        ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'equipment_configs', String(this.equipmentConfig.id), 'init_statuses'))
                            .then((result) => {
                                const statuses: StatusConfig[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const currentStatus = (new StatusConfig()).load(datum);
                                    statuses.push(currentStatus);
                                });
                                if (this.equipmentConfig instanceof EquipmentConfig) {
                                    this.equipmentConfig.initStatuses = statuses;
                                }
                            });
                        ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL + 'equipment_configs', String(this.equipmentConfig.id), 'mechanics'))
                            .then((result) => {
                                const mechanics: Mechanics[] = [];
                                result.data['hydra:member'].forEach((datum: any) => {
                                    const currentMechanics = (new Mechanics()).load(datum);
                                    mechanics.push(currentMechanics);
                                });
                                if (this.equipmentConfig instanceof EquipmentConfig) {
                                    this.equipmentConfig.mechanics = mechanics;
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
        addDismountedProduct(product: string): void {
            if (this.equipmentConfig && this.equipmentConfig.dismountedProducts) {
                const productKey = this.equipmentConfig.dismountedProducts.get(product);
                if (productKey !== undefined) {
                    this.equipmentConfig.dismountedProducts.set(product, productKey + 1);
                } else {
                    this.equipmentConfig.dismountedProducts.set(product, 1);
                }
            }
        },
        removeDismountedProduct(product: string): void {
            if (this.equipmentConfig && this.equipmentConfig.dismountedProducts) {
                const productKey = this.equipmentConfig.dismountedProducts.get(product);
                if (productKey !== undefined) {
                    if (productKey > 1) {
                        this.equipmentConfig.dismountedProducts.set(product, productKey - 1);
                    } else {
                        this.equipmentConfig.dismountedProducts.delete(product);
                    }
                }
            }
        },
        selectNewAction(selectedId: any) {
            ActionService.loadAction(selectedId).then((res) => {
                if (res && this.equipmentConfig && this.equipmentConfig.actions) {
                    this.equipmentConfig.actions.push(res);
                }
            });
        },
        removeAction(child: any) {
            if (this.equipmentConfig && this.equipmentConfig.actions) {
                this.equipmentConfig.actions = removeItem(this.equipmentConfig.actions, child);
            }
        },
        selectNewInitStatuses(selectedId: any) {
            GameConfigService.loadStatusConfig(selectedId).then((res) => {
                if (res && this.equipmentConfig && this.equipmentConfig.initStatuses) {
                    this.equipmentConfig.initStatuses.push(res);
                }
            });
        },
        removeInitStatuses(child: any) {
            if (this.equipmentConfig && this.equipmentConfig.initStatuses) {
                this.equipmentConfig.initStatuses = removeItem(this.equipmentConfig.initStatuses, child);
            }
        },
        selectNewMechanics(selectedId: any) {
            GameConfigService.loadMechanics(selectedId).then((res) => {
                if (res && this.equipmentConfig && this.equipmentConfig.mechanics) {
                    this.equipmentConfig.mechanics.push(res);
                }
            });
        },
        removeMechanics(child: any) {
            if (this.equipmentConfig && this.equipmentConfig.mechanics) {
                this.equipmentConfig.mechanics = removeItem(this.equipmentConfig.mechanics, child);
            }
        },
        hasDismountedProducts(): boolean {
            if (this.equipmentConfig === null || this.equipmentConfig.dismountedProducts === null) {
                return false;
            }
            return this.equipmentConfig.dismountedProducts.size > 0;

        }
    },
    beforeMount() {
        const equipmentConfigId = String(this.$route.params.equipmentConfigId);
        GameConfigService.loadEquipmentConfig(Number(equipmentConfigId)).then((res: EquipmentConfig | null) => {
            this.equipmentConfig = res;
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+'equipment_configs', equipmentConfigId, 'actions'))
                .then((result) => {
                    const actions : Action[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        const currentAction = (new Action()).load(datum);
                        actions.push(currentAction);
                    });
                    if (this.equipmentConfig instanceof EquipmentConfig) {
                        this.equipmentConfig.actions = actions;
                    }
                });
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+'equipment_configs', equipmentConfigId, 'init_statuses'))
                .then((result) => {
                    const initStatuses : StatusConfig[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        const currentStatusConfig = (new StatusConfig()).load(datum);
                        initStatuses.push(currentStatusConfig);
                    });
                    if (this.equipmentConfig instanceof EquipmentConfig) {
                        this.equipmentConfig.initStatuses = initStatuses;
                    }
                });
            ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL+'equipment_configs', equipmentConfigId, 'mechanics'))
                .then((result) => {
                    const mechanics : Mechanics[] = [];
                    result.data['hydra:member'].forEach((datum: any) => {
                        const currentMechanics = (new Mechanics()).load(datum);
                        mechanics.push(currentMechanics);
                    });
                    if (this.equipmentConfig instanceof EquipmentConfig) {
                        this.equipmentConfig.mechanics = mechanics;
                    }
                });
        });
    }
});
</script>

<style lang="scss" scoped>

.equipmentConfigCheckbox {
    margin-left: 10px;
    margin-right: 10px;
}

</style>
